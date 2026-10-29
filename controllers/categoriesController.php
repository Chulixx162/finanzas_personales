<?php
// controllers/categoriesController.php
// Solo maneja la sesión y las rutas; las consultas están en el modelo.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = '/finanzas_personales/';

if (
    !isset($_SESSION['user_id']) ||
    !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)
) {
    header('Location: ' . $base . 'views/auth/login.php?error=sesion');
    exit;
}

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/categoriesModel.php';

$user_id = (int) $_SESSION['user_id'];
$action  = $_GET['action'] ?? 'index';
$listado = $base . 'views/categories/index.php';

// Mensajes que se muestran en las vistas: [tipo, texto]
$mensajes = [
    'creada'            => ['ok',    'Categoría creada correctamente.'],
    'actualizada'       => ['ok',    'Categoría actualizada correctamente.'],
    'eliminada'         => ['ok',    'Categoría eliminada correctamente.'],
    'nombre_vacio'      => ['error', 'El nombre de la categoría es obligatorio.'],
    'nombre_largo'      => ['error', 'El nombre no puede superar los 50 caracteres.'],
    'descripcion_larga' => ['error', 'La descripción no puede superar los 255 caracteres.'],
    'duplicada'         => ['error', 'Ya tienes una categoría con ese nombre.'],
    'no_existe'         => ['error', 'La categoría no existe o no tienes permisos sobre ella.'],
    'con_relaciones'    => ['error', 'No se puede eliminar: la categoría tiene rubros o movimientos asociados.'],
    'error_guardar'     => ['error', 'No se pudo guardar la categoría. Inténtalo de nuevo.'],
];
$aviso = $mensajes[$_GET['aviso'] ?? ''] ?? null;

/** Valida los campos del formulario. Devuelve el código de error o null. */
function validar_categoria(string $nombre, string $descripcion): ?string
{
    if ($nombre === '') {
        return 'nombre_vacio';
    }
    if (mb_strlen($nombre) > 50) {
        return 'nombre_largo';
    }
    if (mb_strlen($descripcion) > 255) {
        return 'descripcion_larga';
    }
    return null;
}

switch ($action) {
    case 'store':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $listado);
            exit;
        }

        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $formulario  = $base . 'views/categories/create.php';

        $error = validar_categoria($nombre, $descripcion);
        if ($error) {
            header('Location: ' . $formulario . '?aviso=' . $error);
            exit;
        }

        if (categoriaNombreExiste($conn, $user_id, $nombre)) {
            header('Location: ' . $formulario . '?aviso=duplicada');
            exit;
        }

        try {
            $guardada = crearCategoria($conn, $user_id, $nombre, $descripcion);
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $guardada = false;
        }

        header('Location: ' . ($guardada ? $listado . '?aviso=creada' : $formulario . '?aviso=error_guardar'));
        exit;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $listado);
            exit;
        }

        $id          = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!$id || !obtenerCategoriaPorId($conn, $user_id, $id)) {
            header('Location: ' . $listado . '?aviso=no_existe');
            exit;
        }

        $formulario = $base . 'views/categories/editar.php?id=' . $id;

        $error = validar_categoria($nombre, $descripcion);
        if ($error) {
            header('Location: ' . $formulario . '&aviso=' . $error);
            exit;
        }

        if (categoriaNombreExiste($conn, $user_id, $nombre, $id)) {
            header('Location: ' . $formulario . '&aviso=duplicada');
            exit;
        }

        try {
            $guardada = actualizarCategoria($conn, $user_id, $id, $nombre, $descripcion);
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $guardada = false;
        }

        header('Location: ' . ($guardada ? $listado . '?aviso=actualizada' : $formulario . '&aviso=error_guardar'));
        exit;

    case 'delete':
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || !obtenerCategoriaPorId($conn, $user_id, $id)) {
            header('Location: ' . $listado . '?aviso=no_existe');
            exit;
        }

        // No eliminar si tiene rubros o movimientos asociados.
        $relaciones = contarRelacionesCategoria($conn, $user_id, $id);
        if ($relaciones['rubros'] > 0 || $relaciones['movimientos'] > 0) {
            header('Location: ' . $listado . '?aviso=con_relaciones');
            exit;
        }

        try {
            $eliminada = eliminarCategoria($conn, $user_id, $id);
        } catch (mysqli_sql_exception $e) {
            // La llave foránea (ON DELETE RESTRICT) también protege la BD.
            error_log($e->getMessage());
            $eliminada = false;
        }

        header('Location: ' . $listado . '?aviso=' . ($eliminada ? 'eliminada' : 'con_relaciones'));
        exit;

    case 'edit':
        $id            = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $categoriaEdit = $id ? obtenerCategoriaPorId($conn, $user_id, $id) : null;
        break;

    default:
        $categorias = obtenerCategoriasPorUsuario($conn, $user_id);
        break;
}
?>