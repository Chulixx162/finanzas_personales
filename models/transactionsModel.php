<?php
// models/transactionsModel.php

/**
 * Periodos rápidos del filtro de fecha del historial de movimientos.
 * 'custom' permite escribir un rango "Desde / Hasta" a mano.
 */
const PERIODOS_MOVIMIENTOS = ['all', 'today', 'last_7', 'last_30', 'this_month', 'last_month', 'custom'];

/**
 * Obtiene las categorías del usuario para poblar el filtro "Categoría".
 */
function obtenerCategoriasParaFiltro($conn, $user_id): array
{
    $categorias = [];
    $sql = "SELECT id_categoria, nombre
            FROM categorias
            WHERE id_usuario = ?
            ORDER BY nombre ASC";

    try {
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log('obtenerCategoriasParaFiltro (prepare): ' . mysqli_error($conn));
            return $categorias;
        }

        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "i", $u_id);

        if (!mysqli_stmt_execute($stmt)) {
            error_log('obtenerCategoriasParaFiltro (execute): ' . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return $categorias;
        }

        $resultado = mysqli_stmt_get_result($stmt);

        while ($resultado && ($fila = mysqli_fetch_assoc($resultado))) {
            $categorias[] = $fila;
        }
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log('obtenerCategoriasParaFiltro: ' . $e->getMessage());
    }

    return $categorias;
}

/**
 * Devuelve $valor solo si es una fecha real en formato Y-m-d; si no, ''.
 */
function validarFechaFiltro($valor): string
{
    if (!is_string($valor) || $valor === '') {
        return '';
    }

    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

    return ($fecha && $fecha->format('Y-m-d') === $valor) ? $valor : '';
}

/**
 * Limpia y valida los filtros que llegan por GET (periodo, desde, hasta, tipo, categoria).
 * Nunca confía en el navegador: todo valor inválido vuelve a su valor por defecto.
 *
 * @param array $entrada    normalmente $_GET
 * @param array $categorias categorías del usuario (id_categoria, nombre)
 * @return array ['periodo','desde','hasta','tipo','categoria','error']
 */
function normalizarFiltrosMovimientos(array $entrada, array $categorias = []): array
{
    $filtros = [
        'periodo'   => 'all',
        'desde'     => '',
        'hasta'     => '',
        'tipo'      => '',
        'categoria' => 0,
        'error'     => '',
    ];

    // Tipo
    $tipo = is_string($entrada['tipo'] ?? null) ? $entrada['tipo'] : '';
    if (in_array($tipo, ['ingreso', 'gasto'], true)) {
        $filtros['tipo'] = $tipo;
    }

    // Categoría: solo se acepta si pertenece al usuario
    $idCategoria = (int) ($entrada['categoria'] ?? 0);
    $idsValidos  = array_map('intval', array_column($categorias, 'id_categoria'));
    if ($idCategoria > 0 && in_array($idCategoria, $idsValidos, true)) {
        $filtros['categoria'] = $idCategoria;
    }

    // Periodo: si no viene (o no es válido) pero hay fechas, se trata como rango personalizado
    $periodo = is_string($entrada['periodo'] ?? null) ? $entrada['periodo'] : '';
    if (!in_array($periodo, PERIODOS_MOVIMIENTOS, true)) {
        $periodo = (!empty($entrada['desde']) || !empty($entrada['hasta'])) ? 'custom' : 'all';
    }
    $filtros['periodo'] = $periodo;

    $hoy = new DateTimeImmutable('today');

    switch ($periodo) {
        case 'today':
            $filtros['desde'] = $hoy->format('Y-m-d');
            $filtros['hasta'] = $hoy->format('Y-m-d');
            break;

        case 'last_7':
            $filtros['desde'] = $hoy->modify('-6 days')->format('Y-m-d');
            $filtros['hasta'] = $hoy->format('Y-m-d');
            break;

        case 'last_30':
            $filtros['desde'] = $hoy->modify('-29 days')->format('Y-m-d');
            $filtros['hasta'] = $hoy->format('Y-m-d');
            break;

        case 'this_month':
            $filtros['desde'] = $hoy->modify('first day of this month')->format('Y-m-d');
            $filtros['hasta'] = $hoy->modify('last day of this month')->format('Y-m-d');
            break;

        case 'last_month':
            $filtros['desde'] = $hoy->modify('first day of last month')->format('Y-m-d');
            $filtros['hasta'] = $hoy->modify('last day of last month')->format('Y-m-d');
            break;

        case 'custom':
            $desde = validarFechaFiltro($entrada['desde'] ?? '');
            $hasta = validarFechaFiltro($entrada['hasta'] ?? '');

            if ((!empty($entrada['desde']) && $desde === '') || (!empty($entrada['hasta']) && $hasta === '')) {
                $filtros['error'] = 'Una de las fechas no es válida. Usa el formato día/mes/año.';
                break;
            }

            if ($desde !== '' && $hasta !== '' && $desde > $hasta) {
                $filtros['error'] = 'La fecha "Desde" no puede ser posterior a la fecha "Hasta".';
                break;
            }

            $filtros['desde'] = $desde;
            $filtros['hasta'] = $hasta;
            break;
    }

    return $filtros;
}

/**
 * Indica si hay al menos un filtro aplicado (para elegir el mensaje de "no se encontró").
 */
function hayFiltrosActivos(array $filtros): bool
{
    return ($filtros['desde'] ?? '') !== ''
        || ($filtros['hasta'] ?? '') !== ''
        || ($filtros['tipo'] ?? '') !== ''
        || (int) ($filtros['categoria'] ?? 0) > 0;
}

/**
 * Obtiene el historial de movimientos de un usuario, con sus filtros opcionales,
 * junto con los nombres de su rubro y categoría. Todo va con consultas preparadas.
 *
 * Filtros (todos opcionales): desde (Y-m-d), hasta (Y-m-d), tipo, categoria (id).
 */
function obtenerMovimientosFiltrados($conn, $user_id, array $filtros = []): array
{
    $movimientos = [];
    $sql = "SELECT m.id_movimiento, m.id_rubro, m.tipo, m.monto, m.fecha, m.descripcion,
                   r.nombre AS rubro_nombre, c.nombre AS categoria_nombre
            FROM movimientos m
            INNER JOIN rubros r ON m.id_rubro = r.id_rubro
            INNER JOIN categorias c ON r.id_categoria = c.id_categoria
            WHERE c.id_usuario = ?";

    $tipos  = 'i';
    $params = [(int) $user_id];

    if (!empty($filtros['desde'])) {
        $sql      .= " AND m.fecha >= ?";
        $tipos    .= 's';
        $params[]  = $filtros['desde'] . ' 00:00:00';
    }

    if (!empty($filtros['hasta'])) {
        // Se compara contra el día siguiente para incluir todo el último día del rango.
        $sql      .= " AND m.fecha < DATE_ADD(?, INTERVAL 1 DAY)";
        $tipos    .= 's';
        $params[]  = $filtros['hasta'];
    }

    if (!empty($filtros['tipo']) && in_array($filtros['tipo'], ['ingreso', 'gasto'], true)) {
        $sql      .= " AND m.tipo = ?";
        $tipos    .= 's';
        $params[]  = $filtros['tipo'];
    }

    if (!empty($filtros['categoria'])) {
        $sql      .= " AND c.id_categoria = ?";
        $tipos    .= 'i';
        $params[]  = (int) $filtros['categoria'];
    }

    $sql .= " ORDER BY m.fecha DESC, m.id_movimiento DESC";

    try {
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log('obtenerMovimientosFiltrados (prepare): ' . mysqli_error($conn));
            return $movimientos;
        }

        mysqli_stmt_bind_param($stmt, $tipos, ...$params);

        if (!mysqli_stmt_execute($stmt)) {
            error_log('obtenerMovimientosFiltrados (execute): ' . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return $movimientos;
        }

        $resultado = mysqli_stmt_get_result($stmt);

        while ($resultado && ($fila = mysqli_fetch_assoc($resultado))) {
            $movimientos[] = $fila;
        }
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log('obtenerMovimientosFiltrados: ' . $e->getMessage());
    }

    return $movimientos;
}

/**
 * Obtiene todo el historial de movimientos de un usuario (sin filtros).
 */
function obtenerMovimientosPorUsuario($conn, $user_id): array
{
    return obtenerMovimientosFiltrados($conn, $user_id, []);
}

/**
 * Totales de una lista de movimientos ya filtrada (cantidad, ingresos, gastos y saldo).
 */
function resumirMovimientos(array $movimientos): array
{
    $ingresos = 0.0;
    $gastos   = 0.0;

    foreach ($movimientos as $mov) {
        if ($mov['tipo'] === 'ingreso') {
            $ingresos += (float) $mov['monto'];
        } else {
            $gastos += (float) $mov['monto'];
        }
    }

    return [
        'total'    => count($movimientos),
        'ingresos' => $ingresos,
        'gastos'   => $gastos,
        'saldo'    => $ingresos - $gastos,
    ];
}

/**
 * Obtiene los rubros del usuario para poblar el select en el formulario de movimientos.
 */
function obtenerRubrosParaSelect($conn, $user_id): array
{
    $rubros = [];
    $sql = "SELECT r.id_rubro, r.nombre AS rubro_nombre, c.nombre AS categoria_nombre 
            FROM rubros r
            INNER JOIN categorias c ON r.id_categoria = c.id_categoria
            WHERE c.id_usuario = ? 
            ORDER BY c.nombre ASC, r.nombre ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "i", $u_id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $rubros[] = $fila;
        }
        mysqli_stmt_close($stmt);
    }
    return $rubros;
}

/**
 * Inserta un nuevo movimiento financiero validando que el rubro pertenezca al usuario.
 */
function crearMovimiento($conn, $user_id, $id_rubro, $tipo, $monto, $fecha, $descripcion): bool
{
    // 1. Validar que el rubro pertenezca a una categoría del usuario
    $sqlCheck = "SELECT r.id_rubro FROM rubros r 
                 INNER JOIN categorias c ON r.id_categoria = c.id_categoria 
                 WHERE r.id_rubro = ? AND c.id_usuario = ?";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    if ($stmtCheck) {
        $r_id = (int) $id_rubro;
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmtCheck, "ii", $r_id, $u_id);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        if (mysqli_num_rows($resCheck) === 0) {
            mysqli_stmt_close($stmtCheck);
            return false; // El rubro no es del usuario
        }
        mysqli_stmt_close($stmtCheck);
    }

    // 2. Insertar el movimiento
    $sql = "INSERT INTO movimientos (id_rubro, tipo, monto, fecha, descripcion) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        try {
            $r_id  = (int) $id_rubro;
            $t     = $tipo; // 'ingreso' o 'gasto'
            $m     = (float) $monto;
            $f     = empty($fecha) ? date('Y-m-d H:i:s') : $fecha;
            $d     = trim($descripcion);

            mysqli_stmt_bind_param($stmt, "isdss", $r_id, $t, $m, $f, $d);
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $resultado;
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    return false;
}