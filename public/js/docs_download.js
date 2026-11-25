// public/js/docs_download.js
// Perfil -> Manuales y anexos: antes de descargar un documento .md se pregunta
// en qué formato lo quiere la persona (Word, PDF o Markdown).
// Usa la misma alerta (SweetAlert2) que el resto del sistema.
(function () {
    'use strict';

    const dialogo = document.getElementById('dlDialog');
    const nombreDoc = document.getElementById('dlDoc');
    const opciones = dialogo ? dialogo.querySelectorAll('.dl-option') : [];

    // Formatos que se ofrecen (el orden es el que ve la persona).
    const formatos = [
        { clave: 'word', nombre: 'Word', ext: '.docx', ayuda: 'Para editarlo en Word.', nuevaPestana: true },
        { clave: 'pdf', nombre: 'PDF', ext: '.pdf', ayuda: 'Para leerlo, compartirlo o imprimirlo.', nuevaPestana: true },
        { clave: 'md', nombre: 'Markdown', ext: '.md', ayuda: 'El archivo original, en texto.', nuevaPestana: false }
    ];

    // Contenido de la alerta: nombre del documento, pregunta y las 3 opciones.
    function crearContenido(boton) {
        const caja = document.createElement('div');

        const doc = document.createElement('p');
        doc.className = 'al-doc';
        doc.textContent = boton.dataset.titulo || '';
        caja.appendChild(doc);

        const pregunta = document.createElement('p');
        pregunta.className = 'al-ask';
        pregunta.textContent = '¿En qué formato lo quieres?';
        caja.appendChild(pregunta);

        const lista = document.createElement('div');
        lista.className = 'al-options';

        formatos.forEach(function (f) {
            const enlace = document.createElement('a');
            enlace.className = 'al-option';
            enlace.href = boton.dataset[f.clave] || '#';
            if (f.nuevaPestana) {
                enlace.target = '_blank';
                enlace.rel = 'noopener';
            }

            const nombre = document.createElement('span');
            nombre.className = 'al-option-name';
            nombre.textContent = f.nombre + ' ';
            const ext = document.createElement('small');
            ext.textContent = f.ext;
            nombre.appendChild(ext);

            const ayuda = document.createElement('span');
            ayuda.className = 'al-option-hint';
            ayuda.textContent = f.ayuda;

            enlace.appendChild(nombre);
            enlace.appendChild(ayuda);
            lista.appendChild(enlace);
        });

        // Elegir un formato cierra la alerta (el enlace sigue su camino).
        lista.addEventListener('click', function (e) {
            if (e.target.closest('.al-option')) {
                Swal.close();
            }
        });

        caja.appendChild(lista);
        return caja;
    }

    document.addEventListener('click', function (e) {
        const boton = e.target.closest('[data-descarga]');
        if (!boton) {
            return;
        }

        // Alerta con el mismo estilo que el resto del sistema (alerts.js / alerts.css).
        if (typeof Swal !== 'undefined' && typeof opcionesAlerta === 'function') {
            Swal.fire(Object.assign(opcionesAlerta(false), {
                title: 'Descargar documento',
                icon: 'question',
                html: crearContenido(boton),
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Cancelar'
            }));
            return;
        }

        // Respaldo sin SweetAlert (sin internet): el <dialog> nativo.
        if (!dialogo || typeof dialogo.showModal !== 'function') {
            window.location.href = boton.dataset.md;
            return;
        }

        nombreDoc.textContent = boton.dataset.titulo || '';
        opciones.forEach(function (opcion) {
            opcion.href = boton.dataset[opcion.dataset.formato] || '#';
        });

        dialogo.showModal();
    });

    // Elegir un formato cierra el aviso (el enlace sigue su camino); tocar fuera también lo cierra.
    if (dialogo) {
        dialogo.addEventListener('click', function (e) {
            if (e.target === dialogo || e.target.closest('.dl-option')) {
                dialogo.close();
            }
        });
    }
})();