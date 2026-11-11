// public/js/category_report.js
// Reporte por categoría: muestra las fechas solo en el periodo personalizado
// y valida el rango antes de enviar el formulario.

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reportForm');

    if (!form) {
        return;
    }

    const periodo = document.getElementById('periodo');
    const cajaFechas = document.getElementById('customDates');
    const desde = document.getElementById('desde');
    const hasta = document.getElementById('hasta');
    const mensaje = document.getElementById('periodError');

    function esPersonalizado() {
        return periodo.value === 'personalizado';
    }

    // Muestra u oculta las fechas. Si no son necesarias se deshabilitan
    // para que no viajen en la URL (el servidor calcula el periodo fijo).
    function actualizarFechas() {
        const activo = esPersonalizado();

        cajaFechas.hidden = !activo;
        desde.disabled = !activo;
        hasta.disabled = !activo;
        desde.required = activo;
        hasta.required = activo;

        if (!activo) {
            mensaje.textContent = '';
        }
    }

    // Devuelve el texto del error del rango, o '' si todo está bien.
    function validarRango() {
        if (desde.value === '' || hasta.value === '') {
            return 'Elige la fecha inicial y la fecha final del periodo.';
        }

        if (desde.value > hasta.value) {
            return 'La fecha inicial no puede ser posterior a la fecha final.';
        }

        return '';
    }

    periodo.addEventListener('change', () => {
        actualizarFechas();

        // Los periodos fijos se aplican al instante.
        if (!esPersonalizado()) {
            form.submit();
        }
    });

    form.addEventListener('submit', (evento) => {
        if (!esPersonalizado()) {
            return;
        }

        const error = validarRango();
        mensaje.textContent = error;

        if (error !== '') {
            evento.preventDefault();
        }
    });

    actualizarFechas();
});