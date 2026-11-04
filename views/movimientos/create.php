<?php
// views/movimientos/create.php
$base   = '/finanzas_personales/';
$active = 'movimientos';

$_GET['action'] = 'create';
require_once __DIR__ . '/../../controllers/movimientosController.php';

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Movimiento - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/rubros.css">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="../../public/css/layout.css">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Registrar Movimiento</h1>
            <p class="subtitle">Añade un nuevo ingreso o gasto a tus rubros presupuestales</p>
        </div>
    </div>

    <div class="card form-card">
        <?php if (empty($rubros)): ?>
            <p style="color: var(--muted); text-align: center;">Necesitas crear al menos un rubro antes de registrar un movimiento.</p>
            <div class="form-actions" style="justify-content: center; margin-top: 1rem;">
                <a href="/finanzas_personales/views/rubros/create.php" class="btn btn-primary">+ Crear Rubro</a>
            </div>
        <?php else: ?>
            <form action="/finanzas_personales/controllers/movimientosController.php?action=store" method="POST">
                
                <div class="form-group">
                    <label for="tipo">Tipo de Movimiento:</label>
                    <select id="tipo" name="tipo" required class="custom-select">
                        <option value="gasto" selected>Gasto</option>
                        <option value="ingreso">Ingreso</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_rubro">Rubro Asociado:</label>
                    <select id="id_rubro" name="id_rubro" required class="custom-select">
                        <option value="" disabled selected>-- Selecciona un rubro --</option>
                        <?php foreach ($rubros as $r): ?>
                            <option value="<?= $r['id_rubro'] ?>">
                                <?= $h($r['rubro_nombre']) ?> (<?= $h($r['categoria_nombre']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="monto">Monto ($):</label>
                    <input type="number" id="monto" name="monto" step="0.01" min="0.01" max="9999999999.99" required placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha y Hora:</label>
                    <input type="datetime-local" id="fecha" name="fecha" required value="<?= date('Y-m-d\TH:i') ?>">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción (Opcional):</label>
                    <input type="text" id="descripcion" name="descripcion" placeholder="Ej. Compra de supermercado, pago de servicios..." autocomplete="off">
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar Movimiento</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>