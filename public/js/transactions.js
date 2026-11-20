// public/js/transactions.js
// Historial de movimientos: filtro único (periodo, tipo, categoría, fechas y búsqueda),
// resumen en vivo y confirmaciones al registrar.

document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filterForm');
    const createForm = document.getElementById('createMovimientoForm');

    if (filterForm) {
        iniciarFiltros(filterForm);
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

    mostrarAvisoDelServidor();
});

function iniciarFiltros(form) {
    const periodo = document.getElementById('fPeriodo');
    const tipo = document.getElementById('fTipo');
    const categoria = document.getElementById('fCategoria');
    const desde = document.getElementById('fDesde');
    const hasta = document.getElementById('fHasta');
    const cajaDesde = document.getElementById('fDesdeBox');
    const cajaHasta = document.getElementById('fHastaBox');
    const buscador = document.getElementById('searchInput');
    const error = document.getElementById('filterError');
    const btnLimpiar = document.getElementById('btnLimpiar');

    const filas = Array.from(document.querySelectorAll('#movimientosTable tbody tr'))
        .filter((fila) => !fila.classList.contains('empty-row'));
    const cuerpo = document.querySelector('#movimientosTable tbody');

    const resumen = {
        total: document.getElementById('rCount'),
        ingresos: document.getElementById('rIngresos'),
        gastos: document.getElementById('rGastos'),
        saldo: document.getElementById('rSaldo')
    };

    const pesos = (n) => '$' + Math.abs(n).toLocaleString('es-CO');

    // Quita tildes y mayúsculas: "alimentacion" encuentra "Alimentación".
    const normalizar = (texto) => texto
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    // Texto de búsqueda de cada fila, calculado una sola vez.
    filas.forEach((fila) => {
        fila.dataset.busqueda = normalizar(fila.textContent.replace(/\s+/g, ' '));
    });

    // Fila que se muestra cuando ningún movimiento coincide.
    const filaVacia = document.createElement('tr');
    filaVacia.className = 'empty-row';
    filaVacia.hidden = true;
    filaVacia.innerHTML = '<td colspan="5">No hay movimientos con esos filtros.</td>';
    if (cuerpo && filas.length > 0) {
        cuerpo.appendChild(filaVacia);
    }

    const iso = (fecha) => {
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return `${fecha.getFullYear()}-${mes}-${dia}`;
    };

    // Rango [desde, hasta] (YYYY-MM-DD) según el periodo elegido.
    function rangoDelPeriodo() {
        const hoy = new Date();
        const y = hoy.getFullYear();
        const m = hoy.getMonth();

        switch (periodo.value) {
            case 'hoy':
                return [iso(hoy), iso(hoy)];
            case 'semana': {
                const lunes = new Date(y, m, hoy.getDate() - ((hoy.getDay() + 6) % 7));
                const domingo = new Date(lunes.getFullYear(), lunes.getMonth(), lunes.getDate() + 6);
                return [iso(lunes), iso(domingo)];
            }
            case 'mes':
                return [iso(new Date(y, m, 1)), iso(new Date(y, m + 1, 0))];
            case 'mes_pasado':
                return [iso(new Date(y, m - 1, 1)), iso(new Date(y, m, 0))];
            case 'anio':
                return [`${y}-01-01`, `${y}-12-31`];
            case 'personalizado':
                return [desde.value, hasta.value];
            default:
                return ['', ''];
        }
    }

    function actualizarFechas() {
        const personalizado = periodo.value === 'personalizado';
        cajaDesde.hidden = !personalizado;
        cajaHasta.hidden = !personalizado;

        if (!personalizado) {
            error.textContent = '';
        }
    }

    function aplicar() {
        const [min, max] = rangoDelPeriodo();

        if (periodo.value === 'personalizado' && min && max && min > max) {
            error.textContent = 'La fecha inicial no puede ser posterior a la fecha final.';
            return;
        }

        error.textContent = '';

        const texto = normalizar(buscador.value);
        let visibles = 0;
        let ingresos = 0;
        let gastos = 0;

        filas.forEach((fila) => {
            const fecha = fila.dataset.fecha;
            const coincide =
                (!min || fecha >= min) &&
                (!max || fecha <= max) &&
                (!tipo.value || fila.dataset.tipo === tipo.value) &&
                (!categoria.value || fila.dataset.categoria === categoria.value) &&
                (texto === '' || fila.dataset.busqueda.includes(texto));

            fila.hidden = !coincide;

            if (coincide) {
                visibles++;
                const monto = Number(fila.dataset.monto) || 0;
                if (fila.dataset.tipo === 'ingreso') {
                    ingresos += monto;
                } else {
                    gastos += monto;
                }
            }
        });

        filaVacia.hidden = visibles > 0 || filas.length === 0;

        const saldo = ingresos - gastos;
        resumen.total.textContent = visibles;
        resumen.ingresos.textContent = pesos(ingresos);
        resumen.gastos.textContent = pesos(gastos);
        resumen.saldo.textContent = (saldo < 0 ? '-' : '') + pesos(saldo);
        resumen.saldo.className = saldo < 0 ? 'is-out' : 'is-in';
    }

    periodo.addEventListener('change', actualizarFechas);

    form.addEventListener('submit', (evento) => {
        evento.preventDefault();
        aplicar();
    });

    // El buscador responde mientras se escribe.
    buscador.addEventListener('input', aplicar);

    btnLimpiar.addEventListener('click', () => {
        form.reset();
        actualizarFechas();
        aplicar();
        periodo.focus();
    });

    actualizarFechas();
}

// Aviso que envía el servidor (?success=created o ?error=...)
function mostrarAvisoDelServidor() {
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
}