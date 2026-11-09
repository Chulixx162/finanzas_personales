<?php
// controllers/budgetHistoryController.php
// Maneja la sesión, valida el formulario y prepara los datos; las consultas están en el modelo.
//
//  - POST (formulario "Cambiar presupuesto")  -> guarda y redirige a la vista.
//  - GET  (la vista lo incluye)               -> prepara los datos para mostrar.

require_once __DIR__ . '/../config/session.php';

// 1. Comprobar que la persona haya iniciado sesión.
$idUsuario = requerir_login();

require_once __DIR__ . '/../models/budgetHistoryModel.php';

$vista = '../views/presupuestos/index.php';

// 2. Mensajes que muestra la vista: [tipo, texto]
$mensajes = [
    'actualizado' => ['ok',    'Presupuesto actualizado. El cambio quedó registrado en tu historial.'],
    'sin_cambio'  => ['info',  'El valor es igual a tu presupuesto actual, no se registró ningún cambio.'],
    'vacio'       => ['error', 'Escribe el nuevo valor del presupuesto.'],
    'invalido'    => ['error', 'El presupuesto debe ser un número mayor o igual a cero.'],
    'muy_grande'  => ['error', 'El presupuesto supera el máximo permitido ($9.999.999.999).'],
    'csrf'        => ['error', 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.'],
    'error'       => ['error', 'No se pudo guardar el cambio. Inténtalo de nuevo.'],
];

// Redirige a la vista con un aviso y detiene el script.
function volver_con_aviso(string $vista, string $aviso): void
{
    header('Location: ' . $vista . '?aviso=' . $aviso);
    exit;
}

// 3. Guardar un cambio (solo POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Protección CSRF (mismo token que usa el perfil).
    $tokenSesion     = $_SESSION['csrf_token'] ?? '';
    $tokenFormulario = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

    if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
        volver_con_aviso($vista, 'csrf');
    }

    // Se aceptan formatos como 1500000, 1.500.000 o $1.500.000 (pesos enteros).
    $crudo = is_string($_POST['presupuesto'] ?? null) ? trim($_POST['presupuesto']) : '';

    if ($crudo === '') {
        volver_con_aviso($vista, 'vacio');
    }

    // Solo dígitos, con "$" inicial y puntos o espacios como separador de miles.
    if (!preg_match('/^\$?\s*\d[\d.\s]*$/', $crudo)) {
        volver_con_aviso($vista, 'invalido');
    }

    $digitos = preg_replace('/\D/', '', $crudo);

    // DECIMAL(12,2) admite hasta 9.999.999.999,99
    if (strlen(ltrim($digitos, '0')) > 10) {
        volver_con_aviso($vista, 'muy_grande');
    }

    $nuevo  = (float) $digitos;
    $actual = obtener_presupuesto_actual($idUsuario);

    if ($actual !== null && abs($actual - $nuevo) < 0.005) {
        volver_con_aviso($vista, 'sin_cambio');
    }

    // Los rubros no pueden pasar del presupuesto: no se permite BAJARLO por
    // debajo de lo que ya suman los rubros en un mes.
    $minimo = obtener_max_asignado_mensual($idUsuario);

    if ($nuevo + 0.005 < $minimo && ($actual === null || $nuevo < $actual)) {
        header('Location: ' . $vista . '?aviso=menor_asignado&min=' . (int) ceil($minimo));
        exit;
    }

    $guardado = actualizar_presupuesto_mensual($idUsuario, $nuevo);
    volver_con_aviso($vista, $guardado ? 'actualizado' : 'error');
}

// 4. Si abrieron el controlador directamente por GET, llevarlos a la vista.
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header('Location: ' . $vista);
    exit;
}

// 5. Datos que usa la vista.
//    El id sale SIEMPRE de la sesión, nunca de la URL ni de un formulario:
//    cada persona solo ve y cambia su propio presupuesto.
$aviso = $mensajes[$_GET['aviso'] ?? ''] ?? null;

// Aviso con cifra: el presupuesto no puede ser menor a lo asignado a los rubros.
if (($_GET['aviso'] ?? '') === 'menor_asignado') {
    $minimoAviso = max(0, (int) ($_GET['min'] ?? 0));
    $aviso = ['error', 'Tus rubros ya suman $' . number_format($minimoAviso, 0, ',', '.')
        . ' en un mes, así que tu presupuesto no puede ser menor. Reduce tus rubros primero o elige un valor mayor.'];
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$historial         = obtener_historial_presupuesto($idUsuario);
$minAsignado       = obtener_max_asignado_mensual($idUsuario);
$presupuestoActual = obtener_presupuesto_actual($idUsuario);

$totalCambios = count($historial);
$ultimoCambio = $historial[0]['fecha_cambio'] ?? null;

// 6. Uso del presupuesto del mes actual (categorías divididas en rubros).
$usoCategorias  = obtener_uso_presupuesto($idUsuario);
$totalAsignado  = array_sum(array_column($usoCategorias, 'asignado'));
$totalGastado   = array_sum(array_column($usoCategorias, 'gastado'));

// Porcentajes sobre el presupuesto mensual (null si aún no hay presupuesto definido).
$hayPresupuesto = $presupuestoActual !== null && $presupuestoActual > 0;
$pctAsignado    = $hayPresupuesto ? round($totalAsignado / $presupuestoActual * 100, 1) : null;
$pctGastado     = $hayPresupuesto ? round($totalGastado  / $presupuestoActual * 100, 1) : null;
