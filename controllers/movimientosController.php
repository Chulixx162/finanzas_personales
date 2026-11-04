<?php
// controllers/movimientosController.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = '/finanzas_personales/';

if (!isset($_SESSION['user_id'])) {
    header("Location: /finanzas_personales/views/auth/login.php");
    exit();
}

require_once __DIR__ . '/../config/connection.php';
/** @var mysqli $conn */
require_once __DIR__ . '/../models/movimientosModel.php';

$user_id   = $_SESSION['user_id'];
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

            if ($id_rubro && in_array($tipo, ['ingreso', 'gasto']) && $monto > 0 && $monto <= $MAX_MONTO) {
                crearMovimiento($conn, $user_id, $id_rubro, $tipo, $monto, $fecha, $descripcion);
            }
            header("Location: /finanzas_personales/views/movimientos/index.php?success=created");
            exit();
        }
        break;

    case 'create':
        $rubros = obtenerRubrosParaSelect($conn, $user_id);
        break;

    default: // 'index'
        $movimientos = obtenerMovimientosPorUsuario($conn, $user_id);
        break;
}
?>