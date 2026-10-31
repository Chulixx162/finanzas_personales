<?php
// views/rubros/index.php
$base   = '/finanzas_personales/';
$active = 'rubros';

// La vista se ejecuta primero y llama al controlador.
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
    <link rel="stylesheet" href="../../public/css/categories.css">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="<?= $base ?>public/css/alerts.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Mis Rubros</h1>
            <p class="subtitle">Consulta los rubros y la categoría a la que pertenece cada uno</p>
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
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rubros)): ?>
                    <tr class="empty-row">
                        <td colspan="4">No tienes rubros registrados aún.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rubros as $rubro): ?>
                        <tr>
                            <td class="category-name"><?= $h($rubro['rubro']) ?></td>
                            <td><?= $h($rubro['categoria']) ?></td>
                            <td class="text-right"><?= $moneda($rubro['monto_proyectado']) ?></td>
                            <td><?= $fecha($rubro['fecha_inicio']) ?> al <?= $fecha($rubro['fecha_fin']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>