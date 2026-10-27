
/* public/js/dashboard.js
   Gráficos de Finanzas personales con datos de MySQL.
*/

(() => {
    const elementoDatos = document.getElementById('dashboard-data');

    if (!elementoDatos || typeof Chart === 'undefined') {
        console.error('No se encontraron los datos del Dashboard o Chart.js.');
        return;
    }

    let datos;

   //traer los datos del dashboard desde el modelo de datos
    try {
        datos = JSON.parse(elementoDatos.textContent);
    } catch (error) {
        console.error('Error al parsear los datos del Dashboard:', error);
        return;
    }

    const css = getComputedStyle(document.documentElement);
    const color = (variable, alternativo) =>
        css.getPropertyValue(variable).trim() || alternativo;

    const colores = {
        principal: color('--primary', '#3157a4'),
        oscuro: color('--deep', '#182b49'),
        suave: color('--soft-2', '#e9eef7'),
        dorado: color('--gold', '#e6b94c'),
        peligro: color('--danger', '#d94a4a'),
        exito: color('--success', '#299764'),
        borde: color('--border', '#e0e5ed'),
        texto: color('--muted', '#687386'),
    };

    const dinero = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
    });

    const dineroCorto = (valor) => {
        if (valor >= 1000000) {
            return (valor / 1000000).toLocaleString('es-CO') + ' M';
        }

        if (valor >= 1000) {
            return (valor / 1000).toLocaleString('es-CO') + ' mil';
        }

        return valor;
    };

    Chart.defaults.font.family =
        '"Plus Jakarta Sans", system-ui, sans-serif';
    Chart.defaults.color = colores.texto;

    const leyenda = {
        position: 'top',
        labels: {
            usePointStyle: true,
            pointStyle: 'rectRounded',
            padding: 16,
        },
    };

    const opcionesDinero = {
        beginAtZero: true,
        grid: { color: colores.borde },
        ticks: {
            callback: (valor) => dineroCorto(Number(valor)),
        },
    };

    // 1. Ingresos y gastos por mes.
    const canvasMeses = document.getElementById('chart-meses');

    if (canvasMeses) {
        new Chart(canvasMeses, {
            type: 'bar',
            data: {
                labels: datos.meses.labels,
                datasets: [
                    {
                        label: 'Ingresos',
                        data: datos.meses.ingresos,
                        backgroundColor: colores.exito,
                        borderRadius: 5,
                    },
                    {
                        label: 'Gastos',
                        data: datos.meses.gastos,
                        backgroundColor: colores.peligro,
                        borderRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: leyenda,
                    tooltip: {
                        callbacks: {
                            label: (ctx) =>
                                `${ctx.dataset.label}: ${dinero.format(ctx.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: opcionesDinero,
                },
            },
        });
    }

    // 2. Gastos agrupados por categoría.
    const canvasCategorias =
        document.getElementById('chart-categorias');

    if (canvasCategorias) {
        const categorias = datos.categorias.labels;
        const valores = datos.categorias.valores;

        new Chart(canvasCategorias, {
            type: 'doughnut',
            data: {
                labels: categorias.length ? categorias : ['Sin gastos'],
                datasets: [{
                    data: valores.length ? valores : [1],
                    backgroundColor: [
                        colores.principal,
                        colores.dorado,
                        colores.oscuro,
                        colores.exito,
                        colores.peligro,
                        '#8b78c6',
                        '#4e9da6',
                    ],
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                plugins: {
                    legend: {
                        ...leyenda,
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                if (!valores.length) return 'Sin gastos registrados';

                                return `${ctx.label}: ${dinero.format(ctx.parsed)}`;
                            },
                        },
                    },
                },
            },
        });
    }

    // 3. Presupuesto frente a gasto real por categoría.
    const canvasPresupuesto =
        document.getElementById('chart-presupuesto');

    if (canvasPresupuesto) {
        const presupuesto = datos.presupuesto;

        new Chart(canvasPresupuesto, {
            type: 'bar',
            data: {
                labels: presupuesto.labels,
                datasets: [
                    {
                        label: 'Presupuestado',
                        data: presupuesto.presupuestado,
                        backgroundColor: colores.suave,
                        borderRadius: 5,
                    },
                    {
                        label: 'Gastado',
                        data: presupuesto.gastado,
                        backgroundColor: presupuesto.gastado.map(
                            (valor, indice) =>
                                valor > presupuesto.presupuestado[indice]
                                    ? colores.peligro
                                    : colores.principal
                        ),
                        borderRadius: 5,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: leyenda,
                    tooltip: {
                        callbacks: {
                            label: (ctx) =>
                                `${ctx.dataset.label}: ${dinero.format(ctx.parsed.x)}`,
                        },
                    },
                },
                scales: {
                    x: opcionesDinero,
                    y: { grid: { display: false } },
                },
            },
        });
    }
})();
