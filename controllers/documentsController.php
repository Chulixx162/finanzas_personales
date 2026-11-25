<?php
// controllers/documentsController.php
// Entrega un manual o anexo de public/docs/ solo a usuarios con sesión.
//   ?doc=<clave>&modo=ver        -> lo abre en el navegador (.md con el visor, PDF y SQL)
//   ?doc=<clave>&modo=descargar  -> lo descarga
//   ?doc=<clave>&modo=word|pdf   -> (solo .md) abre el visor y descarga el documento en Word o PDF
// Solo se entregan los documentos del catálogo (config/documents.php).
// Los videos (.mp4) se abren en el reproductor del navegador y admiten adelantar.

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/documents.php';

requerir_login();

$clave = is_string($_GET['doc'] ?? null) ? $_GET['doc'] : '';
$modoPedido = is_string($_GET['modo'] ?? null) ? $_GET['modo'] : '';
$modo  = in_array($modoPedido, ['descargar', 'word', 'pdf'], true) ? $modoPedido : 'ver';
$doc   = $clave !== '' ? buscar_documento($clave) : null;

if ($doc === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Documento no disponible.';
    exit;
}

// Word y PDF solo existen para los documentos Markdown; en cualquier otro tipo
// se entrega el archivo original.
$exportable = $doc['extension'] === 'md';
if (!$exportable && in_array($modo, ['word', 'pdf'], true)) {
    $modo = 'descargar';
}

// Los documentos Markdown se muestran con el visor (views/docs/viewer.php).
// Con modo=word o modo=pdf el visor, además, descarga el documento en ese formato.
if ($exportable && $modo !== 'descargar') {
    $claveDoc      = $clave;
    $descargaAuto  = in_array($modo, ['word', 'pdf'], true) ? $modo : '';
    $tituloDoc   = titulo_documento($clave) ?: $doc['archivo'];
    $contenidoMd = (string) file_get_contents($doc['ruta']);

    header('Content-Type: text/html; charset=utf-8');
    require __DIR__ . '/../views/docs/viewer.php';
    exit;
}

$tipos = [
    'md'   => 'text/markdown; charset=utf-8',
    'pdf'  => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'sql'  => 'text/plain; charset=utf-8',
    'jpeg' => 'image/jpeg',
    'mp4'  => 'video/mp4',
];

// PDF, SQL y video se pueden mostrar directo en el navegador; lo demás se descarga.
$sePuedeVer  = in_array($doc['extension'], ['pdf', 'sql', 'mp4'], true);
$disposicion = ($modo === 'ver' && $sePuedeVer) ? 'inline' : 'attachment';

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $tipos[$doc['extension']]);
header('Content-Disposition: ' . $disposicion . '; filename="' . $doc['archivo'] . '"');
header('X-Content-Type-Options: nosniff');

$total  = (int) filesize($doc['ruta']);
$inicio = 0;
$fin    = $total - 1;

// Video: se responde por rangos (206) para que el navegador pueda adelantar y retroceder.
if ($doc['extension'] === 'mp4') {
    header('Accept-Ranges: bytes');

    if (
        $total > 0 &&
        isset($_SERVER['HTTP_RANGE']) &&
        preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER['HTTP_RANGE'], $m) &&
        ($m[1] !== '' || $m[2] !== '')
    ) {
        if ($m[1] === '') {                       // bytes=-500: los últimos 500
            $inicio = max(0, $total - (int) $m[2]);
        } else {
            $inicio = (int) $m[1];
            if ($m[2] !== '') {
                $fin = min((int) $m[2], $total - 1);
            }
        }

        if ($inicio > $fin || $inicio >= $total) {
            http_response_code(416);
            header('Content-Range: bytes */' . $total);
            exit;
        }

        http_response_code(206);
        header('Content-Range: bytes ' . $inicio . '-' . $fin . '/' . $total);
    }

    // Liberar la sesión: un video largo no debe bloquear las demás páginas del usuario.
    session_write_close();
    set_time_limit(0);
}

header('Content-Length: ' . ($fin - $inicio + 1));

$archivo   = fopen($doc['ruta'], 'rb');
$pendiente = $fin - $inicio + 1;

if ($archivo !== false) {
    fseek($archivo, $inicio);

    while ($pendiente > 0 && !feof($archivo) && !connection_aborted()) {
        $trozo = fread($archivo, min(8192 * 16, $pendiente));
        if ($trozo === false) {
            break;
        }
        echo $trozo;
        $pendiente -= strlen($trozo);
        flush();
    }

    fclose($archivo);
}
exit;