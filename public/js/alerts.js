// public/js/alerts.js
// Alertas del sistema con SweetAlert2. El diseño está en public/css/alerts.css.
// Necesita cargar antes: SweetAlert2 (CDN) y alerts.css.

// Opciones comunes: sin los botones azules por defecto y con las clases .al-*
function opcionesAlerta(peligro) {
    return {
        buttonsStyling: false,
        reverseButtons: true,
        focusCancel: peligro,
        backdrop: 'rgba(30, 27, 75, 0.55)',
        customClass: {
            popup: 'al-popup',
            icon: 'al-icon',
            title: 'al-title',
            htmlContainer: 'al-text',
            actions: 'al-actions',
            confirmButton: 'al-btn ' + (peligro ? 'al-btn-danger' : 'al-btn-primary'),
            cancelButton: 'al-btn al-btn-ghost'
        }
    };
}

// Ventana de confirmación (eliminar, guardar cambios...).
// Devuelve true si la persona confirma.
// Los parámetros de color y fondo se conservan para no romper llamadas
// anteriores, pero ya no se usan: los colores salen de alerts.css.
async function alerta(
    title,
    text,
    icon,
    confirmButtonText,
    cancelButtonText,
    confirmButtonColor,
    cancelButtonColor,
    background,
    color,
    deleteUrl
) {
    // Si SweetAlert no cargó (sin internet), se usa la ventana del navegador.
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no se cargó. Revisa la conexión a internet.');
        const confirmado = window.confirm(title + '\n\n' + text);
        if (confirmado && deleteUrl) {
            window.location.href = deleteUrl;
        }
        return confirmado;
    }

    // Las acciones que advierten (eliminar) llevan el botón rojo.
    const peligro = icon === 'warning' || icon === 'error';

    const result = await Swal.fire(Object.assign(opcionesAlerta(peligro), {
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText
    }));

    if (result.isConfirmed && deleteUrl) {
        window.location.href = deleteUrl;
    }
    return result.isConfirmed;
}

// Aviso (creada, duplicada, campo vacío, no se puede eliminar...).
// tipo: 'ok' | 'success' (verde) · 'error' | 'warning' (ámbar) · 'info' (morado)
function mostrarAviso(tipo, texto) {
    // Si SweetAlert no cargó (sin internet), al menos se muestra algo.
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no se cargó. Revisa la conexión a internet.');
        window.alert(texto);
        return Promise.resolve();
    }

    const exito = tipo === 'ok' || tipo === 'success';
    const informativo = tipo === 'info';

    return Swal.fire(Object.assign(opcionesAlerta(false), {
        title: exito ? '¡Listo!' : (informativo ? 'Información' : 'No se pudo completar'),
        text: texto,
        icon: exito ? 'success' : (informativo ? 'info' : 'warning'),
        confirmButtonText: 'Entendido'
    }));
}