<?php
// ¿Se abrió este archivo directamente (formulario o fetch) o lo incluyó la vista?
$accesoDirecto = basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__);

if ($accesoDirecto) {
    // Retiene cualquier texto suelto (un echo olvidado, un aviso de PHP) para que no
    // se mezcle con la respuesta JSON ni impida las redirecciones.
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/usersModel.php';

// Ruta hacia las vistas según desde dónde se ejecute este archivo:
//   directo (formulario o fetch) -> estamos en controllers/ -> ../views/
//   incluido por la vista        -> estamos en views/users/ -> ../
define('RUTA_VISTAS', $accesoDirecto ? '../views/' : '../');

$esAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

/* ---------- Respuestas ---------- */

// Estado HTTP de cada error en las respuestas JSON.
const ESTADOS_ERROR = [
    'datos'         => 422,
    'correo'        => 409,
    'contrasena'    => 403,
    'csrf'          => 403,
    'sesion'        => 401,
    'actualizacion' => 500,
    'eliminacion'   => 500,
];

function responder(array $datos, int $estado = 200)
{
    // Descarta lo retenido para enviar solo el JSON; se deja constancia en el log.
    $sobrante = '';
    while (ob_get_level() > 0) {
        $sobrante .= (string) ob_get_clean();
    }
    if (trim($sobrante) !== '') {
        error_log('profileController.php: salida inesperada antes del JSON: ' . $sobrante);
    }

    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function fallar(string $codigo)
{
    global $esAjax;

    if ($esAjax) {
        responder(['ok' => false, 'error' => $codigo], ESTADOS_ERROR[$codigo] ?? 400);
    }

    header('Location: ' . RUTA_VISTAS . 'users/profile.php?error=' . $codigo);
    exit;
}

function cerrar_sesion()
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $cookie['path'],
            'domain'   => $cookie['domain'],
            'secure'   => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => $cookie['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

// Lee un campo de $_POST como texto; si no es texto (por ejemplo un arreglo) devuelve ''.
function campo(string $nombre): string
{
    $valor = $_POST[$nombre] ?? '';
    return is_string($valor) ? $valor : '';
}

/* ---------- Acciones ---------- */

function actualizar_perfil(int $idUsuario)
{
    global $esAjax;

    $nombre = trim(campo('nombre'));
    $nueva  = campo('contrasena_nueva');

    // El correo NO se puede cambiar: siempre se usa el que ya está guardado
    // y se ignora cualquier valor "correo" que llegue en la petición.
    $guardado = obtener_usuario_por_id($idUsuario);
    if (!$guardado) {
        fallar('actualizacion');
    }
    $correo = $guardado['correo'];

    // Límite igual a la columna: nombre VARCHAR(50).
    if (
        mb_strlen($nombre) < 2 ||
        mb_strlen($nombre) > 50 ||
        ($nueva !== '' && strlen($nueva) < 8)
    ) {
        fallar('datos');
    }

    if (!actualizar_usuario($idUsuario, $nombre, $correo, $nueva)) {
        fallar('actualizacion');
    }

    // Mantener la sesión al día con los datos nuevos.
    $_SESSION['user_nombre'] = $nombre;
    $_SESSION['user_email']  = $correo;

    if ($nueva !== '') {
        session_regenerate_id(true);
    }

    if ($esAjax) {
        responder(['ok' => true, 'nombre' => $nombre, 'correo' => $correo]);
    }

    header('Location: ' . RUTA_VISTAS . 'users/profile.php?mensaje=actualizado');
    exit;
}

function eliminar_cuenta(int $idUsuario)
{
    if (campo('confirmar_eliminacion') !== 'si') {
        fallar('datos');
    }

    if (!eliminar_usuario($idUsuario)) {
        fallar('eliminacion');
    }

    cerrar_sesion();
    header('Location: ' . RUTA_VISTAS . 'auth/login.php');
    exit;
}

/* ---------- Sesión ---------- */

if (
    !isset($_SESSION['user_id']) ||
    !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)
) {
    if ($esAjax) {
        responder(['ok' => false, 'error' => 'sesion'], 401);
    }

    header('Location: ' . RUTA_VISTAS . 'auth/login.php?error=sesion');
    exit;
}

$idUsuario = (int) $_SESSION['user_id'];

/* ---------- Ruta POST: acciones del formulario ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = campo('accion');

    // Protección CSRF.
    $tokenSesion = $_SESSION['csrf_token'] ?? '';

    if ($tokenSesion === '' || !hash_equals($tokenSesion, campo('csrf_token'))) {
        fallar('csrf');
    }

    try {
        // El usuario debe seguir existiendo en la base de datos.
        $credenciales = obtener_credenciales_usuario($idUsuario);

        if (!$credenciales) {
            cerrar_sesion();

            if ($esAjax) {
                responder(['ok' => false, 'error' => 'sesion'], 401);
            }

            header('Location: ' . RUTA_VISTAS . 'auth/login.php');
            exit;
        }

        // Ambas acciones exigen la contraseña actual.
        $passwordActual = campo('contrasena_actual');

        if (
            $passwordActual === '' ||
            !password_verify($passwordActual, $credenciales['contrasena'])
        ) {
            fallar('contrasena');
        }

        if ($accion === 'actualizar') {
            actualizar_perfil($idUsuario);
        }

        if ($accion === 'eliminar') {
            eliminar_cuenta($idUsuario);
        }

        fallar('datos');
    } catch (Throwable $e) {
        error_log('profileController.php: ' . $e->getMessage());

        // 1062 = correo duplicado (otro usuario lo registró en el mismo instante).
        if ($e instanceof mysqli_sql_exception && (int) $e->getCode() === 1062) {
            fallar('correo');
        }

        fallar($accion === 'eliminar' ? 'eliminacion' : 'actualizacion');
    }
}

/* ---------- Ruta GET: la vista incluyó este archivo ---------- */

if ($accesoDirecto) {
    // Escribieron la dirección del controlador: llevarlos a la vista.
    header('Location: ../views/users/profile.php');
    exit;
}

$usuario = obtener_usuario_por_id($idUsuario);

// Si el usuario ya no existe, cerrar la sesión.
if (!$usuario) {
    cerrar_sesion();

    header('Location: ' . RUTA_VISTAS . 'auth/login.php?error=sesion');
    exit;
}

// Mantener la sesión al día con los datos guardados.
$_SESSION['user_nombre'] = $usuario['nombre'];
$_SESSION['user_email']  = $usuario['correo'];

// Token CSRF que la vista pone en los formularios.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['csrf_token'];