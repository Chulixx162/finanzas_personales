<?php
// controllers/budgetHistoryController.php
// Maneja la sesión, valida el formulario y prepara los datos; las consultas están en el modelo.
//
//  - POST (formulario "Cambiar presupuesto")  -> guarda y redirige a la vista.
//  - GET  (exportar reporte a CSV)            -> genera la descarga directa filtrada.
//  - GET  (exportar reporte a PDF)            -> carga la vista de impresión con gráficos.
//  - GET  (la vista lo incluye)               -> prepara los datos para mostrar.

require_once __DIR__ . '/../config/session.php';

// 1. Comprobar que la persona haya iniciado sesión
$idUsuario = requerir_login();
require_once __DIR__ . '/../models/budgetHistoryModel.php';

$vista = '../views/presupuestos/index.php';

// 2. Exportación a CSV con filtros aplicados y delimitador compatible con Excel
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $filtros = [
        'desde' => is_string($_GET['desde'] ?? null) ? trim($_GET['desde']) : '',
        'hasta' => is_string($_GET['hasta'] ?? null) ? trim($_GET['hasta']) : '',
        'tipo'  => is_string($_GET['tipo'] ?? null)  ? trim($_GET['tipo'])  : '',
    ];

    $movimientos = obtener_movimientos_reporte($idUsuario, $filtros);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_movimientos_' . date('Y-m-d') . '.csv');

    $salida = fopen('php://output', 'w');

    // BOM UTF-8 para visualización correcta de caracteres especiales en Excel
    fprintf($salida, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Cabeceras exactas de columna
    fputcsv($salida, ['ID', 'Tipo', 'Monto ($)', 'Fecha y Hora', 'Rubro', 'Categoría', 'Descripción'], ';');

    foreach ($movimientos as $m) {
        fputcsv($salida, [
            $m['id_movimiento'],
            ucfirst($m['tipo']),
            number_format((float) $m['monto'], 2, ',', '.'),
            date('d/m/Y H:i', strtotime($m['fecha'])),
            $m['rubro_nombre'],
            $m['categoria_nombre'],
            $m['descripcion'] ?? ''
        ], ';');
    }

    fclose($salida);
    exit;
}

// 3. Exportación a PDF (Carga la vista de impresión con cálculos en memoria)
if (isset($_GET['action']) && $_GET['action'] === 'export_pdf') {
    $filtros = [
        'desde' => is_string($_GET['desde'] ?? null) ? trim($_GET['desde']) : '',
        'hasta' => is_string($_GET['hasta'] ?? null) ? trim($_GET['hasta']) : '',
        'tipo'  => is_string($_GET['tipo'] ?? null)  ? trim($_GET['tipo'])  : '',
    ];

    $movimientos       = obtener_movimientos_reporte($idUsuario, $filtros);
    $presupuestoActual = obtener_presupuesto_actual($idUsuario); // Solo lectura en memoria

    require_once __DIR__ . '/../views/reports/print_pdf.php';
    exit;
}

// 4. Catálogo de mensajes
$mensajes = [
    'actualizado' => ['ok',    'Presupuesto actualizado. El cambio quedó registrado en tu historial.'],
    'sin_cambio'  => ['info',  'El valor es igual a tu presupuesto actual, no se registró ningún cambio.'],
    'vacio'       => ['error', 'Escribe el nuevo valor del presupuesto.'],
    'invalido'    => ['error', 'El presupuesto debe ser un número mayor o igual a cero.'],
    'muy_grande'  => ['error', 'El presupuesto supera el máximo permitido ($9.999.999.999).'],
    'csrf'        => ['error', 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.'],
    'error'       => ['error', 'No se pudo guardar el cambio. Inténtalo de nuevo.'],
];

// Redirige a la vista con un aviso y detiene el script
function volver_con_aviso(string $vista, string $aviso): void
{
    header('Location: ' . $vista . '?aviso=' . $aviso);
    exit;
}

// 5. Guardar un cambio de presupuesto (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenSesion     = $_SESSION['csrf_token'] ?? '';
    $tokenFormulario = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

    if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
        volver_con_aviso($vista, 'csrf');
    }

    // Acepta formatos numéricos como 1500000, 1.500.000 o $1.500.000
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

    // Los rubros no pueden superar el presupuesto
    $minimo = obtener_max_asignado_mensual($idUsuario);

    if ($nuevo + 0.005 < $minimo && ($actual === null || $nuevo < $actual)) {
        header('Location: ' . $vista . '?aviso=menor_asignado&min=' . (int) ceil($minimo));
        exit;
    }

    $guardado = actualizar_presupuesto_mensual($idUsuario, $nuevo);
    volver_con_aviso($vista, $guardado ? 'actualizado' : 'error');
}

// 6. Si acceden al controlador directamente por GET sin action, redirigir a la vista
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header('Location: ' . $vista);
    exit;
}

// 7. Preparación de variables para la vista (views/budget_history/index.php)
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

// Uso del presupuesto del mes actual por categorías
$usoCategorias  = obtener_uso_presupuesto($idUsuario);
$totalAsignado  = array_sum(array_column($usoCategorias, 'asignado'));
$totalGastado   = array_sum(array_column($usoCategorias, 'gastado'));

// Porcentajes calculados
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