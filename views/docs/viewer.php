<?php
// views/docs/viewer.php
// Visor de documentos Markdown (.md). Lo carga controllers/documentsController.php
// cuando la persona pulsa "Ver" en un documento .md de public/docs/.
//
// Variables que recibe del controlador:
//   $tituloDoc    título del documento (catálogo de config/documents.php)
//   $contenidoMd  texto Markdown del archivo
//   $claveDoc     clave del documento (para el enlace de descarga)
//   $descargaAuto 'word' o 'pdf' si hay que descargar el documento al abrir ('' si no)

/** @var string $tituloDoc   */
/** @var string $claveDoc    */
/** @var string $contenidoMd */
/** @var string $descargaAuto */

$descargaAuto = in_array($descargaAuto ?? '', ['word', 'pdf'], true) ? $descargaAuto : '';

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
        <div class="doc-actions" role="group" aria-label="Descargar este documento">
            <button type="button" class="doc-btn" data-export="word">Descargar Word</button>
            <button type="button" class="doc-btn" data-export="pdf">Descargar PDF</button>
            <a class="doc-download"
                href="documentsController.php?doc=<?= rawurlencode($claveDoc) ?>&amp;modo=descargar">.md</a>
        </div>
    </header>

    <p class="doc-status" id="docStatus" role="status" aria-live="polite" hidden></p>

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
            const tituloDoc = <?= json_encode((string) $tituloDoc, $jsonSeguro) ?>;
            const descargaAuto = <?= json_encode($descargaAuto) ?>;
            const body = document.getElementById('markdownBody');
            const estado = document.getElementById('docStatus');
            const botones = document.querySelectorAll('[data-export]');

            const LIBRERIAS = {
                pdf: 'https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js',
                word: 'https://cdn.jsdelivr.net/npm/html-docx-js@0.3.1/dist/html-docx.js'
            };

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

                body.dataset.rendered = '1';
            } catch (e) {
                // Sin internet (las librerías vienen de un CDN) se muestra el texto tal cual.
                const pre = document.createElement('pre');
                pre.className = 'raw-markdown';
                pre.textContent = markdown;
                body.replaceChildren(pre);
            }

            /* ---------- Descarga en Word y PDF ---------- */

            function avisar(texto, esError) {
                estado.textContent = texto;
                estado.hidden = texto === '';
                estado.classList.toggle('is-error', !!esError);
            }

            function nombreArchivo(extension) {
                const base = tituloDoc
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                return (base || 'documento') + '.' + extension;
            }

            function cargarLibreria(tipo, comprobar) {
                if (comprobar()) {
                    return Promise.resolve();
                }
                return new Promise(function(resolver, rechazar) {
                    const script = document.createElement('script');
                    script.src = LIBRERIAS[tipo];
                    script.onload = function() { comprobar() ? resolver() : rechazar(new Error('Librería vacía')); };
                    script.onerror = function() { rechazar(new Error('No se pudo cargar la librería')); };
                    document.head.appendChild(script);
                });
            }

            // Descarga un Blob como archivo.
            function guardarBlob(blob, nombre) {
                const url = URL.createObjectURL(blob);
                const enlace = document.createElement('a');
                enlace.href = url;
                enlace.download = nombre;
                document.body.appendChild(enlace);
                enlace.click();
                enlace.remove();
                setTimeout(function() { URL.revokeObjectURL(url); }, 4000);
            }

            // Espera a que carguen todas las imágenes (son lazy: las de abajo aún no se pidieron).
            function esperarImagenes() {
                return Promise.all(Array.from(body.querySelectorAll('img')).map(function(img) {
                    img.loading = 'eager';
                    if (img.complete) {
                        return null;
                    }
                    return new Promise(function(listo) { img.onload = img.onerror = listo; });
                }));
            }

            // Word no entiende rutas relativas: cada imagen se incrusta como data URI.
            async function incrustarImagenes(contenedor) {
                const imagenes = Array.from(contenedor.querySelectorAll('img'));

                await Promise.all(imagenes.map(async function(img) {
                    try {
                        const respuesta = await fetch(img.src, { credentials: 'same-origin' });
                        if (!respuesta.ok) { throw new Error('HTTP ' + respuesta.status); }
                        const blob = await respuesta.blob();
                        const datos = await new Promise(function(resolver, rechazar) {
                            const lector = new FileReader();
                            lector.onload = function() { resolver(lector.result); };
                            lector.onerror = rechazar;
                            lector.readAsDataURL(blob);
                        });
                        const medida = await new Promise(function(resolver) {
                            const prueba = new Image();
                            prueba.onload = function() { resolver(prueba); };
                            prueba.onerror = function() { resolver(null); };
                            prueba.src = datos;
                        });

                        img.removeAttribute('loading');
                        img.removeAttribute('style');
                        img.src = datos;
                        if (medida && medida.naturalWidth) {
                            // Máximo ~16 cm de ancho (600 px) para que quepa en la hoja.
                            const ancho = Math.min(medida.naturalWidth, 600);
                            img.setAttribute('width', ancho);
                            img.setAttribute('height', Math.round(ancho * medida.naturalHeight / medida.naturalWidth));
                        }
                    } catch (e) {
                        img.replaceWith(document.createTextNode('[Imagen no disponible: ' + (img.getAttribute('alt') || '') + ']'));
                    }
                }));
            }

            const ESTILOS_WORD = 'body{font-family:Calibri,Arial,sans-serif;font-size:11pt;color:#1F1B3A;line-height:1.4}'
                + 'h1{font-size:22pt;color:#1E1B4B;border-bottom:2px solid #6D28D9}h2{font-size:16pt;color:#1E1B4B}'
                + 'h3{font-size:13pt;color:#1E1B4B}h4{font-size:11pt;color:#1E1B4B}'
                + 'table{border-collapse:collapse;width:100%}th,td{border:1px solid #999999;padding:4px 6px;vertical-align:top}'
                + 'th{background:#EDE9FE}'
                + 'pre{background:#F3F0FF;border:1px solid #D8D0F5;padding:8px;font-family:Consolas,monospace;font-size:9pt;white-space:pre-wrap}'
                + 'code{font-family:Consolas,monospace;background:#F3F0FF}'
                + 'blockquote{border-left:4px solid #6D28D9;padding-left:10px;color:#5B5580}'
                + 'a{color:#6D28D9}';

            async function exportarWord() {
                await cargarLibreria('word', function() { return !!window.htmlDocx; });

                const copia = body.cloneNode(true);
                copia.removeAttribute('id');
                await incrustarImagenes(copia);

                copia.querySelectorAll('table').forEach(function(tabla) {
                    tabla.setAttribute('border', '1');
                    tabla.setAttribute('cellpadding', '4');
                    tabla.setAttribute('cellspacing', '0');
                    tabla.setAttribute('width', '100%');
                });

                const html = '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>'
                    + tituloDoc.replace(/</g, '&lt;') + '</title><style>' + ESTILOS_WORD + '</style></head><body>'
                    + copia.innerHTML + '</body></html>';

                const blob = window.htmlDocx.asBlob(html, {
                    orientation: 'portrait',
                    margins: { top: 1134, right: 1134, bottom: 1134, left: 1134 } // 2 cm
                });
                guardarBlob(blob, nombreArchivo('docx'));
            }

            async function exportarPdf() {
                await cargarLibreria('pdf', function() { return typeof window.html2pdf === 'function'; });
                await esperarImagenes();

                // Mientras se genera, el documento se muestra sin marco ni sombra.
                body.classList.add('pdf-export');
                try {
                    await window.html2pdf().set({
                        margin: [12, 12, 14, 12],
                        filename: nombreArchivo('pdf'),
                        image: { type: 'jpeg', quality: 0.95 },
                        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                        pagebreak: { mode: ['css', 'legacy'], avoid: ['img', 'tr', 'pre', 'blockquote', 'h2', 'h3'] }
                    }).from(body).save();
                } finally {
                    body.classList.remove('pdf-export');
                }
            }

            let ocupado = false;

            async function exportar(tipo) {
                if (ocupado) {
                    return;
                }
                if (body.dataset.rendered !== '1') {
                    avisar('No se pudo preparar el documento: revisa tu conexión a internet y recarga la página.', true);
                    return;
                }

                ocupado = true;
                botones.forEach(function(b) { b.disabled = true; });
                avisar('Preparando tu ' + (tipo === 'word' ? 'Word' : 'PDF') + '… puede tardar unos segundos.', false);

                try {
                    if (tipo === 'word') {
                        await exportarWord();
                    } else {
                        await exportarPdf();
                    }
                    avisar('Listo: tu descarga de ' + (tipo === 'word' ? 'Word' : 'PDF') + ' comenzó.', false);
                } catch (e) {
                    console.error(e);
                    avisar('No se pudo generar el ' + (tipo === 'word' ? 'Word' : 'PDF')
                        + '. Revisa tu conexión a internet e inténtalo de nuevo.', true);
                } finally {
                    ocupado = false;
                    botones.forEach(function(b) { b.disabled = false; });
                }
            }

            botones.forEach(function(boton) {
                boton.addEventListener('click', function() { exportar(boton.dataset.export); });
            });

            // Llegó desde el perfil con "Word" o "PDF": la descarga empieza sola.
            if (descargaAuto) {
                exportar(descargaAuto);
            }
        })();
    </script>

</body>

</html>