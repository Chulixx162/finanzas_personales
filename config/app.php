<?php
// config/app.php
// Configuración central de la aplicación: variables de entorno, rutas (URLs) y
// redirecciones. Es lo PRIMERO que cargan config/session.php y config/connection.php.
//
// Por qué existe: antes cada controlador redirigía con rutas relativas escritas a
// mano ('../views/...'). Eso funcionaba solo si el archivo estaba a una profundidad
// concreta y la app colgaba de la raíz del servidor. Ahora todas las redirecciones
// salen de redirigir(), que construye la URL desde una única base (APP_BASE_PATH),
// igual en Laragon, en Docker (desarrollo) y en producción detrás de un proxy.

require_once __DIR__ . '/env.php';

/* ==========================================================
   Entorno
   ========================================================== */

/** ¿Está activo el modo depuración? (APP_DEBUG=true). En producción debe ser false. */
function app_debug(): bool
{
    $valor = strtolower((string) app_env('APP_DEBUG', 'false'));

    return in_array($valor, ['1', 'true', 'on', 'yes'], true);
}

/**
 * Errores de PHP: visibles en pantalla solo con APP_DEBUG=true; en producción se
 * escriben en el log (en Docker: `docker compose logs app`) y no se muestran.
 */
function app_configurar_errores(): void
{
    error_reporting(E_ALL);
    ini_set('display_errors', app_debug() ? '1' : '0');
    ini_set('display_startup_errors', app_debug() ? '1' : '0');
    ini_set('log_errors', '1');
}

app_configurar_errores();
date_default_timezone_set((string) app_env('APP_TIMEZONE', 'America/Bogota'));

// Manejador global de errores: registra en storage/logs/error_log.md y muestra una
// página amigable (controllers/errorController.php). Debe ir DESPUÉS de fijar la zona horaria.
require_once __DIR__ . '/error_handler.php';

/* ==========================================================
   HTTPS y proxy inverso
   ========================================================== */

/**
 * ¿La petición llegó por HTTPS? Detrás de un proxy (Nginx, Traefik, Caddy...) el
 * contenedor recibe HTTP plano; solo si APP_TRUST_PROXY=true se cree en la cabecera
 * X-Forwarded-Proto que pone el proxy.
 */
function app_es_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    if ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443') {
        return true;
    }

    $confiaProxy = in_array(strtolower((string) app_env('APP_TRUST_PROXY', 'false')), ['1', 'true', 'on', 'yes'], true);

    return $confiaProxy
        && strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

/* ==========================================================
   URLs
   ========================================================== */

/**
 * Prefijo de la aplicación en la URL, sin barra final.
 *   ''           -> la app está en la raíz del dominio (Docker, finanzas.nexohuila.com)
 *   '/finanzas'  -> la app cuelga de una subcarpeta (Laragon: http://localhost/finanzas)
 *
 * APP_BASE_PATH=auto (o sin definir) lo detecta solo; para fijarlo a mano
 * (por ejemplo, proxy que quita un prefijo) escribe '/' (raíz) o '/subcarpeta'.
 */
function app_base_path(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $configurado = trim((string) app_env('APP_BASE_PATH', 'auto'));

    if ($configurado !== '' && strtolower($configurado) !== 'auto') {
        return $base = rtrim('/' . trim($configurado, '/'), '/');
    }

    // Detección: SCRIPT_NAME menos la ruta del script dentro del proyecto.
    //   SCRIPT_NAME = /finanzas/controllers/authController.php
    //   dentro del proyecto = controllers/authController.php  ->  base = /finanzas
    $proyecto = rtrim(str_replace('\\', '/', (string) realpath(__DIR__ . '/..')), '/');
    $script   = str_replace('\\', '/', (string) realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $nombre   = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    if ($proyecto === '' || $script === '' || strpos($script, $proyecto . '/') !== 0) {
        return $base = '';
    }

    $relativo = '/' . substr($script, strlen($proyecto) + 1);

    if ($nombre !== '' && substr($nombre, -strlen($relativo)) === $relativo) {
        return $base = rtrim(substr($nombre, 0, -strlen($relativo)), '/');
    }

    return $base = '';
}

/**
 * URL (desde la raíz del sitio) de una ruta del proyecto.
 *   app_url('views/rubros/index.php')            -> /views/rubros/index.php
 *   app_url('views/rubros/index.php', ['x' => 1]) -> /views/rubros/index.php?x=1
 * La ruta puede traer ya su propia query ('views/a.php?id=3').
 */
function app_url(string $ruta = '', array $query = []): string
{
    // Nada de saltos de línea: evita inyección de cabeceras en Location.
    $ruta = str_replace(["\r", "\n"], '', ltrim($ruta, '/'));
    $url  = app_base_path() . '/' . $ruta;

    if ($query) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
    }

    return $url;
}

/** Igual que app_url('') pero siempre termina en '/': sirve de prefijo ($base . 'public/...'). */
function app_base(): string
{
    return app_base_path() . '/';
}

/**
 * URL completa (con dominio) para correos y enlaces que salen de la app.
 * Usa APP_URL (por ejemplo https://finanzas.nexohuila.com); si no existe, la
 * deduce de la petición.
 */
function app_url_absoluta(string $ruta = ''): string
{
    $appUrl = rtrim((string) app_env('APP_URL', ''), '/');

    if ($appUrl !== '') {
        return $appUrl . '/' . str_replace(["\r", "\n"], '', ltrim($ruta, '/'));
    }

    $esquema = app_es_https() ? 'https' : 'http';
    $host    = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');

    return $esquema . '://' . $host . app_url($ruta);
}

/**
 * Redirige a una ruta del proyecto y detiene el script.
 *   redirigir('views/auth/login.php');
 *   redirigir('views/rubros/index.php', ['error' => 'Mensaje']);
 *   redirigir('views/rubros/index.php?success=created');
 */
function redirigir(string $ruta, array $query = [], int $codigo = 302): void
{
    if (!headers_sent()) {
        header('Location: ' . app_url($ruta, $query), true, $codigo);
    }

    exit;
}