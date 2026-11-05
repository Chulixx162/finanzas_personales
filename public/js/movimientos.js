// public/js/movimientos.js

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#movimientosTable tbody tr');
    const createForm = document.getElementById('createMovimientoForm');

    // Buscador interactivo en tiempo real
    if (searchInput) {
        searchInput.addEventListener('keyup', () => {
            const term = searchInput.value.toLowerCase().trim();

            tableRows.forEach(row => {
                if (row.classList.contains('empty-row')) return;

                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(term) ? '' : 'none';
            });
        });
    }

    // Confirmación antes de registrar un movimiento
    if (createForm) {
        createForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const tipo = createForm.querySelector('#tipo').value;
            const monto = Number(createForm.querySelector('#monto').value);
            const select = createForm.querySelector('#id_rubro');
            const rubro = select.options[select.selectedIndex].text.replace(/\s+/g, ' ').trim();

            const confirmed = await alerta(
                '¿Registrar movimiento?',
                `Vas a registrar un ${tipo} de $${monto.toLocaleString('es-CO')} en "${rubro}".`,
                'question',
                'Sí, registrar',
                'Cancelar'
            );

            if (confirmed) {
                createForm.submit();
            }
        });
    }

    // Aviso que envía el servidor (?success=created o ?error=...)
    const params = new URLSearchParams(window.location.search);

    if (params.get('success') === 'created' && params.get('excede') === '1') {
        // El gasto se registró, pero el mes ya superó el presupuesto estimado.
        const pesos = (n) => '$' + Number(n).toLocaleString('es-CO');
        const gastado = Number(params.get('gastado')) || 0;
        const presupuesto = Number(params.get('presupuesto')) || 0;
        const texto = 'El gasto se registró, pero superaste el 100 % de tu presupuesto estimado: llevas gastados '
            + pesos(gastado) + ' y tu presupuesto mensual es de ' + pesos(presupuesto)
            + ' (te pasaste por ' + pesos(gastado - presupuesto) + ').';

        if (typeof Swal !== 'undefined' && typeof opcionesAlerta === 'function') {
            Swal.fire(Object.assign(opcionesAlerta(true), {
                title: '¡Superaste el 100 % de tu presupuesto!',
                text: texto,
                icon: 'warning',
                confirmButtonText: 'Entendido'
            }));
        } else {
            mostrarAviso('error', texto);
        }
        window.history.replaceState(null, '', window.location.pathname);
    } else if (params.get('success') === 'created') {
        mostrarAviso('ok', 'El movimiento se registró correctamente.');
        window.history.replaceState(null, '', window.location.pathname);
    } else if (params.has('error')) {
        mostrarAviso('error', params.get('error'));
        window.history.replaceState(null, '', window.location.pathname);
    }
});