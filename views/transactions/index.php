<?php
// views/movimientos/index.php
$active = 'movimientos';

require_once __DIR__ . '/../../controllers/transactionsController.php';

$h      = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
$moneda = fn($valor) => '$' . number_format((float) $valor, 0, ',', '.');
$fecha  = fn($valor) => date('d/m/Y h:i A', strtotime($valor));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Movimientos - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/rubros.css">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Historial de Movimientos</h1>
            <p class="subtitle">Consulta todos tus ingresos y gastos registrados</p>
        </div>
        <a href="create.php" class="btn btn-primary">+ Nuevo Movimiento</a>
    </div>

    <div class="table-toolbar" style="margin-bottom: 1rem;">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Buscar por descripción, rubro o categoría..." autocomplete="off">
        </div>
    </div>

    <div class="card table-card">
        <table class="data-table" id="movimientosTable">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Rubro / Categoría</th>
                    <th>Tipo</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr class="empty-row">
                        <td colspan="5">No tienes movimientos registrados aún.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td style="white-space: nowrap; color: var(--muted);"><?= $fecha($mov['fecha']) ?></td>
                            <td>
                                <span class="category-name"><?= $h($mov['descripcion'] ?: 'Sin descripción') ?></span>
                            </td>
                            <td>
                                <span class="category-name" style="display: block; font-size: 0.95rem; font-weight: 700; color: var(--text);">
                                    <?= $h($mov['rubro_nombre']) ?>
                                </span>
                                <span class="category-desc" style="display: block; font-size: 0.82rem; color: var(--muted); margin-top: 3px;">
                                    <?= $h($mov['categoria_nombre']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="btn-action" style="cursor: default; pointer-events: none; background: rgba(<?= $mov['tipo'] === 'ingreso' ? '16, 185, 129' : '239, 68, 68' ?>, 0.12); color: <?= $mov['tipo'] === 'ingreso' ? '#10b981' : '#ef4444' ?>;">
                                    <?= ucfirst($mov['tipo']) ?>
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 800; color: <?= $mov['tipo'] === 'ingreso' ? '#10b981' : '#ef4444' ?>;">
                                <?= $mov['tipo'] === 'ingreso' ? '+' : '-' ?><?= $moneda($mov['monto']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="../../public/js/transactions.js?v=<?= filemtime(__DIR__ . '/../../public/js/movimientos.js') ?>"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>