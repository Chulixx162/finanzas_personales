
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/users/perfil.php');
    exit;
}

// Protección CSRF pendiente de integrar en el formulario.
// No procesar solicitudes de cambio hasta incorporar el token.

require_once __DIR__ . '/../config/connection.php';

function volver(string $parametro): never {
    header('Location: ../views/users/perfil.php?' . $parametro);
    exit;
}

$id = (int) $_SESSION['user_id'];
$accion = $_POST['accion'] ?? '';

$stmt = $conn->prepare(
    'SELECT id_usuario, nombre, correo, contrasena
     FROM usuarios
     WHERE id_usuario = ?
     LIMIT 1'
);

if (!$stmt) {
    volver('error=actualizacion');
}

$stmt->bind_param('i', $id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../views/auth/login.php');
    exit;
}

$passwordActual = $_POST['contrasena_actual'] ?? '';

if (!password_verify($passwordActual, $usuario['contrasena'])) {
    volver('error=contrasena');
}

if ($accion === 'actualizar') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $nueva = $_POST['contrasena_nueva'] ?? '';

    if (
        $nombre === '' ||
        mb_strlen($nombre) > 100 ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        mb_strlen($correo) > 150 ||
        ($nueva !== '' && strlen($nueva) < 8)
    ) {
        volver('error=datos');
    }

    // Comprobar si el correo pertenece a otro usuario.
    $stmt = $conn->prepare(
        'SELECT id_usuario FROM usuarios
         WHERE correo = ? AND id_usuario <> ?
         LIMIT 1'
    );
    $stmt->bind_param('si', $correo, $id);
    $stmt->execute();
    $ocupado = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($ocupado) {
        volver('error=correo');
    }

    if ($nueva !== '') {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            'UPDATE usuarios
             SET nombre = ?, correo = ?, contrasena = ?
             WHERE id_usuario = ?'
        );
        $stmt->bind_param('sssi', $nombre, $correo, $hash, $id);
    } else {
        $stmt = $conn->prepare(
            'UPDATE usuarios
             SET nombre = ?, correo = ?
             WHERE id_usuario = ?'
        );
        $stmt->bind_param('ssi', $nombre, $correo, $id);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        volver('error=actualizacion');
    }

    $stmt->close();

    $_SESSION['user_nombre'] = $nombre;
    $_SESSION['user_email'] = $correo;

    volver('mensaje=actualizado');
}

if ($accion === 'eliminar') {
    if (($_POST['confirmar_eliminacion'] ?? '') !== 'si') {
        volver('error=datos');
    }

    $stmt = $conn->prepare(
        'DELETE FROM usuarios WHERE id_usuario = ?'
    );
    $stmt->bind_param('i', $id);

    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        $stmt->close();
        volver('error=eliminacion');
    }

    $stmt->close();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => $cookie['samesite'] ?? 'Lax'
        ]);
    }

    session_destroy();

    header('Location: ../views/auth/login.php');
    exit;
}

volver('error=datos');
