<?php
// views/budget_history/index.php
$active = 'historial';

// La vista se ejecuta primero y llama al controlador
// (comprueba la sesión, guarda cambios y prepara $historial, $aviso, $csrf...).
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

    <!-- Uso del presupuesto del mes actual: categorías divididas en rubros -->
    <?php
    // Porcentaje mostrado: nunca negativo, pero SIN tope en 100 para ver cuánto se excedió.
    $pct        = fn($v) => number_format(max(0, (float) $v), 1, ',', '.') . '%';
    // Rojo desde el 100 % (presupuesto agotado o excedido), amarillo desde el 80 %.
    $claseBarra = fn($p) => $p >= 100 ? 'bar-danger' : ($p >= 80 ? 'bar-warn' : 'bar-ok');
    // El ancho de la barra sí se limita a 100.
    $ancho      = fn($p) => max(0, min(100, (float) $p));

    // Los GASTOS sí pueden superar el presupuesto: se avisa con alerta y mensaje.
    $gastoExcedido = $hayPresupuesto && $totalGastado > $presupuestoActual;

    // Los RUBROS ya no pueden superarlo; esto solo aparece con datos creados antes del límite.
    $rubrosExcedidos = $hayPresupuesto && $totalAsignado > $presupuestoActual;

    $textoExceso = '';
    if ($gastoExcedido) {
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
                        (tus rubros pasan por <?= $moneda($totalAsignado - $presupuestoActual) ?>)
                    <?php else: ?>
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
                        (te pasaste por <?= $moneda($totalGastado - $presupuestoActual) ?>)
                    <?php else: ?>
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
                        $pctDelPresupuesto = $hayPresupuesto ? $c['asignado'] / $presupuestoActual * 100 : null;
                        $pctEjecutado      = $c['asignado'] > 0 ? $c['gastado'] / $c['asignado'] * 100 : null;
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