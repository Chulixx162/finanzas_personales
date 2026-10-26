<?php
// controllers/perfileController.php
// Procesa el formulario de perfil: actualizar datos y eliminar la cuenta.
//
// Si la petición viene de fetch() (cabecera X-Requested-With) responde JSON y
// la página no se recarga. Sin JavaScript, el formulario sigue funcionando con
// redirecciones normales.

// Retiene cualquier texto suelto (un echo olvidado, un aviso de PHP) para que no
// se mezcle con la respuesta JSON ni impida las redirecciones.
ob_start();

session_start();

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
        error_log('perfile.php: salida inesperada antes del JSON: ' . $sobrante);
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

    header('Location: ../views/users/perfile.php?error=' . $codigo);
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

// Ejecuta una sentencia con un único parámetro entero y devuelve las filas afectadas.
function ejecutar(mysqli $conn, string $sql, int $id): int
{
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $filas = $stmt->affected_rows;
    $stmt->close();

    return $filas;
}

// Lee un campo de $_POST como texto; si no es texto (por ejemplo un arreglo) devuelve ''.
function campo(string $nombre): string
{
    $valor = $_POST[$nombre] ?? '';
    return is_string($valor) ? $valor : '';
}

/* ---------- Comprobaciones iniciales ---------- */

if (!isset($_SESSION['user_id'])) {
    if ($esAjax) {
        responder(['ok' => false, 'error' => 'sesion'], 401);
    }

    header('Location: ../views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/users/perfile.php');
    exit;
}

require_once __DIR__ . '/../config/connection.php';

// Hace que mysqli lance excepciones en cualquier versión de PHP,
// así los errores de la base de datos se capturan abajo con try/catch.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ---------- Protección CSRF ---------- */

$tokenSesion = $_SESSION['csrf_token'] ?? '';

if ($tokenSesion === '' || !hash_equals($tokenSesion, campo('csrf_token'))) {
    fallar('csrf');
}

/* ---------- Datos comunes ---------- */

$id             = (int) $_SESSION['user_id'];
$accion         = campo('accion');
$passwordActual = campo('contrasena_actual');

try {
    // El usuario debe seguir existiendo en la base de datos.
    $stmt = $conn->prepare(
        'SELECT id_usuario, contrasena FROM usuarios WHERE id_usuario = ? LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        cerrar_sesion();

        if ($esAjax) {
            responder(['ok' => false, 'error' => 'sesion'], 401);
        }

        header('Location: ../views/auth/login.php');
        exit;
    }

    // Ambas acciones exigen la contraseña actual.
    if ($passwordActual === '' || !password_verify($passwordActual, $usuario['contrasena'])) {
        fallar('contrasena');
    }

    /* ---------- Guardar cambios ---------- */
    if ($accion === 'actualizar') {
        $nombre = trim(campo('nombre'));
        $correo = strtolower(trim(campo('correo')));
        $nueva  = campo('contrasena_nueva');

        // Límites iguales a las columnas: nombre VARCHAR(50), correo VARCHAR(100).
        if (
            mb_strlen($nombre) < 2 ||
            mb_strlen($nombre) > 50 ||
            !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
            mb_strlen($correo) > 100 ||
            ($nueva !== '' && strlen($nueva) < 8)
        ) {
            fallar('datos');
        }

        // El correo no puede pertenecer a otro usuario.
        $stmt = $conn->prepare(
            'SELECT 1 FROM usuarios WHERE correo = ? AND id_usuario <> ? LIMIT 1'
        );
        $stmt->bind_param('si', $correo, $id);
        $stmt->execute();
        $ocupado = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($ocupado) {
            fallar('correo');
        }

        if ($nueva !== '') {
            $hash = password_hash($nueva, PASSWORD_BCRYPT);
            $stmt = $conn->prepare(
                'UPDATE usuarios SET nombre = ?, correo = ?, contrasena = ? WHERE id_usuario = ?'
            );
            $stmt->bind_param('sssi', $nombre, $correo, $hash, $id);
        } else {
            $stmt = $conn->prepare(
                'UPDATE usuarios SET nombre = ?, correo = ? WHERE id_usuario = ?'
            );
            $stmt->bind_param('ssi', $nombre, $correo, $id);
        }

        $stmt->execute();
        $stmt->close();

        // Mantener la sesión al día con los datos nuevos.
        $_SESSION['user_nombre'] = $nombre;
        $_SESSION['user_email']  = $correo;

        if ($nueva !== '') {
            session_regenerate_id(true);
        }

        if ($esAjax) {
            responder(['ok' => true, 'nombre' => $nombre, 'correo' => $correo]);
        }

        header('Location: ../views/users/perfile.php?mensaje=actualizado');
        exit;
    }

    /* ---------- Eliminar cuenta ---------- */
    if ($accion === 'eliminar') {
        if (campo('confirmar_eliminacion') !== 'si') {
            fallar('datos');
        }

        // Las claves foráneas son ON DELETE RESTRICT, así que primero se borran
        // los datos del usuario, de los más internos a los más externos.
        $conn->begin_transaction();

        try {
            ejecutar($conn,
                'DELETE m FROM movimientos m
                 JOIN rubros r ON r.id_rubro = m.id_rubro
                 JOIN categorias c ON c.id_categoria = r.id_categoria
                 WHERE c.id_usuario = ?', $id);

            ejecutar($conn,
                'DELETE r FROM rubros r
                 JOIN categorias c ON c.id_categoria = r.id_categoria
                 WHERE c.id_usuario = ?', $id);

            ejecutar($conn, 'DELETE FROM categorias WHERE id_usuario = ?', $id);
            ejecutar($conn, 'DELETE FROM historial_presupuesto WHERE id_usuario = ?', $id);

            $borrados = ejecutar($conn, 'DELETE FROM usuarios WHERE id_usuario = ?', $id);

            if ($borrados !== 1) {
                throw new RuntimeException('No se eliminó el usuario.');
            }

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }

        cerrar_sesion();
        header('Location: ../views/auth/login.php');
        exit;
    }

    fallar('datos');
} catch (Throwable $e) {
    error_log('perfile.php: ' . $e->getMessage());

    // 1062 = correo duplicado (otro usuario lo registró en el mismo instante).
    if ($e instanceof mysqli_sql_exception && (int) $e->getCode() === 1062) {
        fallar('correo');
    }

    fallar($accion === 'eliminar' ? 'eliminacion' : 'actualizacion');
}