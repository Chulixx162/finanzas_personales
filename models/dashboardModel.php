<?php
// models/dashboardModel.php

require_once __DIR__ . '/../config/connection.php';

/**
 * Libera los resultados pendientes que deja un CALL
 * para poder ejecutar otro procedimiento en la misma conexión.
 */
function dashboard_liberar_resultados(): void
{
    global $conn;

    while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        // Solo se consumen los resultados restantes.
    }
}

/**
 * Resumen de ingresos y gastos del usuario.
 * La fecha final es exclusiva.
 */
function dashboard_resumen_mensual(
    int $idUsuario,
    string $inicio,
    string $fin
): array {
    global $conn;

    $sql = 'CALL sp_dashboard_resumen(?, ?, ?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(mysqli_error($conn));
        return ['ingresos' => 0, 'gastos' => 0];
    }

    mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $inicio, $fin);

    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        dashboard_liberar_resultados();
        return ['ingresos' => 0, 'gastos' => 0];
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $fila = $resultado ? (mysqli_fetch_assoc($resultado) ?: []) : [];
    mysqli_stmt_close($stmt);
    dashboard_liberar_resultados();

    return [
        'ingresos' => (float) ($fila['ingresos'] ?? 0),
        'gastos' => (float) ($fila['gastos'] ?? 0),
    ];
}

/**
 * Movimientos del usuario durante el periodo consultado.
 */
function dashboard_movimientos_recientes(
    int $idUsuario,
    string $inicio,
    string $fin
): array {
    global $conn;

    $sql = "
        SELECT
            DATE_FORMAT(m.fecha, '%d/%m/%Y') AS fecha,
            COALESCE(m.descripcion, r.nombre) AS descripcion,
            c.nombre AS categoria,
            m.tipo,
            m.monto
        FROM movimientos m
        INNER JOIN rubros r
            ON r.id_rubro = m.id_rubro
        INNER JOIN categorias c
            ON c.id_categoria = r.id_categoria
        WHERE c.id_usuario = ?
          AND m.fecha >= ?
          AND m.fecha < ?
        ORDER BY
            m.fecha DESC,
            m.id_movimiento DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(mysqli_error($conn));
        return [];
    }

    mysqli_stmt_bind_param(
        $stmt,
        'iss',
        $idUsuario,
        $inicio,
        $fin
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    $filas = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $filas[] = [
            $fila['fecha'],
            $fila['descripcion'],
            $fila['categoria'],
            $fila['tipo'],
            (float) $fila['monto'],
        ];
    }

    mysqli_stmt_close($stmt);

    return $filas;
}

/** Ingresos y gastos agrupados por mes. */
function dashboard_balance_mensual(
    int $idUsuario,
    string $inicio,
    string $fin
): array {
    global $conn;

    $sql = 'CALL sp_dashboard_balance_mensual(?, ?, ?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(mysqli_error($conn));
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $inicio, $fin);

    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        dashboard_liberar_resultados();
        return [];
    }

    $resultado = mysqli_stmt_get_result($stmt);

    $filas = [];
    while ($resultado && ($fila = mysqli_fetch_assoc($resultado))) {
        $filas[] = $fila;
    }

    mysqli_stmt_close($stmt);
    dashboard_liberar_resultados();

    return $filas;
}

/** Gastos del periodo agrupados por categoría. */
function dashboard_gastos_por_categoria(
    int $idUsuario,
    string $inicio,
    string $fin
): array {
    global $conn;

    $sql = 'CALL sp_dashboard_gastos_categoria(?, ?, ?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(mysqli_error($conn));
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $inicio, $fin);

    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        dashboard_liberar_resultados();
        return [];
    }

    $resultado = mysqli_stmt_get_result($stmt);

    $filas = [];
    while ($resultado && ($fila = mysqli_fetch_assoc($resultado))) {
        $filas[] = [
            'categoria' => $fila['categoria'],
            'total' => (float) $fila['total'],
        ];
    }

    mysqli_stmt_close($stmt);
    dashboard_liberar_resultados();

    return $filas;
}

/** Presupuesto proyectado frente al gasto real por categoría. */
function dashboard_presupuesto_por_categoria(
    int $idUsuario,
    string $inicio,
    string $fin
): array {
    global $conn;

    $sql = 'CALL sp_dashboard_presupuesto_categoria(?, ?, ?)';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(mysqli_error($conn));
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $inicio, $fin);

    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        dashboard_liberar_resultados();
        return [];
    }

    $resultado = mysqli_stmt_get_result($stmt);

    $filas = [];
    while ($resultado && ($fila = mysqli_fetch_assoc($resultado))) {
        $filas[] = [
            'categoria' => $fila['categoria'],
            'presupuestado' => (float) $fila['presupuestado'],
            'gastado' => (float) $fila['gastado'],
        ];
    }

    mysqli_stmt_close($stmt);
    dashboard_liberar_resultados();

    return $filas;
}