<?php
// views/reports/category_report.php
// Reporte por categoría: agrupa los movimientos por categoría, suma los totales
// de cada grupo y permite elegir el periodo.
$active = 'reportes';

// La vista se ejecuta primero y llama al controlador
// (comprueba la sesión, valida el periodo y prepara $reporte, $verificacion...).
require_once __DIR__ . '/../../controllers/categoryReportController.php';

$h      = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
$moneda = fn($valor) => '$' . number_format((float) $valor, 0, ',', '.');
$pct    = fn($valor) => number_format((float) $valor, 1, ',', '.') . '%';
$fecha  = fn($valor) => date('d/m/Y', strtotime($valor));

// Gradiente de la gráfica circular (distribución de los gastos).
$stops = [];
foreach ($segmentos as $s) {
    $stops[] = $s['color'] . ' ' . $s['inicio'] . '% ' . $s['fin'] . '%';
}
$gradiente = implode(', ', $stops);

// Valores de las fechas del formulario (solo si son fechas reales).
$valorDesde = es_fecha_valida($desde) ? $desde : '';
$valorHasta = es_fecha_valida($hasta) ? $hasta : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte por Categoría - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/rubros.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/alerts.css?v=<?= filemtime(__DIR__ . '/../../public/css/alerts.css') ?>">
    <link rel="stylesheet" href="../../public/css/category_report.css?v=<?= filemtime(__DIR__ . '/../../public/css/category_report.css') ?>">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
</head>

<body>

    <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

    <main class="container">
        <div class="page-header">
            <div>
                <h1>Reporte por Categoría</h1>
                <p class="subtitle">Mira cuánto entra y cuánto sale en cada categoría durante el periodo que elijas</p>
            </div>
        </div>

        <!-- Selector de periodo -->
        <form class="card report-filters" id="reportForm" method="GET" action="">
            <div class="form-group">
                <label for="periodo">Periodo</label>
                <select id="periodo" name="periodo">
                    <?php foreach ($periodos as $clave => $etiqueta): ?>
                        <option value="<?= $h($clave) ?>" <?= $periodo === $clave ? 'selected' : '' ?>>
                            <?= $h($etiqueta) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="custom-dates" id="customDates" <?= $periodo === 'personalizado' ? '' : 'hidden' ?>>
                <div class="form-group">
                    <label for="desde">Desde</label>
                    <input type="date" id="desde" name="desde" value="<?= $h($periodo === 'personalizado' ? $desdeEntrada : $valorDesde) ?>"
                        <?= $periodo === 'personalizado' ? '' : 'disabled' ?>>
                </div>
                <div class="form-group">
                    <label for="hasta">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="<?= $h($periodo === 'personalizado' ? $hastaEntrada : $valorHasta) ?>"
                        <?= $periodo === 'personalizado' ? '' : 'disabled' ?>>
                </div>
                <button type="submit" class="btn btn-primary">Aplicar</button>
            </div>
        </form>

        <p class="report-error" id="periodError" role="alert"><?= $h($errorPeriodo ?? '') ?></p>

        <?php if ($errorPeriodo === null): ?>

            <p class="report-range">
                Periodo mostrado: <strong><?= $h($fecha($desde)) ?></strong> al <strong><?= $h($fecha($hasta)) ?></strong>
            </p>

            <?php if ($errorReporte !== null): ?>

                <div class="card report-empty" role="alert">
                    <?= $h($errorReporte) ?>
                </div>

            <?php else: ?>

            <!-- Resumen general -->
            <section class="report-summary" aria-label="Resumen del periodo">
                <div class="card report-card">
                    <p class="report-label">Ingresos</p>
                    <p class="report-value is-in"><?= $moneda($totalIngresos) ?></p>
                </div>
                <div class="card report-card">
                    <p class="report-label">Gastos</p>
                    <p class="report-value is-out"><?= $moneda($totalGastos) ?></p>
                </div>
                <div class="card report-card">
                    <p class="report-label">Saldo (comparado con el presupuesto actual)</p>
                    <p class="report-value<?= $saldo < 0 ? ' is-out' : '' ?>"><?= ($saldo < 0 ? '-' : '') . $moneda(abs($saldo)) ?></p>
                </div>
                <div class="card report-card">
                    <p class="report-label">Movimientos</p>
                    <p class="report-value"><?= (int) $totalMovs ?></p>
                </div>
            </section>

            <?php if (empty($reporte)): ?>
                <div class="card report-empty">
                    No tienes movimientos registrados en este periodo.
                </div>
            <?php else: ?>

                <!-- Gráficas -->
                <section class="report-charts" aria-label="Gráficas por categoría">
                    <div class="card report-chart-card">
                        <h2 class="report-title">¿En qué categorías gastas?</h2>
                        <?php if ($totalGastos > 0): ?>
                            <div class="donut-wrap">
                                <div class="donut" role="img"
                                    aria-label="Distribución de los gastos por categoría"
                                    style="background: conic-gradient(<?= $h($gradiente) ?>);"></div>
                                <ul class="donut-legend">
                                    <?php foreach ($reporte as $fila): ?>
                                        <?php if ($fila['total_gastos'] > 0): ?>
                                            <li>
                                                <span class="dot" style="background: <?= $h($fila['color']) ?>;"></span>
                                                <span class="legend-name"><?= $h($fila['categoria']) ?></span>
                                                <span class="legend-pct"><?= $pct($fila['pct_gastos']) ?></span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="report-note">No hay gastos en este periodo para repartir en la gráfica.</p>
                        <?php endif; ?>
                    </div>

                    <div class="card report-chart-card">
                        <h2 class="report-title">Ingresos y gastos por categoría</h2>
                        <ul class="bar-list">
                            <?php foreach ($reporte as $fila): ?>
                                <?php
                                $anchoIn  = $maxBarra > 0 ? $fila['total_ingresos'] / $maxBarra * 100 : 0;
                                $anchoOut = $maxBarra > 0 ? $fila['total_gastos'] / $maxBarra * 100 : 0;
                                ?>
                                <li class="bar-item">
                                    <span class="bar-name"><?= $h($fila['categoria']) ?></span>
                                    <div class="bar-track" title="Ingresos: <?= $h($moneda($fila['total_ingresos'])) ?>">
                                        <span class="bar-fill bar-in" style="width: <?= round($anchoIn, 2) ?>%"></span>
                                    </div>
                                    <div class="bar-track" title="Gastos: <?= $h($moneda($fila['total_gastos'])) ?>">
                                        <span class="bar-fill bar-out" style="width: <?= round($anchoOut, 2) ?>%"></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="bar-key">
                            <span><span class="dot dot-in"></span> Ingresos</span>
                            <span><span class="dot dot-out"></span> Gastos</span>
                        </p>
                    </div>
                </section>

                <!-- Tabla: totales por categoría -->
                <div class="card table-card report-table-wrap">
                    <table class="data-table" id="categoryReportTable">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th class="text-right">Movimientos</th>
                                <th class="text-right">Ingresos</th>
                                <th class="text-right">Gastos</th>
                                <th class="text-right">Saldo</th>
                                <th class="text-right">% de los gastos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reporte as $fila): ?>
                                <tr>
                                    <td>
                                        <span class="dot" style="background: <?= $h($fila['color']) ?>;"></span>
                                        <span class="category-name"><?= $h($fila['categoria']) ?></span>
                                    </td>
                                    <td class="text-right"><?= (int) $fila['total_movimientos'] ?></td>
                                    <td class="text-right is-in"><?= $moneda($fila['total_ingresos']) ?></td>
                                    <td class="text-right is-out"><?= $moneda($fila['total_gastos']) ?></td>
                                    <td class="text-right<?= $fila['saldo'] < 0 ? ' is-out' : '' ?>">
                                        -
                                    </td>
                                    <td class="text-right"><?= $pct($fila['pct_gastos']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th class="text-right"><?= (int) $totalMovs ?></th>
                                <th class="text-right is-in"><?= $moneda($totalIngresos) ?></th>
                                <th class="text-right is-out"><?= $moneda($totalGastos) ?></th>
                                <th class="text-right<?= $saldo < 0 ? ' is-out' : '' ?>"><?= ($saldo < 0 ? '-' : '') . $moneda(abs($saldo)) ?></th>
                                <th class="text-right"><?= $totalGastos > 0 ? '100,0%' : '—' ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>


            <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script src="../../public/js/category_report.js?v=<?= filemtime(__DIR__ . '/../../public/js/category_report.js') ?>"></script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>