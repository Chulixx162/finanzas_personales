<?php
$base = '/finanzas_personales/';
$active = 'categorias';

$_GET['action'] = 'edit';
require_once __DIR__ . '/../../controllers/categoriesController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/categories.css?v=<?= filemtime(__DIR__ . '/../../public/css/categories.css') ?>">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/alertas.css?v=<?= filemtime(__DIR__ . '/../../public/css/alertas.css') ?>">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Editar Categoría</h1>
    </div>

    <?php if (!empty($aviso)): ?>
        <div id="avisoData" hidden
             data-tipo="<?= htmlspecialchars($aviso[0]) ?>"
             data-texto="<?= htmlspecialchars($aviso[1]) ?>"></div>
    <?php endif; ?>

    <div class="card form-card">
        <?php if (!empty($categoriaEdit)): ?>
            <form action="/finanzas_personales/controllers/categoriesController.php?action=update" method="POST" id="editCategoryForm" novalidate>
                <input type="hidden" name="id" value="<?= htmlspecialchars($categoriaEdit['id']) ?>">

                <div class="form-group">
                    <label for="nombre">Nombre de la Categoría:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($categoriaEdit['nombre']) ?>" required maxlength="50" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <input type="text" id="descripcion" name="descripcion" value="<?= htmlspecialchars($categoriaEdit['descripcion'] ?? '') ?>" maxlength="255" autocomplete="off">
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Categoría</button>
                </div>
            </form>
        <?php else: ?>
            <p style="color: var(--muted); text-align: center;">La categoría no existe o no tienes permisos para editarla.</p>
            <div class="form-actions" style="justify-content: center; margin-top: 1rem;">
                <a href="index.php" class="btn btn-secondary">Volver al listado</a>
            </div>
        <?php endif; ?>
    </div>
</main>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/finanzas_personales/public/js/alertas.js?v=<?= filemtime(__DIR__ . '/../../public/js/alertas.js') ?>"></script>
<script src="/finanzas_personales/public/js/categories.js?v=<?= filemtime(__DIR__ . '/../../public/js/categories.js') ?>"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>