<?php
// models/budgetHistoryModel.php
// Consultas del historial de presupuesto y reportes.

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/rubrosModel.php';

/**
 * Historial de cambios de presupuesto de UN usuario, del más reciente al más antiguo.
 */
function obtener_historial_presupuesto(int $idUsuario): array
{
    global $conn;
    $historial = [];

    $sql = '
        SELECT
            id_historial,
            presupuesto_anterior,
            presupuesto_nuevo,
            (presupuesto_nuevo - presupuesto_anterior) AS diferencia,
            fecha_cambio
        FROM historial_presupuesto
        WHERE id_usuario = ?
        ORDER BY fecha_cambio DESC, id_historial DESC
    ';

    try {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            error_log(mysqli_error($conn));
            return $historial;
        }

        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        while ($resultado && ($fila = mysqli_fetch_assoc($resultado))) {
            $historial[] = $fila;
        }
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
    }

    return $historial;
}

function obtener_presupuesto_actual(int $idUsuario): ?float
{
    global $conn;
    $sql = 'SELECT presupuesto_mensual FROM usuarios WHERE id_usuario = ?';

    try {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            error_log(mysqli_error($conn));
            return null;
        }

        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        return null;
    }

    if (!$fila || $fila['presupuesto_mensual'] === null) {
        return null;
    }

    return (float) $fila['presupuesto_mensual'];
}

/**
 * Cambia el presupuesto mensual del usuario mediante procedimiento almacenado.
 */
function actualizar_presupuesto_mensual(int $idUsuario, float $nuevoPresupuesto): bool
{
    global $conn;

    try {
        $stmt = mysqli_prepare($conn, 'CALL sp_actualizar_presupuesto(?, ?)');
        if (!$stmt) {
            error_log(mysqli_error($conn));
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'id', $idUsuario, $nuevoPresupuesto);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        }

        return $ok;
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Uso del presupuesto del mes actual, por categoría.
 */
function obtener_uso_presupuesto(int $idUsuario): array
{
    global $conn;
    $uso = [];
    $inicio = date('Y-m-01');
    $fin = date('Y-m-01', strtotime('+1 month', strtotime($inicio)));

    $sql = '
        SELECT
            c.id_categoria,
            c.nombre AS categoria,
            SUM(r.monto_proyectado)     AS asignado,
            COALESCE(SUM(g.gastado), 0)  AS gastado
        FROM categorias c
        INNER JOIN rubros r ON r.id_categoria = c.id_categoria
        LEFT JOIN (
            SELECT id_rubro, SUM(monto) AS gastado
            FROM movimientos
            WHERE tipo = \'gasto\' AND fecha >= ? AND fecha < ?
            GROUP BY id_rubro
        ) g ON g.id_rubro = r.id_rubro
        WHERE c.id_usuario = ?
          AND r.fecha_inicio < ?
          AND r.fecha_fin >= ?
        GROUP BY c.id_categoria, c.nombre
        ORDER BY asignado DESC, c.nombre
    ';

    try {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            error_log(mysqli_error($conn));
            return $uso;
        }

        mysqli_stmt_bind_param($stmt, 'ssiss', $inicio, $fin, $idUsuario, $fin, $inicio);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        while ($resultado && ($fila = mysqli_fetch_assoc($resultado))) {
            $fila['asignado'] = (float)$fila['asignado'];
            $fila['gastado']  = (float)$fila['gastado'];
            $uso[] = $fila;
        }
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
    }

    return $uso;
}

/**
 * Cupo máximo asignado a los rubros en el mes actual.
 */
function obtener_max_asignado_mensual(int $idUsuario): float
{
    global $conn;
    return rubro_max_asignado(obtenerCupoRubros($conn, $idUsuario));
}
/**
 * Obtener los meses que tienen registros de movimientos o rubros activos
 * para alimentar el selector de periodo del reporte financiero.
 */
function obtener_meses_con_movimientos(int $idUsuario): array
{
    global $conn;
    $meses = [];

    // Consulta robusta: une los meses reales de movimientos y los meses del rango de fechas de los rubros
    $sql = "
        SELECT DISTINCT periodo FROM (
            -- 1. Meses que tienen movimientos registrados (CON JOIN PARA LLEGAR AL USUARIO)
            SELECT DATE_FORMAT(m.fecha, '%Y-%m') AS periodo 
            FROM movimientos m
            INNER JOIN rubros r_mov ON m.id_rubro = r_mov.id_rubro
            INNER JOIN categorias c_mov ON r_mov.id_categoria = c_mov.id_categoria
            WHERE c_mov.id_usuario = ?
            
            UNION
            
            -- 2. Meses comprendidos dentro del periodo de los rubros (ej. Octubre a Diciembre)
            SELECT DATE_FORMAT(r.fecha_inicio + INTERVAL t.n MONTH, '%Y-%m') AS periodo
            FROM rubros r
            INNER JOIN categorias c ON r.id_categoria = c.id_categoria
            CROSS JOIN (
                SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 
                UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 
                UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11
                UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15
            ) t
            WHERE c.id_usuario = ? 
              AND r.fecha_inicio + INTERVAL t.n MONTH <= r.fecha_fin
        ) AS periodos_combinados
        ORDER BY periodo DESC
    ";

    try {
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            // Se pasan los dos parámetros idUsuario para ambas consultas del UNION
            mysqli_stmt_bind_param($stmt, 'ii', $idUsuario, $idUsuario);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($resultado)) {
                if (!empty($row['periodo'])) {
                    $meses[] = $row['periodo'];
                }
            }
            mysqli_stmt_close($stmt);
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
    }

    // Si por alguna razón la lista llega vacía, garantizamos al menos el mes actual
    if (empty($meses)) {
        $meses[] = date('Y-m');
    }

    return $meses;
}
/**
 * Obtener el reporte financiero de ingresos y gastos de un mes específico (ej: '2026-11').
 */
function obtener_reporte_financiero_mensual(int $idUsuario, string $periodo): array
{
    global $conn;
    
    $sql = "SELECT m.* 
            FROM movimientos m
            INNER JOIN rubros r ON m.id_rubro = r.id_rubro
            INNER JOIN categorias c ON r.id_categoria = c.id_categoria
            WHERE c.id_usuario = ? AND DATE_FORMAT(m.fecha, '%Y-%m') = ? 
            ORDER BY m.fecha DESC, m.id_movimiento DESC";

    $transacciones = [];
    $totalIngresos = 0;
    $totalGastos = 0;

    try {
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'is', $idUsuario, $periodo);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($resultado)) {
                $row['monto'] = (float) $row['monto'];
                $transacciones[] = $row;
                
                if ($row['tipo'] === 'ingreso') {
                    $totalIngresos += $row['monto'];
                } else if ($row['tipo'] === 'gasto') {
                    $totalGastos += $row['monto'];
                }
            }
            mysqli_stmt_close($stmt);
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
    }

    $balance = $totalIngresos - $totalGastos;

    return [
        'transacciones' => $transacciones,
        'totalIngresos' => $totalIngresos,
        'totalGastos' => $totalGastos,
        'balance' => $balance
    ];
}

function obtener_movimientos_reporte(int $idUsuario, array $filtros = []): array
{
    global $conn;

    $movimientos = [];
    $condiciones = ['c.id_usuario = ?'];
    $tipos = 'i';
    $params = [$idUsuario];

    if (!empty($filtros['desde'])) {
        $condiciones[] = 'm.fecha >= ?';
        $tipos .= 's';
        $params[] = $filtros['desde'] . ' 00:00:00';
    }

    if (!empty($filtros['hasta'])) {
        $condiciones[] = 'm.fecha <= ?';
        $tipos .= 's';
        $params[] = $filtros['hasta'] . ' 23:59:59';
    }

    if (!empty($filtros['tipo']) && in_array($filtros['tipo'], ['ingreso', 'gasto'], true)) {
        $condiciones[] = 'm.tipo = ?';
        $tipos .= 's';
        $params[] = $filtros['tipo'];
    }

    $whereSql = implode(' AND ', $condiciones);

    $sql = "
        SELECT
            m.id_movimiento,
            m.tipo,
            m.monto,
            m.fecha,
            m.descripcion,
            r.nombre AS rubro_nombre,
            c.nombre AS categoria_nombre
        FROM movimientos m
        INNER JOIN rubros r ON m.id_rubro = r.id_rubro
        INNER JOIN categorias c ON r.id_categoria = c.id_categoria
        WHERE {$whereSql}
        ORDER BY m.fecha DESC, m.id_movimiento DESC
    ";

    try {
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $tipos, ...$params);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            while ($resultado && ($fila = mysqli_fetch_assoc($resultado))) {
                $movimientos[] = $fila;
            }
            mysqli_stmt_close($stmt);
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
    }

    return $movimientos;
}