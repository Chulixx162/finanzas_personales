<?php
// views/reports/print_pdf.php
if (!isset($movimientos)) {
    exit('Acceso denegado');
}

$h      = fn($t) => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');
$moneda = fn($v) => '$' . number_format((float) $v, 0, ',', '.');
$fecha  = fn($v) => date('d/m/Y H:i', strtotime($v));

// Totales para el resumen contable y agregación para gráficos
$totalIngresos = 0;
$totalGastos   = 0;
$gastosPorCategoria = [];

foreach ($movimientos as $m) {
    $monto = (float) $m['monto'];
    if ($m['tipo'] === 'ingreso') {
        $totalIngresos += $monto;
    } else {
        $totalGastos += $monto;
        $cat = !empty($m['categoria_nombre']) ? $m['categoria_nombre'] : 'Sin Categoría';
        $gastosPorCategoria[$cat] = ($gastosPorCategoria[$cat] ?? 0) + $monto;
    }
}

// Balance neto clásico (Ingresos - Gastos)
$balanceNeto = $totalIngresos - $totalGastos;

// CÁLCULO SOLO PARA VISTA: Diferencia entre Presupuesto y Gastos (no toca base de datos)
$presupuesto = isset($presupuestoActual) && $presupuestoActual !== null ? (float) $presupuestoActual : null;
$presupuestoRestante = $presupuesto !== null ? ($presupuesto - $totalGastos) : null;

// Preparación de datos JSON para Chart.js
$catLabels = json_encode(array_keys($gastosPorCategoria), JSON_UNESCAPED_UNICODE);
$catValues = json_encode(array_values($gastosPorCategoria));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte_Movimientos_<?= date('Ymd_His') ?></title>
    <link rel="icon" href="../public/img/icon.webp" type="image/x-icon">
    <style>
        <?= file_get_contents(__DIR__ . '/../../public/css/print_pdf.css'); ?>
    </style>
    <!-- Chart.js vía CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <div class="actions-bar">
        <button class="btn btn-print" onclick="window.print()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                <path d="M6 14h12v8H6z" />
            </svg>
            Imprimir o Guardar como PDF
        </button>
        <button class="btn btn-close" onclick="window.close()">Cerrar</button>
    </div>

    <div class="report-wrapper">
        <header class="header">
            <div class="brand">
                <h1>Reporte de Movimientos</h1>
                <p>Historial Financiero Personal</p>
            </div>
            <div class="meta-info">
                <p>Fecha de emisión: <strong><?= date('d/m/Y h:i A') ?></strong></p>
                <p>Registros analizados: <strong><?= count($movimientos) ?></strong></p>
            </div>
        </header>

        <!-- Bloque de Resumen Contable (4 tarjetas con la resta de presupuesto en memoria) -->
        <section class="summary-grid">
            <div class="summary-box">
                <div class="label">Total Ingresos</div>
                <div class="amount amount-ingreso">+<?= $moneda($totalIngresos) ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Total Gastos</div>
                <div class="amount amount-gasto">-<?= $moneda($totalGastos) ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Balance Neto</div>
                <div class="amount <?= $balanceNeto >= 0 ? 'amount-neto-pos' : 'amount-neto-neg' ?>">
                    <?= ($balanceNeto >= 0 ? '+' : '') . $moneda($balanceNeto) ?>
                </div>
            </div>
            <div class="summary-box">
                <div class="label">Presupuesto Restante</div>
                <?php if ($presupuestoRestante !== null): ?>
                    <div class="amount <?= $presupuestoRestante >= 0 ? 'amount-disponible' : 'amount-neto-neg' ?>">
                        <?= ($presupuestoRestante >= 0 ? '+' : '') . $moneda($presupuestoRestante) ?>
                    </div>
                <?php else: ?>
                    <div class="amount" style="color: #94a3b8; font-size: 14px;">No definido</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Filtros activos -->
        <?php if (!empty($filtros['desde']) || !empty($filtros['hasta']) || !empty($filtros['tipo'])): ?>
            <div class="filters-pill">
                <strong>Filtros aplicados:</strong>
                <?= !empty($filtros['tipo']) ? 'Tipo: ' . ucfirst($filtros['tipo']) . ' &bull; ' : '' ?>
                <?= !empty($filtros['desde']) ? 'Desde: ' . date('d/m/Y', strtotime($filtros['desde'])) . ' &bull; ' : '' ?>
                <?= !empty($filtros['hasta']) ? 'Hasta: ' . date('d/m/Y', strtotime($filtros['hasta'])) : '' ?>
            </div>
        <?php endif; ?>

        <!-- 1. Tabla de Movimientos Arriba -->
        <table>
            <thead>
                <tr>
                    <th style="width: 16%;">Fecha</th>
                    <th style="width: 12%;">Tipo</th>
                    <th style="width: 32%;">Rubro / Categoría</th>
                    <th style="width: 24%;">Descripción</th>
                    <th class="text-right" style="width: 16%;">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">
                            No hay movimientos registrados que coincidan con los criterios seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                        <tr>
                            <td class="date-col"><?= $fecha($m['fecha']) ?></td>
                            <td>
                                <span class="badge-type badge-<?= $m['tipo'] ?>">
                                    <?= ucfirst($m['tipo']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="item-title"><?= $h($m['rubro_nombre']) ?></div>
                                <div class="item-category"><?= $h($m['categoria_nombre']) ?></div>
                            </td>
                            <td style="color: #64748b;"><?= $h($m['descripcion'] ?: '—') ?></td>
                            <td class="text-right amount-cell <?= $m['tipo'] === 'ingreso' ? 'amount-ingreso' : 'amount-gasto' ?>">
                                <?= $m['tipo'] === 'ingreso' ? '+' : '-' ?><?= $moneda($m['monto']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 2. Sección de Gráficos Abajo -->
        <?php if (!empty($movimientos)): ?>
            <section class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">Comparativa Ingresos vs Gastos</h3>
                    <div class="chart-container">
                        <canvas id="balanceChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">Distribución de Gastos por Categoría</h3>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <footer class="footer">
            <span>Generado automáticamente desde el módulo de Finanzas Personales</span>
            <span>Documento Oficial</span>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hasData = <?= !empty($movimientos) ? 'true' : 'false' ?>;

            if (hasData) {
                // Paleta exacta de dashboard.js
                const colores = {
                    principal: '#3157a4',
                    oscuro: '#182b49',
                    suave: '#e9eef7',
                    dorado: '#e6b94c',
                    peligro: '#d94a4a',
                    exito: '#299764',
                    borde: '#e0e5ed',
                    texto: '#687386',
                    morado: '#8b78c6',
                    teal: '#4e9da6'
                };

                const dinero = {
                    format: (valor) => '$' + Math.round(Number(valor) || 0).toLocaleString('es-CO')
                };

                const dineroCorto = (valor) => {
                    if (valor >= 1000000) return (valor / 1000000).toLocaleString('es-CO') + ' M';
                    if (valor >= 1000) return (valor / 1000).toLocaleString('es-CO') + ' mil';
                    return valor;
                };

                Chart.defaults.font.family = '"Plus Jakarta Sans", system-ui, sans-serif';
                Chart.defaults.color = colores.texto;

                // 1. Gráfico de Barras: Comparativa Ingresos vs Gastos
                new Chart(document.getElementById('balanceChart'), {
                    type: 'bar',
                    data: {
                        labels: ['Ingresos', 'Gastos'],
                        datasets: [{
                            data: [<?= $totalIngresos ?>, <?= $totalGastos ?>],
                            backgroundColor: [colores.exito, colores.peligro],
                            borderRadius: 5,
                            maxBarThickness: 42
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => ' ' + dinero.format(ctx.raw)
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: colores.borde
                                },
                                ticks: {
                                    callback: (valor) => dineroCorto(Number(valor))
                                }
                            }
                        }
                    }
                });

                // 2. Gráfico de Donut: Gastos por Categoría
                const catLabels = <?= $catLabels ?>;
                const catValues = <?= $catValues ?>;

                if (catLabels.length > 0) {
                    new Chart(document.getElementById('categoryChart'), {
                        type: 'doughnut',
                        data: {
                            labels: catLabels,
                            datasets: [{
                                data: catValues,
                                backgroundColor: [
                                    colores.principal,
                                    colores.dorado,
                                    colores.oscuro,
                                    colores.exito,
                                    colores.peligro,
                                    colores.morado,
                                    colores.teal
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: false,
                            cutout: '64%',
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        usePointStyle: true,
                                        pointStyle: 'rectRounded',
                                        padding: 12,
                                        boxWidth: 10,
                                        font: {
                                            size: 11,
                                            weight: '600'
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${ctx.label}: ${dinero.format(ctx.parsed)}`
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // Disparar diálogo de impresión
            setTimeout(() => {
                window.print();
            }, 450);
        });
    </script>
</body>

</html>