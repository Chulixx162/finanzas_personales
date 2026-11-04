<?php
// views/rubros/edit.php
$base   = '/finanzas_personales/';
$active = 'rubros';

$_GET['action'] = 'edit';
require_once __DIR__ . '/../../controllers/rubrosController.php';

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Rubro - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/rubros.css">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Editar Rubro</h1>
            <p class="subtitle">Modifica los límites o las fechas asignadas a este rubro</p>
        </div>
    </div>

    <div class="card form-card">
        <?php if (!empty($rubroEdit)): ?>
            <!-- Importante: el id="editRubroForm" para interceptar con JS -->
            <form action="/finanzas_personales/controllers/rubrosController.php?action=update" method="POST" id="editRubroForm">
                <input type="hidden" name="id_rubro" value="<?= $h($rubroEdit['id_rubro']) ?>">

                <div class="form-group">
                    <label for="id_categoria">Categoría:</label>
                    <select id="id_categoria" name="id_categoria" required class="custom-select">
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>" <?= $cat['id_categoria'] == $rubroEdit['id_categoria'] ? 'selected' : '' ?>>
                                <?= $h($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre del Rubro:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= $h($rubroEdit['nombre']) ?>" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="monto_proyectado">Monto Proyectado ($):</label>
                    <input type="number" id="monto_proyectado" name="monto_proyectado" step="0.01" min="0" max="9999999999.99" value="<?= $h($rubroEdit['monto_proyectado']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="fecha_inicio">Fecha Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= $h($rubroEdit['fecha_inicio']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="fecha_fin">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?= $h($rubroEdit['fecha_fin']) ?>" required>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Rubro</button>
                </div>
            </form>
        <?php else: ?>
            <p style="color: var(--muted); text-align: center;">El rubro no existe o no tienes permisos para editarlo.</p>
            <div class="form-actions" style="justify-content: center; margin-top: 1rem;">
                <a href="index.php" class="btn btn-secondary">Volver al listado</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Scripts de SweetAlert2 y alertas globales -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/finanzas_personales/public/js/alerts.js"></script>
<script src="/finanzas_personales/public/js/rubros.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>