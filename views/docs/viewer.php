<?php
// views/docs/viewer.php
// Visor de documentos Markdown (.md). Lo carga controllers/documentsController.php
// cuando la persona pulsa "Ver" en un documento .md de public/docs/.
//
// Variables que recibe del controlador:
//   $tituloDoc    título del documento (catálogo de config/documents.php)
//   $contenidoMd  texto Markdown del archivo
//   $claveDoc     clave del documento (para el enlace de descarga)

/** @var string $tituloDoc   */
/** @var string $claveDoc    */
/** @var string $contenidoMd */

// Si alguien abre este archivo directo (sin pasar por el controlador), vuelve al perfil.
if (!isset($tituloDoc, $claveDoc, $contenidoMd)) {
    redirigir('views/users/profile.php');
    exit;
}

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

// Opciones que hacen seguro el JSON dentro de <script> (sin cerrar la etiqueta por accidente).
$jsonSeguro = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $h($tituloDoc) ?> · Finanzas</title>
    <link rel="icon" href="<?= app_url('public/img/icon.webp') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= app_url('public/css/docs_viewer.css') ?>?v=<?= filemtime(__DIR__ . '/../../public/css/docs_viewer.css') ?>">
</head>

<body>

    <header class="doc-bar">
        <a class="doc-back" href="<?= app_url('views/users/profile.php') ?>">&larr; Volver a mi perfil</a>
        <span class="doc-name"><?= $h($tituloDoc) ?></span>
        <a class="doc-download"
            href="documentsController.php?doc=<?= rawurlencode($claveDoc) ?>&amp;modo=descargar">Descargar .md</a>
    </header>

    <main class="doc-page">
        <article class="markdown-body" id="markdownBody" aria-live="polite">
            <p>Cargando documento…</p>
        </article>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
    <script>
        (function() {
            const markdown = <?= json_encode((string) $contenidoMd, $jsonSeguro) ?>;
            const docsUrl = '<?= app_url('public/docs/') ?>';
            const body = document.getElementById('markdownBody');

            try {
                if (!window.marked || !window.DOMPurify) {
                    throw new Error('Librerías no disponibles');
                }

                body.innerHTML = DOMPurify.sanitize(marked.parse(markdown));

                // Las imágenes del .md son relativas a public/docs/ (ej. img/user_manual/image1.png)
                body.querySelectorAll('img').forEach(function(img) {
                    const src = img.getAttribute('src') || '';
                    if (src !== '' && !/^(?:[a-z]+:|\/|#)/i.test(src)) {
                        img.setAttribute('src', docsUrl + src);
                    }
                    img.setAttribute('loading', 'lazy');
                });

                // Las tablas anchas se desplazan sin romper la página
                body.querySelectorAll('table').forEach(function(table) {
                    const wrap = document.createElement('div');
                    wrap.className = 'table-scroll';
                    table.parentNode.insertBefore(wrap, table);
                    wrap.appendChild(table);
                });
            } catch (e) {
                // Sin internet (las librerías vienen de un CDN) se muestra el texto tal cual.
                const pre = document.createElement('pre');
                pre.className = 'raw-markdown';
                pre.textContent = markdown;
                body.replaceChildren(pre);
            }
        })();
    </script>

</body>

</html>