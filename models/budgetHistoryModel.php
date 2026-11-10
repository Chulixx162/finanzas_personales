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

/**
 * Presupuesto mensual vigente del usuario (null si aún no tiene uno definido).
 */
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

    $uso    = [];
    $inicio = date('Y-m-01');
    $fin    = date('Y-m-01', strtotime('+1 month', strtotime($inicio)));

    $sql = '
        SELECT
            c.id_categoria,
            c.nombre AS categoria,
            SUM(r.monto_proyectado)      AS asignado,
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
 * Obtiene los movimientos con filtros para exportación a CSV/PDF[cite: 4].
 */
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
