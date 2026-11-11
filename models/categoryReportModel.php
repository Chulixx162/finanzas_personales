<?php
// models/categoryReportModel.php
// Modelo del reporte por categoria.
// Todas las consultas se ejecutan mediante procedimientos almacenados.

require_once __DIR__ . '/../config/connection.php';

/**
 * Libera los resultados pendientes que deja un CALL
 * para poder ejecutar otro procedimiento en la misma conexión.
 */
function category_report_liberar_resultados(): void
{
    global $conn;

    try {
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
            // Solo se consumen los resultados restantes.
        }
    } catch (mysqli_sql_exception $e) {
        error_log('Reportes (liberar resultados): ' . $e->getMessage());
    }
}

/**
 * Ejecuta un procedimiento almacenado de reportes y devuelve sus filas.
 * Los procedimientos usados aquí reciben: id_usuario, desde y hasta.
 *
 * Devuelve un arreglo (vacío si no hay filas) o null si el procedimiento falló.
 * Así el controlador puede avisar del error en vez de mostrar un reporte vacío.
 */
function category_report_ejecutar_sp(
    string $procedimiento,
    int $idUsuario,
    string $desde,
    string $hasta
): ?array {
    global $conn;

    // Los nombres de procedimientos no se pueden enviar como parámetros.
    // Se utiliza una lista blanca para evitar inyección SQL.
    $procedimientosPermitidos = [
        'sp_reporte_por_categoria',
        'sp_reporte_totales_periodo'
    ];

    if (!in_array($procedimiento, $procedimientosPermitidos, true)) {
        error_log('Procedimiento no permitido en reportes: ' . $procedimiento);
        return null;
    }

    $sql  = "CALL {$procedimiento}(?, ?, ?)";
    $stmt = null;

    try {
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log($procedimiento . ' (prepare): ' . mysqli_error($conn));
            return null;
        }

        mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $desde, $hasta);

        if (!mysqli_stmt_execute($stmt)) {
            error_log($procedimiento . ' (execute): ' . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            category_report_liberar_resultados();
            return null;
        }

        $filas     = [];
        $resultado = mysqli_stmt_get_result($stmt);

        if ($resultado instanceof mysqli_result) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $filas[] = $fila;
            }
            mysqli_free_result($resultado);
        }

        mysqli_stmt_close($stmt);

        // CALL deja resultados pendientes en la conexión: se consumen
        // para que la siguiente consulta funcione (igual que en los demás modelos).
        category_report_liberar_resultados();

        return $filas;

    } catch (mysqli_sql_exception $e) {
        error_log($procedimiento . ': ' . $e->getMessage());

        if ($stmt instanceof mysqli_stmt) {
            try {
                mysqli_stmt_close($stmt);
            } catch (Throwable $cierre) {
                // El statement ya no se puede cerrar; se ignora.
            }
        }

        category_report_liberar_resultados();
        return null;
    }
}

/**
 * Movimientos de un usuario agrupados por categoria.
 * Devuelve null si el procedimiento falló.
 *
 * Procedimiento almacenado: sp_reporte_por_categoria
 */
function obtener_presupuesto_usuario(int $idUsuario): float {
    global $conn;

    $sql = 'SELECT presupuesto_mensual FROM usuarios WHERE id_usuario = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log(mysqli_error($conn));
        return 0.0;
    }

    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $presupuesto);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (float) ($presupuesto ?? 0.0);
}
function obtener_reporte_por_categoria(
    int $idUsuario,
    string $desde,
    string $hasta
): ?array {
    $resultado = category_report_ejecutar_sp(
        'sp_reporte_por_categoria',
        $idUsuario,
        $desde,
        $hasta
    );

    if ($resultado === null) {
        return null;
    }

    $filas = [];

    foreach ($resultado as $fila) {
        $filas[] = [
            'id_categoria'      => (int) $fila['id_categoria'],
            'categoria'         => (string) $fila['categoria'],
            'total_movimientos' => (int) $fila['total_movimientos'],
            'total_ingresos'    => (float) $fila['total_ingresos'],
            'total_gastos'      => (float) $fila['total_gastos'],
        ];
    }

    return $filas;
}

/**
 * Totales generales de ingresos, gastos y movimientos del periodo.
 * Devuelve null si el procedimiento falló.
 *
 * Procedimiento almacenado: sp_reporte_totales_periodo
 */
function obtener_totales_periodo(
    int $idUsuario,
    string $desde,
    string $hasta
): ?array {
    $totales = [
        'ingresos'    => 0.0,
        'gastos'      => 0.0,
        'movimientos' => 0,
    ];

    $resultado = category_report_ejecutar_sp(
        'sp_reporte_totales_periodo',
        $idUsuario,
        $desde,
        $hasta
    );

    if ($resultado === null) {
        return null;
    }

    if (isset($resultado[0])) {
        $totales = [
            'ingresos'    => (float) $resultado[0]['ingresos'],
            'gastos'      => (float) $resultado[0]['gastos'],
            'movimientos' => (int) $resultado[0]['movimientos'],
        ];
    }

    return $totales;
}