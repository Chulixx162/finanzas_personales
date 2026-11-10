<?php
// views/presupuestos/index.php
$base   = '../../';
$active = 'presupuestos';

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
    <title>Presupuestos - Finanzas</title>
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
                <h1>Presupuestos</h1>
                <p class="subtitle">Define tu presupuesto mensual; cada cambio queda guardado en tu historial</p>
            </div>
            <a href="../budget_history/index.php" class="btn btn-ghost">Ver historial de cambios</a>
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

        <!-- Registrar un cambio: el trigger de la BD guarda el historial automáticamente -->
        <div class="card budget-form-card">
            <h2 class="budget-form-title">Cambiar presupuesto mensual</h2>
            <form action="../../controllers/budgetHistoryController.php"
                method="POST" id="budgetForm" novalidate
                data-actual="<?= $presupuestoActual !== null ? $h((string) (int) $presupuestoActual) : '' ?>">
                <input type="hidden" name="csrf_token" value="<?= $h($csrf) ?>">

                <div class="budget-form-row">
                    <div class="form-group">
                        <label for="presupuesto">Nuevo presupuesto (COP):</label>
                        <input type="text" id="presupuesto" name="presupuesto"
                            inputmode="numeric" autocomplete="off" maxlength="16"
                            placeholder="Ej. 1.800.000"
                            value="<?= $presupuestoActual !== null ? $h(number_format($presupuestoActual, 0, ',', '.')) : '' ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar cambio</button>
                </div>
                <?php if ($minAsignado > 0): ?>
                    <p class="usage-note" style="margin: 12px 0 0;">
                        No deberías bajarlo de <?= $moneda($minAsignado) ?>, que es lo que ya asignaste a tus rubros en un mes.
                    </p>
                <?php endif; ?>
            </form>
        </div>
    </main>

    <script src="../../public/js/budget_history.js?v=<?= filemtime(__DIR__ . '/../../public/js/budget_history.js') ?>"></script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>