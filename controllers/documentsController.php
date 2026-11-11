<?php
// controllers/documentsController.php
// Entrega un manual o anexo de public/docs/ solo a usuarios con sesión.
//   ?doc=<clave>&modo=ver        -> lo abre en el navegador (PDF y SQL)
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

$tipos = [
    'pdf'  => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];

// Solo PDF y SQL se pueden mostrar en el navegador; lo demás se descarga.
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