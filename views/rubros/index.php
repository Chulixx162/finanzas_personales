<?php
// views/rubros/index.php
$active = 'rubros';

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
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
    <link rel="stylesheet" href="../../public/css/filters.css?v=<?= filemtime(__DIR__ . '/../../public/css/filters.css') ?>">
</head>

<body>

    <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

    <main class="container">
        <div class="page-header">
            <div>
                <h1>Mis Rubros</h1>
                <p class="subtitle">Consulta los rubros y la categoría a la que pertenece cada uno</p>
            </div>
            <?php if (!empty($estadoCupo['alcanzado'])): ?>
                <span class="btn btn-primary is-disabled" role="link" aria-disabled="true"
                    title="Alcanzaste el límite de tu presupuesto mensual">+ Nuevo Rubro</span>
            <?php else: ?>
                <a href="create.php" class="btn btn-primary">+ Nuevo Rubro</a>
            <?php endif; ?>
        </div>

        <?php
        // Rubros cuyos gastos ya llegaron al 100 % de su monto proyectado.
        $rubrosAlLimite = 0;
        foreach ($rubros as $r) {
            if (rubro_uso_porcentaje($r) >= 100) {
                $rubrosAlLimite++;
            }
        }
        ?>

        <?php if (!empty($estadoCupo['alcanzado'])): ?>
            <p class="notice notice-error" role="alert">
                Alcanzaste el 100&nbsp;% de tu presupuesto mensual:
                <?= $h(rubro_pesos($estadoCupo['asignado'])) ?> de
                <?= $h(rubro_pesos($estadoCupo['presupuesto'])) ?> ya están asignados a rubros.
                No puedes crear más rubros hasta que subas tu presupuesto en
                <a href="../presupuestos/index.php">Presupuestos</a>
                o reduzcas o elimines un rubro.
            </p>
        <?php endif; ?>

        <?php if ($rubrosAlLimite > 0): ?>
            <p class="notice notice-warning" role="status">
                <?= $rubrosAlLimite === 1
                    ? '1 rubro ya gastó el 100&nbsp;% de su monto proyectado.'
                    : $rubrosAlLimite . ' rubros ya gastaron el 100&nbsp;% de su monto proyectado.' ?>
                Están resaltados en la tabla.
            </p>
        <?php endif; ?>

        <div class="card filter-card filter-card--search" role="search">
            <div class="filter-grid">
                <div class="filter-field">
                    <label for="searchInput">Buscar</label>
                    <input type="text" id="searchInput" class="filter-control" placeholder="Buscar rubro o categoría..." autocomplete="off">
                </div>
                <div class="filter-actions">
                    <button type="button" class="filter-btn filter-btn--clear" data-filter-clear>Limpiar filtros</button>
                </div>
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
                            <?php
                            $uso     = rubro_uso_porcentaje($rubro);
                            $alLimite = $uso >= 100;
                            $superado = $uso > 100;
                            ?>
                            <tr class="<?= $alLimite ? 'rubro-row--limite' : '' ?>">
                                <td class="category-name">
                                    <?= $h($rubro['rubro']) ?>
                                    <?php if ($alLimite): ?>
                                        <span class="rubro-badge <?= $superado ? 'rubro-badge--superado' : 'rubro-badge--limite' ?>"
                                            title="Gastado <?= $h(rubro_pesos($rubro['total_gastos'])) ?> de <?= $h(rubro_pesos($rubro['monto_proyectado'])) ?>">
                                            <?= $superado ? 'Superado' : '100&nbsp;% gastado' ?>
                                        </span>
                                    <?php endif; ?>
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
                                    <a href="../../controllers/rubrosController.php?action=delete&id=<?= urlencode($rubro['id_rubro']) ?>"
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

    <script src="../../public/js/filters.js?v=<?= filemtime(__DIR__ . '/../../public/js/filters.js') ?>"></script>
    <script src="../../public/js/rubros.js?v=<?= filemtime(__DIR__ . '/../../public/js/rubros.js') ?>"></script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>