<?php
// views/budget_history/index.php
$active = 'historial';

require_once __DIR__ . '/../../controllers/budgetHistoryController.php';

$h         = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
$moneda    = fn($valor) => '$' . number_format((float) $valor, 0, ',', '.');
$fechaHora = fn($valor) => date('d/m/Y H:i', strtotime($valor));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Presupuesto - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/categories.css">
    <link rel="stylesheet" href="../../public/css/budget_history.css?v=<?= filemtime(__DIR__ . '/../../public/css/budget_history.css') ?>">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/alerts.css?v=<?= filemtime(__DIR__ . '/../../public/css/alerts.css') ?>">
</head>

<body>

    <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

    <main class="container">
        <div class="page-header">
            <div>
                <h1>Historial de Presupuesto</h1>
                <p class="subtitle">Consulta el uso de tu presupuesto y cada modificación en orden cronológico</p>
            </div>
            <a href="../presupuestos/index.php" class="btn btn-primary">Cambiar presupuesto</a>
        </div>

        <?php if (!empty($aviso)): ?>
            <div id="avisoData" hidden
                data-tipo="<?= $h($aviso[0]) ?>"
                data-texto="<?= $h($aviso[1]) ?>"></div>
        <?php endif; ?>

        <section class="history-summary" aria-label="Resumen del historial">
            <div class="card summary-card">
                <p class="summary-label">Presupuesto actual</p>
                <p class="summary-value">
                    <?= $presupuestoActual !== null ? $moneda($presupuestoActual) : 'Sin definir' ?>
                </p>
            </div>
            <div class="card summary-card">
                <p class="summary-label">Cambios registrados</p>
                <p class="summary-value"><?= (int) $totalCambios ?></p>
            </div>
            <div class="card summary-card">
                <p class="summary-label">Último cambio</p>
                <p class="summary-value">
                    <?= $ultimoCambio ? $h($fechaHora($ultimoCambio)) : 'Sin cambios' ?>
                </p>
            </div>
        </section>

        <?php
        $pct        = fn($v) => number_format(max(0, (float) $v), 1, ',', '.') . '%';
        $claseBarra = fn($p) => $p >= 100 ? 'bar-danger' : ($p >= 80 ? 'bar-warn' : 'bar-ok');
        $ancho      = fn($p) => max(0, min(100, (float) $p));

        $gastoExcedido = $hayPresupuesto && $totalGastado > $presupuestoActual;
        $rubrosExcedidos = $hayPresupuesto && $totalAsignado > $presupuestoActual;

        $textoExceso = '';
        if ($gastoExcedido) {
            // CORREGIDO: Se agregó la resta ($totalGastado - $presupuestoActual)
            $textoExceso = 'Superaste el 100 % de tu presupuesto estimado: llevas gastados '
                . $moneda($totalGastado) . ' y tu presupuesto mensual es de '
                . $moneda($presupuestoActual) . ' (te pasaste por '
                . $moneda($totalGastado - $presupuestoActual) . ').';
        }
        ?>
        <h2 class="budget-form-title">Uso de tu presupuesto este mes</h2>

        <?php if ($gastoExcedido): ?>
            <div class="usage-alert" role="alert">
                <span class="usage-alert-icon" aria-hidden="true">⚠</span>
                <span><?= $h($textoExceso) ?></span>
            </div>
            <div id="excesoData" hidden data-texto="<?= $h($textoExceso) ?>"></div>
        <?php endif; ?>

        <?php if ($rubrosExcedidos): ?>
            <div class="usage-alert usage-alert--warn" role="status">
                <span class="usage-alert-icon" aria-hidden="true">⚠</span>
                <span>
                    Tus rubros suman <?= $moneda($totalAsignado) ?> y superan tu presupuesto mensual de
                    <?= $moneda($presupuestoActual) ?>. Los rubros no pueden pasar del presupuesto:
                    reduce el monto de alguno o sube tu presupuesto.
                </span>
            </div>
        <?php endif; ?>

        <?php if (!$hayPresupuesto): ?>
            <p class="usage-note">Define tu presupuesto mensual para ver qué porcentaje llevas ocupado.</p>
        <?php else: ?>
            <section class="usage-grid" aria-label="Porcentaje del presupuesto ocupado">
                <div class="card usage-card">
                    <p class="summary-label">Asignado a rubros</p>
                    <p class="usage-percent<?= $rubrosExcedidos ? ' is-over' : '' ?>"><?= $pct($pctAsignado) ?></p>
                    <div class="usage-bar" role="progressbar" aria-label="Porcentaje asignado a rubros"
                        aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $ancho($pctAsignado) ?>">
                        <span class="<?= $claseBarra($pctAsignado) ?>" style="width: <?= $ancho($pctAsignado) ?>%"></span>
                    </div>
                    <p class="usage-detail">
                        <?= $moneda($totalAsignado) ?> de <?= $moneda($presupuestoActual) ?>
                        <?php if ($rubrosExcedidos): ?>
                            <!-- CORREGIDO: Se agregó la resta -->
                            (tus rubros pasan por <?= $moneda($totalAsignado - $presupuestoActual) ?>)
                        <?php else: ?>
                            <!-- CORREGIDO: Se agregó la resta -->
                            (te quedan <?= $moneda($presupuestoActual - $totalAsignado) ?> por asignar)
                        <?php endif; ?>
                    </p>
                    <p class="usage-hint">Tus rubros no pueden sumar más que tu presupuesto mensual.</p>
                </div>
                <div class="card usage-card<?= $gastoExcedido ? ' usage-card--over' : '' ?>">
                    <p class="summary-label">
                        Gastado
                        <?php if ($gastoExcedido): ?>
                            <span class="usage-pill">Excedido</span>
                        <?php endif; ?>
                    </p>
                    <p class="usage-percent<?= $gastoExcedido ? ' is-over' : '' ?>"><?= $pct($pctGastado) ?></p>
                    <div class="usage-bar" role="progressbar" aria-label="Porcentaje gastado"
                        aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $ancho($pctGastado) ?>">
                        <span class="<?= $claseBarra($pctGastado) ?>" style="width: <?= $ancho($pctGastado) ?>%"></span>
                    </div>
                    <p class="usage-detail">
                        <?= $moneda($totalGastado) ?> de <?= $moneda($presupuestoActual) ?>
                        <?php if ($gastoExcedido): ?>
                            <!-- CORREGIDO: Se agregó la resta -->
                            (te pasaste por <?= $moneda($totalGastado - $presupuestoActual) ?>)
                        <?php else: ?>
                            <!-- CORREGIDO: Se agregó la resta -->
                            (te quedan <?= $moneda($presupuestoActual - $totalGastado) ?>)
                        <?php endif; ?>
                    </p>
                    <p class="usage-hint">Los gastos sí pueden pasarse; si pasa, te avisamos.</p>
                </div>
            </section>
        <?php endif; ?>

        <div class="card table-card history-table-wrap" style="margin-bottom: 24px;">
            <table class="data-table" id="usageTable">
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th class="text-right">Asignado (rubros)</th>
                        <th class="text-right">% del presupuesto</th>
                        <th class="text-right">Gastado</th>
                        <th class="text-right">% de lo asignado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usoCategorias)): ?>
                        <tr class="empty-row">
                            <td colspan="5">No tienes rubros vigentes este mes.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usoCategorias as $c): ?>
                            <?php
                            // CORREGIDO: Se agregó la operación matemática para el porcentaje
                            $pctDelPresupuesto = $hayPresupuesto ? ($c['asignado'] / $presupuestoActual) * 100 : null;
                            $pctEjecutado      = $c['asignado'] > 0 ? ($c['gastado'] / $c['asignado']) * 100 : null;
                            ?>
                            <tr>
                                <td class="category-name"><?= $h($c['categoria']) ?></td>
                                <td class="text-right"><?= $moneda($c['asignado']) ?></td>
                                <td class="text-right"><?= $pctDelPresupuesto !== null ? $pct($pctDelPresupuesto) : '—' ?></td>
                                <td class="text-right"><?= $moneda($c['gastado']) ?></td>
                                <td class="text-right">
                                    <?php if ($pctEjecutado === null): ?>
                                        —
                                    <?php else: ?>
                                        <span class="<?= $pctEjecutado > 100 ? 'text-over' : '' ?>"><?= $pct($pctEjecutado) ?></span>
                                        <div class="usage-bar" style="margin-top: 6px;">
                                            <span class="<?= $claseBarra($pctEjecutado) ?>" style="width: <?= $ancho($pctEjecutado) ?>%"></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Reporte Financiero Mensual -->
    <div style="margin-top: 40px; margin-bottom: 20px;">
        <h2 class="budget-form-title" style="margin-bottom: 4px;">Reporte Financiero Mensual</h2>
        <p class="usage-note">Selecciona un periodo para consultar los ingresos, gastos y el balance de ese mes.</p>
    </div>

    <!-- Selector de periodo ajustado exactamente al ancho estándar -->
    <div class="card" style="margin-bottom: 24px; padding: 16px 20px;">
        <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 12px;">
            <label for="periodo" style="font-weight: 600; font-size: 0.95rem; white-space: nowrap;">Filtrar por mes:</label>
            <select name="periodo" id="periodo" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color, #d1d5db); background: var(--bg-input, #fff); color: inherit; width: 250px;">
                <?php foreach ($mesesDisponibles as $m): ?>
                    <option value="<?= $m; ?>" <?= ($m === $periodoSeleccionado) ? 'selected' : ''; ?>>
                        <?= date('F Y', strtotime($m . '-01')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Tarjetas de resumen del reporte (idénticas en ancho a las de arriba) -->
    <section class="history-summary" aria-label="Resumen financiero del periodo" style="margin-bottom: 24px;">
        <div class="card summary-card">
            <p class="summary-label">Total Ingresos</p>
            <p class="summary-value" style="color: #10b981;"><?= $moneda($reporteMensual['totalIngresos']) ?></p>
        </div>
        <div class="card summary-card">
            <p class="summary-label">Total Gastos</p>
            <p class="summary-value" style="color: #ef4444;"><?= $moneda($reporteMensual['totalGastos']) ?></p>
        </div>
        <div class="card summary-card">
            <p class="summary-label">Balance del Periodo</p>
            <p class="summary-value" style="color: <?= $reporteMensual['balance'] >= 0 ? '#10b981' : '#ef4444' ?>;">
                <?= $moneda($reporteMensual['balance']) ?>
            </p>
        </div>
    </section>

    <!-- Tabla de movimientos del reporte -->
    <div class="card table-card history-table-wrap" style="margin-bottom: 32px;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Tipo</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reporteMensual['transacciones'])): ?>
                    <tr class="empty-row">
                        <td colspan="4" style="text-align: center; padding: 24px;">No hay movimientos registrados para este periodo.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reporteMensual['transacciones'] as $t): ?>
                        <tr>
                            <td><?= $h(date('d/m/Y', strtotime($t['fecha']))) ?></td>
                            <td class="category-name"><?= $h($t['descripcion']) ?></td>
                            <td>
                                <span class="badge" style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; background: <?= $t['tipo'] === 'ingreso' ? 'rgba(16, 185, 129, 0.1); color: #10b981;' : 'rgba(239, 68, 68, 0.1); color: #ef4444;' ?>">
                                    <?= ucfirst($t['tipo']) ?>
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 600; color: <?= $t['tipo'] === 'ingreso' ? '#10b981' : '#ef4444' ?>;">
                                <?= ($t['tipo'] === 'gasto' ? '-' : '+') . $moneda($t['monto']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

        <h2 class="budget-form-title" style="margin-bottom: 4px;">Cambios de tu presupuesto mensual</h2>
        <p class="usage-note">Cada fila muestra cuánto subió (▲) o bajó (▼) tu presupuesto frente al valor anterior.</p>

        <div class="card table-card history-table-wrap">
            <table class="data-table" id="historyTable">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th class="text-right">Presupuesto anterior</th>
                        <th class="text-right">Presupuesto nuevo</th>
                        <th class="text-right">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historial)): ?>
                        <tr class="empty-row">
                            <td colspan="4">Aún no has cambiado tu presupuesto.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historial as $fila): ?>
                            <?php
                            $dif = (float) $fila['diferencia'];

                            if ($dif > 0) {
                                $clase = 'badge-up';
                                $texto = '▲ +' . $moneda($dif);
                            } elseif ($dif < 0) {
                                $clase = 'badge-down';
                                $texto = '▼ -' . $moneda(abs($dif));
                            } else {
                                $clase = 'badge-equal';
                                $texto = 'Sin cambio';
                            }
                            ?>
                            <tr>
                                <td><?= $h($fechaHora($fila['fecha_cambio'])) ?></td>
                                <td class="text-right"><?= $moneda($fila['presupuesto_anterior']) ?></td>
                                <td class="text-right category-name"><?= $moneda($fila['presupuesto_nuevo']) ?></td>
                                <td class="text-right"><span class="<?= $clase ?>"><?= $h($texto) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    

    <script src="../../public/js/budget_history.js?v=<?= filemtime(__DIR__ . '/../../public/js/budget_history.js') ?>"></script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>
