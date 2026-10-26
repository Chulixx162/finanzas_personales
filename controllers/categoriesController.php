<?php
// controllers/categoriesController.php
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
require_once __DIR__ . '/../models/categoriesModel.php';

$user_id = $_SESSION['user_id'];
$action  = $_GET['action'] ?? 'index';

switch ($action) {
    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre      = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            if (!empty($nombre)) {
                crearCategoria($conn, $user_id, $nombre, $descripcion);
            }
            header("Location: /finanzas_personales/views/categories/index.php");
            exit();
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = $_POST['id'] ?? null;
            $nombre      = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            if ($id && !empty($nombre)) {
                actualizarCategoria($conn, $user_id, $id, $nombre, $descripcion);
            }
            header("Location: /finanzas_personales/views/categories/index.php");
            exit();
        }
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            eliminarCategoria($conn, $user_id, $id);
        }
        header("Location: /finanzas_personales/views/categories/index.php");
        exit();
        break;

    case 'edit':
        $id = $_GET['id'] ?? null;
        $categoriaEdit = null;
        if ($id) {
            $categoriaEdit = obtenerCategoriaPorId($conn, $user_id, $id);
        }
        break;

    default:
        $categorias = obtenerCategoriasPorUsuario($conn, $user_id);
        break;
}
?>