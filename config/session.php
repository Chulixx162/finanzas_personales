<?php
// config/session.php
// Configuración central de la sesión. Todo archivo que necesite sesión debe
// incluir este archivo y llamar a iniciar_sesion_segura() en lugar de session_start().

/**
 * Inicia la sesión con cookies seguras (una sola vez).
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

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    session_set_cookie_params([
        'lifetime' => 0,          // se borra al cerrar el navegador
        'path'     => '/',
        'httponly' => true,       // JavaScript no puede leer la cookie
        'samesite' => 'Lax',      // no se envía en peticiones cruzadas
        'secure'   => $https,     // solo por HTTPS cuando el sitio lo usa
    ]);

    session_start();
}

/**
 * Guardia de las rutas privadas.
 * Si no hay sesión válida redirige al login y detiene el script.
 * Si la hay, devuelve el id del usuario (siempre sale de la sesión).
 */
function requerir_login(): int
{
    iniciar_sesion_segura();

    if (
        !isset($_SESSION['user_id']) ||
        !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)
    ) {
        header('Location: ../views/auth/login.php?error=sesion');
        exit;
    }

    // Que el navegador no guarde páginas privadas (botón "atrás" tras cerrar sesión).
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    return (int) $_SESSION['user_id'];
}

/**
 * Cierra la sesión por completo: variables, cookie y archivo de sesión.
 */
function destruir_sesion(): void
{
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