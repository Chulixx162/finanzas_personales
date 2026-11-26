<?php
// config/session.php
// Configuración central de la sesión. Todo archivo que necesite sesión debe
// incluir este archivo y llamar a iniciar_sesion_segura() en lugar de session_start().

require_once __DIR__ . '/app.php';

// RNF-05 / CP-08: la sesión expira tras 30 minutos sin actividad.
const SESION_INACTIVIDAD = 1800;

/**
 * @deprecated Usa redirigir() / app_url() de config/app.php.
 * Ruta relativa desde el script que se está ejecutando hasta la raíz del proyecto.
 * Se conserva solo por compatibilidad; ya nada del proyecto la usa.
 */
function ruta_a_raiz(): string
{
    $proyecto = rtrim(str_replace('\\', '/', (string) realpath(__DIR__ . '/..')), '/');
    $script   = str_replace('\\', '/', (string) realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));

    if ($proyecto === '' || strpos($script, $proyecto . '/') !== 0) {
        return '../';
    }

    $relativo = substr($script, strlen($proyecto) + 1);

    return str_repeat('../', substr_count($relativo, '/'));
}

/**
 * Inicia la sesión con cookies seguras (una sola vez) y cierra la sesión
 * si el usuario superó el tiempo máximo de inactividad.
 */
function iniciar_sesion_segura(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    // Rechaza identificadores de sesión que el servidor no haya creado.
    ini_set('session.use_strict_mode', '1');
    // La sesión viaja solo por cookie, nunca por la URL.
    ini_set('session.use_only_cookies', '1');
    // El servidor puede borrar los archivos de sesión inactivos pasado el límite.
    ini_set('session.gc_maxlifetime', (string) SESION_INACTIVIDAD);

    // Detrás de un proxy (Docker en producción) se apoya en X-Forwarded-Proto.
    $https = app_es_https();

    session_set_cookie_params([
        'lifetime' => 0,          // se borra al cerrar el navegador
        'path'     => '/',
        'httponly' => true,       // JavaScript no puede leer la cookie
        'samesite' => 'Lax',      // no se envía en peticiones cruzadas
        'secure'   => $https,     // solo por HTTPS cuando el sitio lo usa
    ]);

    session_start();

    expirar_si_inactiva();
    restaurar_sesion_recordada();
}

/**
 * Si hay un usuario con sesión pero lleva más de 30 minutos sin actividad,
 * se borran sus datos y se deja una marca para avisarle en el login.
 */
function expirar_si_inactiva(): void
{
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $ultimaActividad = (int) ($_SESSION['ultima_actividad'] ?? 0);

    if ($ultimaActividad > 0 && (time() - $ultimaActividad) > SESION_INACTIVIDAD) {
        $_SESSION = [];

        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['sesion_expirada'] = true;
    }
}

/**
 * Si no hay sesión pero existe una cookie "Recordarme" válida, inicia la sesión.
 * No actúa si la sesión acaba de expirar por inactividad (RNF-05).
 */
function restaurar_sesion_recordada(): void
{
    if (
        isset($_SESSION['user_id']) ||
        !empty($_SESSION['sesion_expirada']) ||
        empty($_COOKIE['recordarme'])
    ) {
        return;
    }

    require_once __DIR__ . '/../models/rememberModel.php';

    $usuario = recordarme_autenticar();
    if (!$usuario) {
        return;
    }

    if (!headers_sent()) {
        session_regenerate_id(true);
    }

    $_SESSION['user_id']     = (int) $usuario['id_usuario'];
    $_SESSION['user_nombre'] = $usuario['nombre'];
    $_SESSION['user_email']  = $usuario['correo'];

    registrar_actividad();
}

/**
 * Cabeceras para que el navegador NO guarde páginas privadas.
 * Así, al cerrar sesión y pulsar "atrás", el navegador vuelve a pedir la página al
 * servidor y este redirige al inicio en lugar de mostrar la cuenta.
 */
function enviar_cabeceras_privadas(): void
{
    if (headers_sent()) {
        return;
    }

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
    header('Pragma: no-cache');
    header('Expires: 0');
}

/**
 * Anota que el usuario sigue activo (reinicia el contador de 30 minutos).
 */
function registrar_actividad(): void
{
    $_SESSION['ultima_actividad'] = time();
}

/**
 * Segundos que le quedan a la sesión antes de expirar (0 si no hay sesión).
 * No reinicia el contador: solo consulta.
 */
function segundos_restantes_sesion(): int
{
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }

    $ultimaActividad = (int) ($_SESSION['ultima_actividad'] ?? time());

    return max(0, SESION_INACTIVIDAD - (time() - $ultimaActividad));
}

/**
 * Redirige a quien no tiene sesión válida y detiene el script.
 *   - Sesión cerrada o inexistente  -> página de inicio (public/index.php)
 *   - Sesión expirada por inactividad -> login con el aviso correspondiente
 */
function redirigir_sin_sesion(): void
{
    $expirada = !empty($_SESSION['sesion_expirada']);
    unset($_SESSION['sesion_expirada']);

    enviar_cabeceras_privadas();

    $destino = $expirada
        ? 'views/auth/login.php?error=expirada'
        : 'public/index.php';

    redirigir($destino);
}

/**
 * Guardia de las rutas privadas.
 * Si no hay sesión válida redirige y detiene el script.
 * Si la hay, devuelve el id del usuario (siempre sale de la sesión).
 */
function requerir_login(): int
{
    iniciar_sesion_segura();

    // Siempre: ninguna página privada se guarda en la caché del navegador.
    enviar_cabeceras_privadas();

    if (
        !isset($_SESSION['user_id']) ||
        !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)
    ) {
        redirigir_sin_sesion();
    }

    registrar_actividad();

    return (int) $_SESSION['user_id'];
}

/**
 * Cierra la sesión por completo: variables, cookie y archivo de sesión.
 */
function destruir_sesion(): void
{
    require_once __DIR__ . '/../models/rememberModel.php';
    recordarme_olvidar();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?: 'Lax',
        ]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}