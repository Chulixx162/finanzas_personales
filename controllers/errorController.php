<?php
// controllers/errorController.php
// Controlador de errores. Tiene dos usos:
//
//   1. Lo llama config/error_handler.php cuando algo falla:
//        mostrar_error(500, 'ERR-20261121-A1B2C3', $detalleSoloEnDebug);
//
//   2. Se puede abrir directo para páginas de error "de cortesía"
//      (no escribe nada en el log):
//        controllers/errorController.php?code=404
//        controllers/errorController.php?code=403
//
// Si la petición viene de JavaScript (fetch / AJAX) responde JSON en vez de HTML,
// para no romper pantallas como el perfil o el control de sesión.

require_once __DIR__ . '/../config/app.php';

/** Códigos HTTP que sabemos mostrar: código => [título, mensaje para la persona]. */
const ERRORES_HTTP = [
    400 => ['Solicitud no válida', 'No pudimos entender lo que pediste. Revisa el enlace e inténtalo de nuevo.'],
    403 => ['Acceso denegado', 'No tienes permiso para ver esta página.'],
    404 => ['Página no encontrada', 'La página que buscas no existe o fue movida.'],
    405 => ['Acción no permitida', 'Esta acción no está permitida desde aquí.'],
    500 => ['Algo salió mal', 'Tuvimos un problema de nuestro lado. Ya quedó registrado y lo vamos a revisar. Tus datos están a salvo.'],
    503 => ['Servicio no disponible', 'Estamos haciendo ajustes. Inténtalo de nuevo en unos minutos.'],
];

/**
 * ¿Quien pide espera JSON? Sí si es AJAX, si el controlador ya había fijado
 * Content-Type JSON, o si el navegador pide JSON y no HTML.
 */
function peticion_espera_json(): bool
{
    if (strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') {
        return true;
    }

    foreach (headers_list() as $cabecera) {
        if (stripos($cabecera, 'content-type: application/json') === 0) {
            return true;
        }
    }

    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));

    return strpos($accept, 'application/json') !== false && strpos($accept, 'text/html') === false;
}

/**
 * Descarta lo que se hubiera escrito a medias y responde con la vista amigable
 * (o JSON). $detalle solo se muestra con APP_DEBUG=true.
 */
function mostrar_error(int $codigo = 500, string $referencia = '', ?string $detalle = null): void
{
    if (!isset(ERRORES_HTTP[$codigo])) {
        $codigo = 500;
    }

    [$titulo, $mensaje] = ERRORES_HTTP[$codigo];

    // Hay que decidir antes de limpiar: headers_list() todavía muestra lo que fijó el controlador.
    $esJson = peticion_espera_json();

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (!headers_sent()) {
        http_response_code($codigo);
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
    }

    // Detalles técnicos: nunca fuera de modo depuración.
    $detalle = app_debug() ? $detalle : null;

    if ($esJson) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode([
            'ok'         => false,
            'error'      => 'error_interno',
            'mensaje'    => $mensaje,
            'referencia' => $referencia !== '' ? $referencia : null,
        ], JSON_UNESCAPED_UNICODE);

        return;
    }

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    $base     = app_base();
    $logueado = session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id']);
    $destino  = $base . ($logueado ? 'views/users/dashboard.php' : 'public/index.php');
    $h        = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

    require __DIR__ . '/../views/errors/error.php';
}

/** Uso directo del controlador: errorController.php?code=404 */
function mostrar_error_desde_peticion(): void
{
    $codigo = filter_input(INPUT_GET, 'code', FILTER_VALIDATE_INT);

    if (!$codigo || !isset(ERRORES_HTTP[$codigo])) {
        $codigo = 404;
    }

    mostrar_error($codigo);
}

// Solo se ejecuta si el navegador abrió ESTE archivo; al incluirlo desde el manejador no hace nada.
if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === realpath(__FILE__)) {
    mostrar_error_desde_peticion();
    exit;
}