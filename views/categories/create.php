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
    <link rel="stylesheet" href="/finanzas_personales/public/css/categories.css">
    <link rel="icon" href="/finanzas_personales/public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="/finanzas_personales/public/css/layout.css">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Crear Nueva Categoría</h1>
    </div>

    <div class="card form-card">
        <form action="/finanzas_personales/controllers/categoriesController.php?action=store" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre de la Categoría:</label>
                <input type="text" id="nombre" name="nombre" required placeholder="Ej. Alimentación, Transporte..." autocomplete="off">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción (opcional):</label>
                <input type="text" id="descripcion" name="descripcion" placeholder="Ej. Gastos relacionados con mercado y restaurantes..." autocomplete="off">
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Categoría</button>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>