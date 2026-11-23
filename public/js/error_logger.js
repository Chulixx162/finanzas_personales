// public/js/error_logger.js
// Captura los errores de JavaScript del navegador y los envía a
// controllers/clientErrorController.php, que los anota en storage/logs/error_log.md.
// Además le muestra a la persona un aviso amable con la referencia ERR-...
//
// Se carga con una sola etiqueta (la URL del controlador se deduce sola de la ruta del script,
// así funciona igual en Laragon, Docker o detrás de un proxy):
//   <script src="<?= $base ?>public/js/error_logger.js"></script>
//
// Captura: errores de script, promesas rechazadas sin catch, recursos que no cargan
// (script, css, img) y respuestas 5xx de fetch(). No usa librerías.

(function () {
    'use strict';

    if (window.__errorLoggerInstalado) {
        return;
    }
    window.__errorLoggerInstalado = true;

    var MAX_POR_PAGINA = 5;          // evita inundar el log si algo falla en bucle
    var enviados = 0;
    var vistos = {};
    var script = document.currentScript;
    var endpoint = script && script.src
        ? new URL('../../controllers/clientErrorController.php', script.src).href
        : null;

    function cortar(texto, max) {
        texto = String(texto == null ? '' : texto);
        return texto.length > max ? texto.slice(0, max) : texto;
    }

    // ---------- Aviso HTML para la persona ----------
    function mostrarAviso(referencia) {
        var previo = document.getElementById('error-logger-aviso');
        if (previo) {
            previo.remove();
        }

        var aviso = document.createElement('div');
        aviso.id = 'error-logger-aviso';
        aviso.setAttribute('role', 'alert');
        aviso.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:99999;max-width:360px;'
            + 'padding:14px 16px;border-radius:12px;background:#fff;color:#1e1b4b;'
            + 'border:1px solid #e5e7eb;border-left:4px solid #dc2626;'
            + 'box-shadow:0 12px 32px rgba(30,27,75,.18);font:14px/1.5 system-ui,sans-serif';

        var titulo = document.createElement('strong');
        titulo.textContent = 'Algo no salió como esperábamos';

        var texto = document.createElement('p');
        texto.style.margin = '6px 0 0';
        texto.textContent = 'Ya quedó registrado. Si el problema continúa, comparte esta referencia: ';

        var ref = document.createElement('strong');
        ref.textContent = referencia;
        ref.style.userSelect = 'all';
        texto.appendChild(ref);

        var cerrar = document.createElement('button');
        cerrar.type = 'button';
        cerrar.textContent = 'Cerrar';
        cerrar.style.cssText = 'margin-top:10px;padding:6px 12px;border:0;border-radius:8px;'
            + 'background:#4f46e5;color:#fff;cursor:pointer;font:inherit';
        cerrar.addEventListener('click', function () { aviso.remove(); });

        aviso.appendChild(titulo);
        aviso.appendChild(texto);
        aviso.appendChild(cerrar);
        document.body.appendChild(aviso);

        setTimeout(function () { if (aviso.parentNode) { aviso.remove(); } }, 15000);
    }

    // ---------- Envío al servidor ----------
    function reportar(error) {
        if (!endpoint || enviados >= MAX_POR_PAGINA) {
            return;
        }

        var huella = [error.tipo, error.mensaje, error.archivo, error.linea].join('|');
        if (vistos[huella]) {
            return;
        }
        vistos[huella] = true;
        enviados++;

        error.pagina = window.location.pathname; // sin query string: puede llevar tokens

        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(error),
            credentials: 'same-origin',
            keepalive: true
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (datos) {
                if (datos && datos.ok && datos.referencia) {
                    if (document.body) {
                        mostrarAviso(datos.referencia);
                    }
                }
            })
            .catch(function () { /* registrar nunca debe romper la página */ });
    }

    // ---------- Errores de script ----------
    window.addEventListener('error', function (e) {
        // Recursos que no cargan (script, css, img): el evento llega por captura, sin e.message.
        if (e.target && e.target !== window && (e.target.src || e.target.href)) {
            reportar({
                tipo: 'Recurso no cargó (' + e.target.tagName.toLowerCase() + ')',
                mensaje: 'No se pudo cargar el recurso',
                archivo: cortar(e.target.src || e.target.href, 300),
                linea: 0,
                columna: 0,
                traza: ''
            });
            return;
        }

        reportar({
            tipo: (e.error && e.error.name) || 'Error',
            mensaje: cortar(e.message || (e.error && e.error.message), 600),
            archivo: cortar(e.filename, 300),
            linea: e.lineno || 0,
            columna: e.colno || 0,
            traza: cortar(e.error && e.error.stack, 3000)
        });
    }, true);

    // ---------- Promesas rechazadas sin catch ----------
    window.addEventListener('unhandledrejection', function (e) {
        var motivo = e.reason;
        var esError = motivo instanceof Error;

        reportar({
            tipo: esError ? motivo.name : 'PromesaRechazada',
            mensaje: cortar(esError ? motivo.message : motivo, 600),
            archivo: '',
            linea: 0,
            columna: 0,
            traza: cortar(esError ? motivo.stack : '', 3000)
        });
    });

    // ---------- fetch() que responde 5xx ----------
    if (typeof window.fetch === 'function') {
        var fetchOriginal = window.fetch;

        window.fetch = function (entrada, opciones) {
            var url = typeof entrada === 'string' ? entrada : (entrada && entrada.url) || '';

            return fetchOriginal.apply(this, arguments).then(function (respuesta) {
                // No se vigila al propio registrador (evita bucles).
                if (respuesta.status >= 500 && url.indexOf('clientErrorController') === -1) {
                    reportar({
                        tipo: 'HTTP ' + respuesta.status,
                        mensaje: 'El servidor respondió ' + respuesta.status + ' a una petición fetch',
                        archivo: cortar(new URL(url, window.location.href).pathname, 300),
                        linea: 0,
                        columna: 0,
                        traza: ''
                    });
                }
                return respuesta;
            });
        };
    }
})();