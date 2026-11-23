<?php
// views/transactions/edit.php
$active = 'movimientos';

$_GET['action'] = 'edit';
require_once __DIR__ . '/../../controllers/transactionsController.php';

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Movimiento - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/rubros.css">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
    <link rel="stylesheet" href="../../public/css/movement_alerts.css?v=<?= filemtime(__DIR__ . '/../../public/css/movement_alerts.css') ?>">
</head>

<body>

    <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

    <main class="container">
        <div class="page-header">
            <div>
                <h1>Editar Movimiento</h1>
                <p class="subtitle">Corrige los datos de un ingreso o gasto ya registrado</p>
            </div>
        </div>

        <div class="card form-card">
            <?php if (empty($movimiento)): ?>
                <p style="color: var(--muted); text-align: center;">El movimiento no existe o no tienes permisos para editarlo.</p>
                <div class="form-actions" style="justify-content: center; margin-top: 1rem;">
                    <a href="index.php" class="btn btn-primary">Volver al historial</a>
                </div>
            <?php else: ?>
                <form action="../../controllers/transactionsController.php?action=update" method="POST" id="editMovimientoForm">
                    <input type="hidden" name="csrf_token" value="<?= $h($csrf) ?>">
                    <input type="hidden" name="id_movimiento" value="<?= (int) $movimiento['id_movimiento'] ?>">

                    <div class="form-group">
                        <label for="tipo">Tipo de Movimiento:</label>
                        <select id="tipo" name="tipo" required class="custom-select">
                            <option value="gasto" <?= $movimiento['tipo'] === 'gasto' ? 'selected' : '' ?>>Gasto</option>
                            <option value="ingreso" <?= $movimiento['tipo'] === 'ingreso' ? 'selected' : '' ?>>Ingreso</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_rubro">Rubro Asociado:</label>
                        <select id="id_rubro" name="id_rubro" required class="custom-select">
                            <?php foreach ($rubros as $r): ?>
                                <option value="<?= (int) $r['id_rubro'] ?>"
                                    <?= (int) $r['id_rubro'] === (int) $movimiento['id_rubro'] ? 'selected' : '' ?>>
                                    <?= $h($r['rubro_nombre']) ?> (<?= $h($r['categoria_nombre']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="monto">Monto ($):</label>
                        <input type="number" id="monto" name="monto" step="0.01" min="0.01" max="9999999999.99" required
                            value="<?= $h(number_format((float) $movimiento['monto'], 2, '.', '')) ?>">
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha y Hora:</label>
                        <input type="datetime-local" id="fecha" name="fecha" required
                            value="<?= $h(date('Y-m-d\TH:i', strtotime($movimiento['fecha']))) ?>">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción (Opcional):</label>
                        <input type="text" id="descripcion" name="descripcion" maxlength="255" autocomplete="off"
                            value="<?= $h($movimiento['descripcion']) ?>">
                    </div>

                    <p class="policy-note policy-note--form">
                        Por políticas del sistema los movimientos no se pueden eliminar: corrígelo aquí si algo está mal.
                    </p>

                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <script src="../../public/js/transactions.js?v=<?= filemtime(__DIR__ . '/../../public/js/transactions.js') ?>"></script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>