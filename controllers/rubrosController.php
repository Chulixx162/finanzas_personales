<?php
// controllers/rubrosController.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/session.php';

$idUsuario = requerir_login();

require_once __DIR__ . '/../config/connection.php';
/** @var mysqli $conn */
require_once __DIR__ . '/../models/rubrosModel.php';

$action    = $_GET['action'] ?? 'index';
$MAX_MONTO = 9999999999.99; // Límite estricto de la columna DECIMAL(12,2)

switch ($action) {
    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_categoria     = $_POST['id_categoria'] ?? null;
            $nombre           = trim($_POST['nombre'] ?? '');
            $monto_proyectado = (float) ($_POST['monto_proyectado'] ?? 0);
            $fecha_inicio     = $_POST['fecha_inicio'] ?? '';
            $fecha_fin        = $_POST['fecha_fin'] ?? '';

            if ($id_categoria && !empty($nombre) && $monto_proyectado >= 0 && $monto_proyectado <= $MAX_MONTO && !empty($fecha_inicio) && !empty($fecha_fin) && ($fecha_fin >= $fecha_inicio)) {
                crearRubro($conn, $id_categoria, $nombre, $monto_proyectado, $fecha_inicio, $fecha_fin);
            }
            header("Location: /finanzas_personales/views/rubros/index.php");
            exit();
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rubro         = $_POST['id_rubro'] ?? null;
            $id_categoria     = $_POST['id_categoria'] ?? null;
            $nombre           = trim($_POST['nombre'] ?? '');
            $monto_proyectado = (float) ($_POST['monto_proyectado'] ?? 0);
            $fecha_inicio     = $_POST['fecha_inicio'] ?? '';
            $fecha_fin        = $_POST['fecha_fin'] ?? '';

            if ($id_rubro && $id_categoria && !empty($nombre) && $monto_proyectado >= 0 && $monto_proyectado <= $MAX_MONTO && !empty($fecha_inicio) && !empty($fecha_fin) && ($fecha_fin >= $fecha_inicio)) {
                actualizarRubro($conn, $user_id, $id_rubro, $id_categoria, $nombre, $monto_proyectado, $fecha_inicio, $fecha_fin);
            }
            // Redirige con indicador de éxito en la actualización
            header("Location: /finanzas_personales/views/rubros/index.php?success=updated");
            exit();
        }
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $resultado = eliminarRubro($conn, $user_id, $id);
            if (!$resultado['success']) {
                $mensaje = urlencode($resultado['message']);
                header("Location: /finanzas_personales/views/rubros/index.php?error=$mensaje");
                exit();
            }
            header("Location: /finanzas_personales/views/rubros/index.php?success=deleted");
            exit();
        }
        header("Location: /finanzas_personales/views/rubros/index.php");
        exit();
        break;

    case 'create':
        $categorias = obtenerCategoriasParaSelect($conn, $user_id);
        break;

    case 'edit':
        $id = $_GET['id'] ?? null;
        $rubroEdit  = null;
        $categorias = obtenerCategoriasParaSelect($conn, $user_id);
        if ($id) {
            $rubroEdit = obtenerRubroPorId($conn, $user_id, $id);
        }
        break;

    default: // 'index'
        $rubros = obtenerRubrosPorUsuario($conn, $user_id);
        break;
}
?>