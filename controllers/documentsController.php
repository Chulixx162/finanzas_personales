<?php
// controllers/documentsController.php
// Entrega un manual o anexo de public/docs/ solo a usuarios con sesión.
//   ?doc=<clave>&modo=ver        -> lo abre en el navegador (.md con el visor, PDF y SQL)
//   ?doc=<clave>&modo=descargar  -> lo descarga
// Solo se entregan los documentos del catálogo (config/documents.php).

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/documents.php';

requerir_login();

$clave = is_string($_GET['doc'] ?? null) ? $_GET['doc'] : '';
$modo  = ($_GET['modo'] ?? '') === 'descargar' ? 'descargar' : 'ver';
$doc   = $clave !== '' ? buscar_documento($clave) : null;

if ($doc === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Documento no disponible.';
    exit;
}

// Los documentos Markdown se muestran con el visor (views/docs/viewer.php).
if ($doc['extension'] === 'md' && $modo === 'ver') {
    $claveDoc    = $clave;
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
];

// Solo PDF y SQL se pueden mostrar directo en el navegador; lo demás se descarga.
$sePuedeVer  = in_array($doc['extension'], ['pdf', 'sql'], true);
$disposicion = ($modo === 'ver' && $sePuedeVer) ? 'inline' : 'attachment';

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $tipos[$doc['extension']]);
header('Content-Disposition: ' . $disposicion . '; filename="' . $doc['archivo'] . '"');
header('Content-Length: ' . filesize($doc['ruta']));
header('X-Content-Type-Options: nosniff');

readfile($doc['ruta']);
exit;