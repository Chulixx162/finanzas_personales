<?php
$base = '/finanzas_personales/';
$active = 'categorias';

require_once __DIR__ . '/../../controllers/categoriesController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Categorías - Finanzas</title>
    <link rel="stylesheet" href="/finanzas_personales/public/css/categories.css">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Mis Categorías</h1>
            <p class="subtitle">Administra y clasifica tus rubros financieros</p>
        </div>
        <a href="create.php" class="btn btn-primary">+ Nueva Categoría</a>
    </div>

    <div class="table-toolbar">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Buscar categoría..." autocomplete="off">
        </div>
    </div>

    <div class="card table-card">
        <table class="data-table" id="categoriesTable">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr class="empty-row">
                        <td colspan="3">No tienes categorías registradas aún.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categorias as $cat): ?>
                        <tr>
                            <td class="category-name">
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($cat['descripcion'] ?? 'Sin descripción') ?>
                            </td>
                            <td class="text-right actions-cell">
                                <a href="editar.php?id=<?= urlencode($cat['id']) ?>" 
                                   class="btn-action btn-edit" title="Editar">
                                    Editar
                                </a>
                                <a href="/finanzas_personales/controllers/categoriesController.php?action=delete&id=<?= urlencode($cat['id']) ?>" 
                                   class="btn-action btn-delete" 
                                   data-name="<?= htmlspecialchars($cat['nombre']) ?>" title="Eliminar">
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
<script src="/finanzas_personales/public/js/alertas.js"></script>
<script src="/finanzas_personales/public/js/categories.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>