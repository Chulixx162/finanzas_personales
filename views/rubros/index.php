 <?php
require_once __DIR__ . '/../../controllers/rubrosController.php';
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
                            <td class="category-name">
                                <?= $h($rubro['rubro']) ?>
                            </td>

                            <td>
                                <?= $h($rubro['categoria']) ?>
                            </td>

                            <td class="text-right">
                                <?= '$' . number_format((float)($rubro['monto_proyectado'] ?? 0), 0, ',', '.') ?>
                            </td>

                            <td>
                                <?= !empty($rubro['fecha_inicio'])
                                    ? date('d/m/Y', strtotime($rubro['fecha_inicio']))
                                    : 'Sin fecha' ?>
                                al
                                <?= !empty($rubro['fecha_fin'])
                                    ? date('d/m/Y', strtotime($rubro['fecha_fin']))
                                    : 'Sin fecha' ?>
                            </td>

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

<script src="../../public/js/rubros.js?v=<?= filemtime(__DIR__ . '/../../public/js/rubros.js') ?>"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>
```
