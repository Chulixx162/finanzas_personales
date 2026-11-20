<?php
// models/transactionsModel.php

/**
 * Periodos rápidos del filtro de fecha del historial de movimientos.
 * 'custom' permite escribir un rango "Desde / Hasta" a mano.
 */
const PERIODOS_MOVIMIENTOS = ['all', 'today', 'last_7', 'last_30', 'this_month', 'last_month', 'custom'];

/**
 * Porcentaje del presupuesto mensual a partir del cual se avisa al usuario.
 * Se compara con la suma de lo gastado en todos los rubros del mes.
 */
const UMBRAL_ALERTA_PRESUPUESTO = 80;

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

/**
 * Convierte la fecha de un formulario ('2026-11-20T14:30', '2026-11-20 14:30'
 * o con segundos) en 'Y-m-d H:i:s'. Devuelve null si no es una fecha real.
 */
function normalizarFechaMovimiento($valor): ?string
{
    if (!is_string($valor) || trim($valor) === '') {
        return null;
    }

    $valor = trim(str_replace('T', ' ', $valor));

    foreach (['Y-m-d H:i:s', 'Y-m-d H:i'] as $formato) {
        $fecha = DateTimeImmutable::createFromFormat('!' . $formato, $valor);

        if ($fecha && $fecha->format($formato) === $valor) {
            return $fecha->format('Y-m-d H:i:s');
        }
    }

    return null;
}

/**
 * Obtiene UN movimiento del usuario (con su rubro y categoría) para editarlo.
 * Devuelve null si no existe o si pertenece a otro usuario.
 */
function obtenerMovimientoPorId($conn, $user_id, $id_movimiento): ?array
{
    $sql = "SELECT m.id_movimiento, m.id_rubro, m.tipo, m.monto, m.fecha, m.descripcion,
                   r.nombre AS rubro_nombre, c.nombre AS categoria_nombre
            FROM movimientos m
            INNER JOIN rubros r ON m.id_rubro = r.id_rubro
            INNER JOIN categorias c ON r.id_categoria = c.id_categoria
            WHERE m.id_movimiento = ? AND c.id_usuario = ?
            LIMIT 1";

    try {
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log('obtenerMovimientoPorId (prepare): ' . mysqli_error($conn));
            return null;
        }

        $m_id = (int) $id_movimiento;
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmt, "ii", $m_id, $u_id);

        if (!mysqli_stmt_execute($stmt)) {
            error_log('obtenerMovimientoPorId (execute): ' . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return null;
        }

        $resultado = mysqli_stmt_get_result($stmt);
        $fila      = $resultado ? mysqli_fetch_assoc($resultado) : null;
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log('obtenerMovimientoPorId: ' . $e->getMessage());
        return null;
    }

    return $fila ?: null;
}

/**
 * Actualiza un movimiento del usuario. Exige que el movimiento y el rubro
 * elegido sean del usuario; el SQL repite esa condición para no depender solo
 * de la validación previa. La fecha debe venir ya normalizada ('Y-m-d H:i:s').
 */
function actualizarMovimiento($conn, $user_id, $id_movimiento, $id_rubro, $tipo, $monto, $fecha, $descripcion): bool
{
    if (!in_array($tipo, ['ingreso', 'gasto'], true)) {
        return false;
    }

    // 1. El movimiento debe ser del usuario
    if (obtenerMovimientoPorId($conn, $user_id, $id_movimiento) === null) {
        return false;
    }

    // 2. El rubro elegido debe ser del usuario
    $sqlCheck = "SELECT r.id_rubro FROM rubros r
                 INNER JOIN categorias c ON r.id_categoria = c.id_categoria
                 WHERE r.id_rubro = ? AND c.id_usuario = ?";

    try {
        $stmtCheck = mysqli_prepare($conn, $sqlCheck);

        if (!$stmtCheck) {
            error_log('actualizarMovimiento (prepare check): ' . mysqli_error($conn));
            return false;
        }

        $r_id = (int) $id_rubro;
        $u_id = (int) $user_id;
        mysqli_stmt_bind_param($stmtCheck, "ii", $r_id, $u_id);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $esDelUsuario = $resCheck && mysqli_num_rows($resCheck) > 0;
        mysqli_stmt_close($stmtCheck);

        if (!$esDelUsuario) {
            return false;
        }

        // 3. Actualizar (solo si el movimiento actual sigue siendo de un rubro del usuario)
        $sql = "UPDATE movimientos
                SET id_rubro = ?, tipo = ?, monto = ?, fecha = ?, descripcion = ?
                WHERE id_movimiento = ?
                  AND id_rubro IN (
                      SELECT r2.id_rubro FROM rubros r2
                      INNER JOIN categorias c2 ON r2.id_categoria = c2.id_categoria
                      WHERE c2.id_usuario = ?
                  )";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log('actualizarMovimiento (prepare): ' . mysqli_error($conn));
            return false;
        }

        $m_id = (int) $id_movimiento;
        $m    = (float) $monto;
        $d    = trim((string) $descripcion);
        mysqli_stmt_bind_param($stmt, "isdssii", $r_id, $tipo, $m, $fecha, $d, $m_id, $u_id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
    } catch (mysqli_sql_exception $e) {
        error_log('actualizarMovimiento: ' . $e->getMessage());
        return false;
    }
}

/**
 * Evalúa en qué punto va el presupuesto mensual del usuario.
 * Suma lo gastado en todos los rubros del mes actual y lo compara con el
 * presupuesto mensual establecido:
 *   'sin_presupuesto' -> no hay presupuesto definido
 *   'ok'              -> hasta el 80 % (inclusive)
 *   'aviso'           -> más del 80 % y hasta el 100 %
 *   'excedido'        -> más del 100 %
 * Necesita models/budgetHistoryModel.php (presupuesto y uso por categoría).
 */
function evaluarAlertaPresupuesto(int $user_id): array
{
    $alerta = [
        'nivel'       => 'sin_presupuesto',
        'gastado'     => 0.0,
        'presupuesto' => 0.0,
        'porcentaje'  => 0.0,
        'disponible'  => 0.0,
    ];

    $presupuesto = obtener_presupuesto_actual($user_id);

    if ($presupuesto === null || $presupuesto <= 0) {
        return $alerta;
    }

    $gastado = (float) array_sum(array_column(obtener_uso_presupuesto($user_id), 'gastado'));

    $alerta['gastado']     = $gastado;
    $alerta['presupuesto'] = $presupuesto;
    $alerta['porcentaje']  = $gastado / $presupuesto * 100;
    $alerta['disponible']  = max(0.0, $presupuesto - $gastado);

    // Se comparan los valores en centavos para evitar errores de decimales.
    if (round($gastado * 100) > round($presupuesto * 100)) {
        $alerta['nivel'] = 'excedido';
    } elseif (round($gastado * 100, 2) > round($presupuesto * UMBRAL_ALERTA_PRESUPUESTO, 2)) {
        $alerta['nivel'] = 'aviso';
    } else {
        $alerta['nivel'] = 'ok';
    }

    return $alerta;
}

/**
 * Parámetros de URL para avisar, tras guardar un movimiento, que el gasto del
 * mes pasó el 80 % ('alerta80=1') o el 100 % ('excede=1') del presupuesto.
 * Devuelve '' si el movimiento no es un gasto del mes actual o no hay alerta.
 */
function parametrosAlertaPresupuesto(int $user_id, string $tipo, string $fecha): string
{
    if ($tipo !== 'gasto' || substr($fecha, 0, 7) !== date('Y-m')) {
        return '';
    }

    $alerta = evaluarAlertaPresupuesto($user_id);

    if (!in_array($alerta['nivel'], ['aviso', 'excedido'], true)) {
        return '';
    }

    return ($alerta['nivel'] === 'excedido' ? '&excede=1' : '&alerta80=1')
        . '&gastado=' . (int) round($alerta['gastado'])
        . '&presupuesto=' . (int) round($alerta['presupuesto'])
        . '&porcentaje=' . number_format($alerta['porcentaje'], 1, '.', '');
}