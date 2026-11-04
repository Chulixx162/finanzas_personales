<?php
// views/rubros/create.php
$base   = '/finanzas_personales/';
$active = 'rubros';

$_GET['action'] = 'create';
require_once __DIR__ . '/../../controllers/rubrosController.php';

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Rubro - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/rubros.css">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="../../public/css/layout.css">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Crear Nuevo Rubro</h1>
            <p class="subtitle">Asigna una proyección financiera a una de tus categorías</p>
        </div>
    </div>

    <div class="card form-card">
        <?php if (empty($categorias)): ?>
            <p style="color: var(--muted); text-align: center;">Necesitas crear al menos una categoría antes de agregar un rubro.</p>
            <div class="form-actions" style="justify-content: center; margin-top: 1rem;">
                <a href="/finanzas_personales/views/categories/create.php" class="btn btn-primary">+ Crear Categoría</a>
            </div>
        <?php else: ?>
            <form action="/finanzas_personales/controllers/rubrosController.php?action=store" method="POST">
                <div class="form-group">
                    <label for="id_categoria">Categoría:</label>
                    <select id="id_categoria" name="id_categoria" required class="custom-select">
                        <option value="" disabled selected>-- Selecciona una categoría --</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>"><?= $h($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre del Rubro:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej. Mercado mensual, Arriendo..." autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="monto_proyectado">Monto Proyectado ($):</label>
                    <input type="number" id="monto_proyectado" name="monto_proyectado" step="0.01" min="0" max="9999999999.99" required placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="fecha_inicio">Fecha Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?= date('Y-m-01') ?>">
                </div>

                <div class="form-group">
                    <label for="fecha_fin">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" required value="<?= date('Y-12-31') ?>">
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Rubro</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>