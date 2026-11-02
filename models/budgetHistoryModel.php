<?php
// models/budgetHistoryModel.php
// Consultas del historial de presupuesto. No tiene lógica de sesión ni HTML.

require_once __DIR__ . '/../config/connection.php';

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