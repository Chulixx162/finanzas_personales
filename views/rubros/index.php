<?php
// views/rubros/index.php
$base   = '/finanzas_personales/';
$active = 'rubros';

require_once __DIR__ . '/../../controllers/rubrosController.php';

$h      = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
$moneda = fn($valor) => '$' . number_format((float) $valor, 0, ',', '.');
$fecha  = fn($valor) => date('d/m/Y', strtotime($valor));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Rubros - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/rubros.css">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="<?= $base ?>public/css/alerts.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Mis Rubros</h1>
            <p class="subtitle">Consulta los rubros y la categoría a la que pertenece cada uno</p>
        </div>
        <a href="create.php" class="btn btn-primary">+ Nuevo Rubro</a>
    </div>

    <div class="table-toolbar" style="margin-bottom: 1rem;">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Buscar rubro o categoría..." autocomplete="off">
        </div>
    </div>

    <div class="card table-card">
        <table class="data-table" id="rubrosTable">
            <thead>
                <tr>
                    <th>Rubro</th>
                    <th>Categoría</th>
                    <th class="text-right">Monto proyectado</th>
                    <th>Periodo</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rubros)): ?>
                    <tr class="empty-row">
                        <td colspan="5">No tienes rubros registrados aún.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rubros as $rubro): ?>
                        <tr>
                            <td class="category-name"><?= $h($rubro['rubro']) ?></td>
                            <td><?= $h($rubro['categoria']) ?></td>
                            <td class="text-right"><?= $moneda($rubro['monto_proyectado']) ?></td>
                            <td><?= $fecha($rubro['fecha_inicio']) ?> al <?= $fecha($rubro['fecha_fin']) ?></td>
                            <td class="text-right actions-cell">
                                <a href="edit.php?id=<?= urlencode($rubro['id_rubro']) ?>" 
                                   class="btn-action btn-edit" title="Editar">
                                    Editar
                                </a>
                                <a href="/finanzas_personales/controllers/rubrosController.php?action=delete&id=<?= urlencode($rubro['id_rubro']) ?>" 
                                   class="btn-action btn-delete" 
                                   data-name="<?= $h($rubro['rubro']) ?>" title="Eliminar">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../public/js/alerts.js"></script>
<script src="../../public/js/rubros.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>