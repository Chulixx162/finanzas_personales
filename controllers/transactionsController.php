<?php
// controllers/transactionsController.php
// Sesión y guardia de ruta privada (igual que el resto de controladores).
require_once __DIR__ . '/../config/session.php';

$user_id = requerir_login();

$base = '../';

if (!isset($_SESSION['user_id'])) {
    redirigir('views/auth/login.php');
    exit();
}

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/transactionsModel.php';
require_once __DIR__ . '/../models/budgetHistoryModel.php';

$action    = $_GET['action'] ?? 'index';
$MAX_MONTO = 9999999999.99; // Límite para DECIMAL(12,2)

switch ($action) {
    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rubro    = $_POST['id_rubro'] ?? null;
            $tipo        = $_POST['tipo'] ?? '';
            $monto       = (float) ($_POST['monto'] ?? 0);
            $fecha       = $_POST['fecha'] ?? date('Y-m-d H:i:s');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $creado      = false;

            if ($id_rubro && in_array($tipo, ['ingreso', 'gasto']) && $monto > 0 && $monto <= $MAX_MONTO) {
                $creado = crearMovimiento($conn, $user_id, $id_rubro, $tipo, $monto, $fecha, $descripcion);
            }

            $destino = $creado
                ? 'success=created'
                : 'error=' . urlencode('No se pudo registrar el movimiento. Revisa los datos.');

            // Los gastos sí pueden superar el presupuesto mensual: se registran
            // igual y se avisa al pasar el 80 % (alerta80) o el 100 % (excede).
            if ($creado) {
                $destino .= parametrosAlertaPresupuesto($user_id, $tipo, (string) $fecha);
            }

            redirigir('views/transactions/index.php?' . $destino);
        }
        break;

    case 'create':
        $rubros = obtenerRubrosParaSelect($conn, $user_id);
        break;

    case 'edit': // Formulario para corregir un movimiento existente
        $id_movimiento = (int) ($_GET['id'] ?? 0);
        $movimiento    = $id_movimiento > 0 ? obtenerMovimientoPorId($conn, $user_id, $id_movimiento) : null;
        $rubros        = obtenerRubrosParaSelect($conn, $user_id);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrf = $_SESSION['csrf_token'];
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('views/transactions/index.php');
            exit();
        }

        $id_movimiento = (int) ($_POST['id_movimiento'] ?? 0);
        $volver        = "views/transactions/edit.php?id=" . $id_movimiento . "&";

        // Protección CSRF (mismo token que usa el formulario de presupuesto)
        $tokenSesion     = $_SESSION['csrf_token'] ?? '';
        $tokenFormulario = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

        if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
            redirigir($volver . "error=" . urlencode('La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.'));
            exit();
        }

        $id_rubro    = (int) ($_POST['id_rubro'] ?? 0);
        $tipo        = $_POST['tipo'] ?? '';
        $monto       = (float) ($_POST['monto'] ?? 0);
        $fecha       = normalizarFechaMovimiento($_POST['fecha'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($id_movimiento <= 0 || $id_rubro <= 0 || !in_array($tipo, ['ingreso', 'gasto'], true)
            || $monto <= 0 || $monto > $MAX_MONTO || $fecha === null || mb_strlen($descripcion) > 255) {
            redirigir($volver . "error=" . urlencode('Revisa los datos: el monto, la fecha, el rubro y la descripción (máx. 255 caracteres).'));
            exit();
        }

        if (!actualizarMovimiento($conn, $user_id, $id_movimiento, $id_rubro, $tipo, $monto, $fecha, $descripcion)) {
            redirigir($volver . "error=" . urlencode('No se pudo actualizar el movimiento.'));
            exit();
        }

        redirigir('views/transactions/index.php?success=updated'
            . parametrosAlertaPresupuesto($user_id, $tipo, $fecha));

    default: // 'index': historial con filtros (periodo, tipo y categoría)
        $categorias  = obtenerCategoriasParaFiltro($conn, $user_id);
        $filtros     = normalizarFiltrosMovimientos($_GET, $categorias);
        $movimientos = obtenerMovimientosFiltrados($conn, $user_id, $filtros);
        $resumen     = resumirMovimientos($movimientos);
        $hayFiltros  = hayFiltrosActivos($filtros);

        // Estado del presupuesto del mes (para el aviso fijo del historial)
        $alertaPresupuesto = evaluarAlertaPresupuesto($user_id);
        break;
}