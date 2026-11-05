<?php

// Consultas del historial de presupuesto. No tiene lógica de sesión ni HTML.

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/rubrosModel.php';

/**
 * Historial de cambios de presupuesto de UN usuario, del más reciente al más antiguo.
 * El WHERE id_usuario = ? garantiza que solo se devuelvan los registros de esa persona.
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
 * Cambia el presupuesto mensual del usuario.
 * El trigger trg_usuarios_au de la base de datos guarda solo el registro en
 * historial_presupuesto (anterior, nuevo, fecha, usuario) cuando el valor cambia.
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

        // Libera resultados pendientes del CALL para poder seguir usando la conexión.
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        }

        return $ok;
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Uso del presupuesto del MES ACTUAL, por categoría (suma de sus rubros vigentes este mes).
 *  - asignado: suma de monto_proyectado de los rubros activos en el mes.
 *  - gastado : suma de los movimientos tipo 'gasto' del mes en esos rubros.
 * Mismo criterio de periodo que sp_dashboard_presupuesto_categoria.
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
            $fila['asignado'] = (float) $fila['asignado'];
            $fila['gastado']  = (float) $fila['gastado'];
            $uso[] = $fila;
        }

        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
    }

    return $uso;
}

/**
 * Lo máximo que suman los rubros del usuario en un mes (desde el mes actual).
 * El presupuesto mensual no puede bajar de este valor.
 */
function obtener_max_asignado_mensual(int $idUsuario): float
{
    global $conn;

    return rubro_max_asignado(obtenerCupoRubros($conn, $idUsuario));
}