
<?php
// models/dashboardModel.php

require_once __DIR__ . '/../config/connection.php';

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

    $sql = "
        SELECT
            COALESCE(SUM(
                CASE WHEN m.tipo = 'ingreso'
                THEN m.monto ELSE 0 END
            ), 0) AS ingresos,
            COALESCE(SUM(
                CASE WHEN m.tipo = 'gasto'
                THEN m.monto ELSE 0 END
            ), 0) AS gastos
        FROM movimientos m
        INNER JOIN rubros r ON r.id_rubro = m.id_rubro
        INNER JOIN categorias c ON c.id_categoria = r.id_categoria
        WHERE c.id_usuario = ?
          AND m.fecha >= ?
          AND m.fecha < ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log(mysqli_error($conn));
        return ['ingresos' => 0, 'gastos' => 0];
    }

    mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $inicio, $fin);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($resultado) ?: [];
    mysqli_stmt_close($stmt);

    return [
        'ingresos' => (float) ($fila['ingresos'] ?? 0),
        'gastos' => (float) ($fila['gastos'] ?? 0),
    ];
}

/** Últimos movimientos del usuario. */
function dashboard_movimientos_recientes(
    int $idUsuario,
    int $limite = 5
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
        INNER JOIN rubros r ON r.id_rubro = m.id_rubro
        INNER JOIN categorias c ON c.id_categoria = r.id_categoria
        WHERE c.id_usuario = ?
        ORDER BY m.fecha DESC, m.id_movimiento DESC
        LIMIT ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log(mysqli_error($conn));
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'ii', $idUsuario, $limite);
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

    $sql = "
        SELECT
            YEAR(m.fecha) AS anio,
            MONTH(m.fecha) AS mes,
            SUM(CASE WHEN m.tipo = 'ingreso'
                THEN m.monto ELSE 0 END) AS ingresos,
            SUM(CASE WHEN m.tipo = 'gasto'
                THEN m.monto ELSE 0 END) AS gastos
        FROM movimientos m
        INNER JOIN rubros r ON r.id_rubro = m.id_rubro
        INNER JOIN categorias c ON c.id_categoria = r.id_categoria
        WHERE c.id_usuario = ?
          AND m.fecha >= ?
          AND m.fecha < ?
        GROUP BY YEAR(m.fecha), MONTH(m.fecha)
        ORDER BY anio, mes
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log(mysqli_error($conn));
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $inicio, $fin);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $filas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $filas[] = $fila;
    }

    mysqli_stmt_close($stmt);
    return $filas;
}

/** Gastos del periodo agrupados por categoría. */
function dashboard_gastos_por_categoria(
    int $idUsuario,
    string $inicio,
    string $fin
): array {
    global $conn;

    $sql = "
        SELECT
            c.nombre AS categoria,
            SUM(m.monto) AS total
        FROM movimientos m
        INNER JOIN rubros r ON r.id_rubro = m.id_rubro
        INNER JOIN categorias c ON c.id_categoria = r.id_categoria
        WHERE c.id_usuario = ?
          AND m.tipo = 'gasto'
          AND m.fecha >= ?
          AND m.fecha < ?
        GROUP BY c.id_categoria, c.nombre
        ORDER BY total DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log(mysqli_error($conn));
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $inicio, $fin);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $filas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $filas[] = [
            'categoria' => $fila['categoria'],
            'total' => (float) $fila['total'],
        ];
    }

    mysqli_stmt_close($stmt);
    return $filas;
}

/** Presupuesto proyectado frente al gasto real por categoría. */
function dashboard_presupuesto_por_categoria(
    int $idUsuario,
    string $inicio,
    string $fin
): array {
    global $conn;

    $sql = "
        SELECT
            c.nombre AS categoria,
            COALESCE(SUM(r.monto_proyectado), 0) AS presupuestado,
            COALESCE(SUM(g.gastado), 0) AS gastado
        FROM categorias c
        INNER JOIN rubros r ON r.id_categoria = c.id_categoria
        LEFT JOIN (
            SELECT
                m.id_rubro,
                SUM(m.monto) AS gastado
            FROM movimientos m
            WHERE m.tipo = 'gasto'
              AND m.fecha >= ?
              AND m.fecha < ?
            GROUP BY m.id_rubro
        ) g ON g.id_rubro = r.id_rubro
        WHERE c.id_usuario = ?
          AND r.fecha_inicio < ?
          AND r.fecha_fin >= ?
        GROUP BY c.id_categoria, c.nombre
        ORDER BY c.nombre
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log(mysqli_error($conn));
        return [];
    }

    mysqli_stmt_bind_param(
        $stmt,
        'ssiss',
        $inicio,
        $fin,
        $idUsuario,
        $fin,
        $inicio
    );

    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $filas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $filas[] = [
            'categoria' => $fila['categoria'],
            'presupuestado' => (float) $fila['presupuestado'],
            'gastado' => (float) $fila['gastado'],
        ];
    }

    mysqli_stmt_close($stmt);
    return $filas;
}
