<?php
// controllers/clientErrorController.php
// Recibe los errores que detecta el NAVEGADOR (public/js/error_logger.js), los anota en
// storage/logs/error_log.md y devuelve la referencia ERR-... en JSON.
//
// Solo acepta POST con JSON, del mismo sitio y de tamaño acotado.

require_once __DIR__ . '/../config/app.php';

const CLIENTE_MAX_BYTES = 8192; // un error legítimo pesa mucho menos

function responder_json(int $codigo, array $datos): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

/** El origen de la petición debe ser este mismo sitio (evita que otras webs llenen el log). */
function origen_permitido(): bool
{
    $origen = (string) ($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '');

    if ($origen === '') {
        return false;
    }

    $hostOrigen = (string) parse_url($origen, PHP_URL_HOST);
    $hostSitio  = (string) parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);

    return $hostOrigen !== '' && strcasecmp($hostOrigen, $hostSitio) === 0;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder_json(405, ['ok' => false, 'error' => 'metodo_no_permitido']);
}

if (!origen_permitido()) {
    responder_json(403, ['ok' => false, 'error' => 'origen_no_permitido']);
}

$cuerpo = file_get_contents('php://input', false, null, 0, CLIENTE_MAX_BYTES + 1);

if ($cuerpo === false || $cuerpo === '' || strlen($cuerpo) > CLIENTE_MAX_BYTES) {
    responder_json(400, ['ok' => false, 'error' => 'cuerpo_invalido']);
}

$datos = json_decode($cuerpo, true);

if (!is_array($datos)) {
    responder_json(400, ['ok' => false, 'error' => 'json_invalido']);
}

$texto = static fn($clave, int $max): string => mb_substr(trim((string) ($datos[$clave] ?? '')), 0, $max);

// Solo la ruta de la página: la query string puede llevar tokens (recuperar contraseña).
$pagina = (string) parse_url($texto('pagina', 500), PHP_URL_PATH);

$referencia = ManejadorErrores::registrarCliente(
    $texto('tipo', 80) !== '' ? $texto('tipo', 80) : 'Error',
    $texto('mensaje', 600),
    $texto('archivo', 300),
    max(0, (int) ($datos['linea'] ?? 0)),
    max(0, (int) ($datos['columna'] ?? 0)),
    $texto('traza', 3000),
    $pagina
);

responder_json(200, ['ok' => true, 'referencia' => $referencia]);