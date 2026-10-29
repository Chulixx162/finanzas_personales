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
    <title>Nueva Categoría - Finanzas</title>
    <link rel="stylesheet" href="/finanzas_personales/public/css/categories.css?v=<?= filemtime(__DIR__ . '/../../public/css/categories.css') ?>">
    <link rel="icon" href="/finanzas_personales/public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="/finanzas_personales/public/css/layout.css">
    <link rel="stylesheet" href="/finanzas_personales/public/css/alerts.css?v=<?= filemtime(__DIR__ . '/../../public/css/alerts.css') ?>">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Crear Nueva Categoría</h1>
    </div>

    <?php if (!empty($aviso)): ?>
        <div id="avisoData" hidden
             data-tipo="<?= htmlspecialchars($aviso[0]) ?>"
             data-texto="<?= htmlspecialchars($aviso[1]) ?>"></div>
    <?php endif; ?>

    <div class="card form-card">
        <form action="/finanzas_personales/controllers/categoriesController.php?action=store" method="POST" id="createCategoryForm" novalidate>
            <div class="form-group">
                <label for="nombre">Nombre de la Categoría:</label>
                <input type="text" id="nombre" name="nombre" required maxlength="50" placeholder="Ej. Alimentación, Transporte..." autocomplete="off">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción (opcional):</label>
                <input type="text" id="descripcion" name="descripcion" maxlength="255" placeholder="Ej. Gastos relacionados con mercado y restaurantes..." autocomplete="off">
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Categoría</button>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/finanzas_personales/public/js/alerts.js?v=<?= filemtime(__DIR__ . '/../../public/js/alerts.js') ?>"></script>
<script src="/finanzas_personales/public/js/categories.js?v=<?= filemtime(__DIR__ . '/../../public/js/categories.js') ?>"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>