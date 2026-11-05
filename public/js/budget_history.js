// public/js/budget_history.js
// Historial de presupuesto: aviso del servidor, formato de miles y confirmación al guardar.
// Usa alerts.js (navbar.php ya lo carga junto con SweetAlert2).

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('budgetForm');
    const input = document.getElementById('presupuesto');
    const avisoData = document.getElementById('avisoData');

    function avisar(tipo, texto) {
        if (typeof mostrarAviso === 'function') {
            return mostrarAviso(tipo, texto);
        }
        window.alert(texto);
    }

    // Aviso que envía el servidor (?aviso=...)
    if (avisoData) {
        avisar(avisoData.dataset.tipo, avisoData.dataset.texto);

        // Quitar ?aviso= de la URL para que no se repita al recargar.
        const url = new URL(window.location.href);
        url.searchParams.delete('aviso');
        url.searchParams.delete('min');
        window.history.replaceState({}, '', url);
    }

    // Alerta emergente cuando se supera el 100 % del presupuesto estimado.
    // Se muestra una sola vez por sesión del navegador para cada situación,
    // y no si ya hay otro aviso del servidor en pantalla.
    const excesoData = document.getElementById('excesoData');

    if (excesoData && !avisoData) {
        const clave = 'exceso_presupuesto:' + excesoData.dataset.texto;
        let yaMostrado = false;

        try {
            yaMostrado = sessionStorage.getItem(clave) === '1';
        } catch (e) { /* sin sessionStorage: se muestra siempre */ }

        if (!yaMostrado) {
            if (typeof Swal !== 'undefined' && typeof opcionesAlerta === 'function') {
                Swal.fire(Object.assign(opcionesAlerta(true), {
                    title: '¡Superaste el 100 % de tu presupuesto!',
                    text: excesoData.dataset.texto,
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                }));
            } else {
                avisar('error', excesoData.dataset.texto);
            }

            try {
                sessionStorage.setItem(clave, '1');
            } catch (e) { /* se ignora */ }
        }
    }

    if (!form || !input) {
        return;
    }

    const soloDigitos = (texto) => texto.replace(/\D/g, '');
    const formatear = (digitos) =>
        digitos === '' ? '' : Number(digitos).toLocaleString('es-CO');

    // Muestra el valor con puntos de miles mientras se escribe.
    input.addEventListener('input', () => {
        input.value = formatear(soloDigitos(input.value));
    });

    form.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const digitos = soloDigitos(input.value);

        if (digitos === '') {
            avisar('error', 'Escribe el nuevo valor del presupuesto.');
            input.focus();
            return;
        }

        if (digitos.length > 10) {
            avisar('error', 'El presupuesto supera el máximo permitido ($9.999.999.999).');
            input.focus();
            return;
        }

        if (digitos === form.dataset.actual) {
            avisar('info', 'El valor es igual a tu presupuesto actual.');
            return;
        }

        const nuevo = '$' + formatear(digitos);
        const confirmado = typeof alerta === 'function'
            ? await alerta(
                '¿Cambiar tu presupuesto?',
                'Tu presupuesto mensual pasará a ' + nuevo + ' y el cambio quedará en tu historial.',
                'question',
                'Sí, guardar',
                'Cancelar'
            )
            : window.confirm('¿Cambiar el presupuesto a ' + nuevo + '?');

        if (confirmado) {
            form.submit();
        }
    });
});