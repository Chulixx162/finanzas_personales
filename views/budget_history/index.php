<?php
// views/budget_history/index.php
$base   = '/finanzas_personales/';
$active = 'historial';

// La vista se ejecuta primero y llama al controlador.
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
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Historial de Presupuesto</h1>
            <p class="subtitle">Consulta cada cambio que has hecho a tu presupuesto mensual</p>
        </div>
    </div>

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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>