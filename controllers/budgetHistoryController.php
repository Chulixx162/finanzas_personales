<?php
// controllers/budgetHistoryController.php
require_once __DIR__ . '/../config/session.php';

$idUsuario = requerir_login();
require_once __DIR__ . '/../models/budgetHistoryModel.php';

$vista = '../views/presupuestos/index.php';

$mensajes = [
    'actualizado' => ['ok',    'Presupuesto actualizado. El cambio quedó registrado en tu historial.'],
    'sin_cambio'  => ['info',  'El valor es igual a tu presupuesto actual, no se registró ningún cambio.'],
    'vacio'       => ['error', 'Escribe el nuevo valor del presupuesto.'],
    'invalido'    => ['error', 'El presupuesto debe ser un número mayor o igual a cero.'],
    'muy_grande'  => ['error', 'El presupuesto supera el máximo permitido ($9.999.999.999).'],
    'csrf'        => ['error', 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.'],
    'error'       => ['error', 'No se pudo guardar el cambio. Inténtalo de nuevo.'],
];

function volver_con_aviso(string $vista, string $aviso): void
{
    header('Location: ' . $vista . '?aviso=' . $aviso);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenSesion     = $_SESSION['csrf_token'] ?? '';
    $tokenFormulario = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

    if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
        volver_con_aviso($vista, 'csrf');
    }

    $crudo = is_string($_POST['presupuesto'] ?? null) ? trim($_POST['presupuesto']) : '';

    if ($crudo === '') {
        volver_con_aviso($vista, 'vacio');
    }

    if (!preg_match('/^\$?\s*\d[\d.\s]*$/', $crudo)) {
        volver_con_aviso($vista, 'invalido');
    }

    $digitos = preg_replace('/\D/', '', $crudo);

    if (strlen(ltrim($digitos, '0')) > 10) {
        volver_con_aviso($vista, 'muy_grande');
    }

    $nuevo  = (float) $digitos;
    $actual = obtener_presupuesto_actual($idUsuario);

    // CORREGIDO: Se agregó el operador de resta (-)
    if ($actual !== null && abs($actual - $nuevo) < 0.005) {
        volver_con_aviso($vista, 'sin_cambio');
    }

    $minimo = obtener_max_asignado_mensual($idUsuario);

    if ($nuevo + 0.005 < $minimo && ($actual === null || $nuevo < $actual)) {
        header('Location: ' . $vista . '?aviso=menor_asignado&min=' . (int) ceil($minimo));
        exit;
    }

    $guardado = actualizar_presupuesto_mensual($idUsuario, $nuevo);
    volver_con_aviso($vista, $guardado ? 'actualizado' : 'error');
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header('Location: ' . $vista);
    exit;
}

$aviso = $mensajes[$_GET['aviso'] ?? ''] ?? null;

if (($_GET['aviso'] ?? '') === 'menor_asignado') {
    $minimoAviso = max(0, (int) ($_GET['min'] ?? 0));
    $aviso = ['error', 'Tus rubros ya suman $' . number_format($minimoAviso, 0, ',', '.')
        . ' en un mes, así que tu presupuesto no puede ser menor. Reduce tus rubros primero o elige un valor mayor.'];
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$historial          = obtener_historial_presupuesto($idUsuario);
$minAsignado       = obtener_max_asignado_mensual($idUsuario);
$presupuestoActual = obtener_presupuesto_actual($idUsuario);

$totalCambios = count($historial);
$ultimoCambio = $historial[0]['fecha_cambio'] ?? null;

$usoCategorias  = obtener_uso_presupuesto($idUsuario);
$totalAsignado  = array_sum(array_column($usoCategorias, 'asignado'));
$totalGastado   = array_sum(array_column($usoCategorias, 'gastado'));

$hayPresupuesto = $presupuestoActual !== null && $presupuestoActual > 0;
// CORREGIDO: Se agregaron los operadores de división (/) y multiplicación (*)
$pctAsignado    = $hayPresupuesto ? round(($totalAsignado / $presupuestoActual) * 100, 1) : null;
$pctGastado     = $hayPresupuesto ? round(($totalGastado / $presupuestoActual) * 100, 1) : null;

// --- SECCIÓN NUEVA: Reporte Financiero Mensual ---
$mesesDisponibles = obtener_meses_con_movimientos($idUsuario);
$mesActualDefault = date('Y-m');

// Si no hay meses con movimientos, aseguramos al menos el mes actual
if (empty($mesesDisponibles)) {
    $mesesDisponibles[] = $mesActualDefault;
}

// Capturar el periodo de la URL (GET) o usar el primero disponible por defecto
$periodoSeleccionado = $_GET['periodo'] ?? $mesesDisponibles[0];

// Obtener el reporte del mes seleccionado
$reporteMensual = obtener_reporte_financiero_mensual($idUsuario, $periodoSeleccionado);