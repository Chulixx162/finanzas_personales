<?php
// views/transactions/index.php
$active = 'movimientos';

require_once __DIR__ . '/../../controllers/transactionsController.php';

$h      = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
$moneda = fn($valor) => '$' . number_format((float) $valor, 0, ',', '.');
$fecha  = fn($valor) => date('d/m/Y h:i A', strtotime($valor));

// Categorías que aparecen en los movimientos (para el filtro) y totales iniciales.
$categoriasFiltro = [];
$totalIngresos    = 0;
$totalGastos      = 0;

foreach ($movimientos as $mov) {
    $categoriasFiltro[$mov['categoria_nombre']] = true;

    if ($mov['tipo'] === 'ingreso') {
        $totalIngresos += (float) $mov['monto'];
    } else {
        $totalGastos += (float) $mov['monto'];
    }
}

ksort($categoriasFiltro, SORT_NATURAL | SORT_FLAG_CASE);
$saldo = $totalIngresos - $totalGastos;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Movimientos - Finanzas</title>
    <link rel="stylesheet" href="../../public/css/rubros.css">
    <link rel="icon" href="../../public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
    <link rel="stylesheet" href="../../public/css/filters.css?v=<?= filemtime(__DIR__ . '/../../public/css/filters.css') ?>">
    <link rel="stylesheet" href="../../public/css/movement_alerts.css?v=<?= filemtime(__DIR__ . '/../../public/css/movement_alerts.css') ?>">
    <link rel="stylesheet" href="../../public/css/responsive_cards.css?v=<?= filemtime(__DIR__ . '/../../public/css/responsive_cards.css') ?>">
</head>

<body>

    <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

    <main class="container">
        <div class="page-header">
            <div>
                <h1>Historial de Movimientos</h1>
                <p class="subtitle">Consulta todos tus ingresos y gastos registrados</p>
            </div>
            <a href="create.php" class="btn btn-primary">+ Nuevo Movimiento</a>
        </div>

        <?php if (in_array($alertaPresupuesto['nivel'], ['aviso', 'excedido'], true)): ?>
            <?php $excedido = $alertaPresupuesto['nivel'] === 'excedido'; ?>
            <div class="budget-banner <?= $excedido ? 'budget-banner--danger' : 'budget-banner--warning' ?>" role="alert">
                <div class="budget-banner__text">
                    <strong>
                        <?= $excedido
                            ? 'Superaste el 100 % de tu presupuesto mensual'
                            : 'Ya usaste más del ' . (int) UMBRAL_ALERTA_PRESUPUESTO . ' % de tu presupuesto mensual' ?>
                    </strong>
                    <span>
                        Llevas gastados <?= $moneda($alertaPresupuesto['gastado']) ?> de
                        <?= $moneda($alertaPresupuesto['presupuesto']) ?>
                        (<?= number_format($alertaPresupuesto['porcentaje'], 1, ',', '.') ?> %).
                        <?= $excedido
                            ? 'Te pasaste por ' . $moneda($alertaPresupuesto['gastado'] - $alertaPresupuesto['presupuesto']) . '.'
                            : 'Te quedan ' . $moneda($alertaPresupuesto['disponible']) . ' disponibles.' ?>
                    </span>
                </div>
                <div class="budget-bar" aria-hidden="true">
                    <span style="width: <?= (int) min(100, round($alertaPresupuesto['porcentaje'])) ?>%;"></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filtro único: periodo, tipo, categoría y búsqueda -->
        <form class="card filter-card filter-card--inline" id="filterForm" action="#" novalidate>
            <div class="filter-grid">
                <div class="filter-field">
                    <label for="fPeriodo">Periodo</label>
                    <select id="fPeriodo" class="filter-control">
                        <option value="todo">Todo el tiempo</option>
                        <option value="hoy">Hoy</option>
                        <option value="semana">Esta semana</option>
                        <option value="mes">Este mes</option>
                        <option value="mes_pasado">Mes pasado</option>
                        <option value="anio">Este año</option>
                        <option value="personalizado">Personalizado</option>
                    </select>
                </div>

                <div class="filter-field">
                    <label for="fTipo">Tipo</label>
                    <select id="fTipo" class="filter-control">
                        <option value="">Todos</option>
                        <option value="ingreso">Ingresos</option>
                        <option value="gasto">Gastos</option>
                    </select>
                </div>

                <div class="filter-field">
                    <label for="fCategoria">Categoría</label>
                    <select id="fCategoria" class="filter-control">
                        <option value="">Todas</option>
                        <?php foreach (array_keys($categoriasFiltro) as $nombreCat): ?>
                            <option value="<?= $h($nombreCat) ?>"><?= $h($nombreCat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-field" id="fDesdeBox" hidden>
                    <label for="fDesde">Desde</label>
                    <input type="date" id="fDesde" class="filter-control">
                </div>

                <div class="filter-field" id="fHastaBox" hidden>
                    <label for="fHasta">Hasta</label>
                    <input type="date" id="fHasta" class="filter-control">
                </div>

                <div class="filter-field filter-field--wide">
                    <label for="searchInput">Buscar</label>
                    <input type="text" id="searchInput" class="filter-control"
                        placeholder="Buscar por descripción, rubro o categoría..." autocomplete="off">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="filter-btn filter-btn--apply">Aplicar filtros</button>
                    <button type="button" class="filter-btn filter-btn--clear" id="btnLimpiar">Limpiar filtros</button>
                </div>
            </div>

            <p class="filter-error" id="filterError" role="alert"></p>
        </form>

        <p class="filter-summary" id="resumen" aria-live="polite">
            <span><strong id="rCount"><?= count($movimientos) ?></strong> movimientos</span>
            <span>Ingresos <strong class="is-in" id="rIngresos"><?= $moneda($totalIngresos) ?></strong></span>
            <span>Gastos <strong class="is-out" id="rGastos"><?= $moneda($totalGastos) ?></strong></span>
            <span>Saldo <strong class="<?= $saldo < 0 ? 'is-out' : 'is-in' ?>" id="rSaldo"><?= ($saldo < 0 ? '-' : '') . $moneda(abs($saldo)) ?></strong></span>
        </p>

        <div class="card table-card">
            <table class="data-table data-table--cards tabla-movimientos" id="movimientosTable">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Rubro / Categoría</th>
                        <th>Tipo</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movimientos)): ?>
                        <tr class="empty-row">
                            <td colspan="6">No tienes movimientos registrados aún.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $mov): ?>
                            <tr data-fecha="<?= $h(date('Y-m-d', strtotime($mov['fecha']))) ?>"
                                data-tipo="<?= $h($mov['tipo']) ?>"
                                data-categoria="<?= $h($mov['categoria_nombre']) ?>"
                                data-monto="<?= $h((float) $mov['monto']) ?>">
                                <td class="c-fecha"><?= $fecha($mov['fecha']) ?></td>
                                <td class="c-desc">
                                    <span class="category-name"><?= $h($mov['descripcion'] ?: 'Sin descripción') ?></span>
                                </td>
                                <td class="c-rubro">
                                    <span class="category-name" style="display: block; font-size: 0.95rem; font-weight: 700; color: var(--text);">
                                        <?= $h($mov['rubro_nombre']) ?>
                                    </span>
                                    <span class="category-desc" style="display: block; font-size: 0.82rem; color: var(--muted); margin-top: 3px;">
                                        <?= $h($mov['categoria_nombre']) ?>
                                    </span>
                                </td>
<<<<<<< HEAD
                                <td class="c-tipo">
                                    <span class="btn-action" style="cursor: default; pointer-events: none; background: rgba(<?= $mov['tipo'] === 'ingreso' ? '16, 185, 129' : '239, 68, 68' ?>, 0.12); color: <?= $mov['tipo'] === 'ingreso' ? '#10b981' : '#ef4444' ?>;">
                                        <?= ucfirst($mov['tipo']) ?>
                                    </span>
                                </td>
                                <td class="text-right c-monto" style="font-weight: 800; color: <?= $mov['tipo'] === 'ingreso' ? '#10b981' : '#ef4444' ?>;">
=======
                                <td>
                                    <span class="btn-action" style="cursor: default; pointer-events: none; font-weight: 600; background: <?= $mov['tipo'] === 'ingreso' ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $mov['tipo'] === 'ingreso' ? '#15803D' : '#B91C1C' ?>;">
                                        <?= ucfirst($mov['tipo']) ?>
                                    </span>
                                </td>
                                <td class="text-right" style="font-weight: 800; color: <?= $mov['tipo'] === 'ingreso' ? '#15803D' : '#B91C1C' ?>;">
>>>>>>> b720cb21a78bdb00cd666a1591220478a1dd3c9f
                                    <?= $mov['tipo'] === 'ingreso' ? '+' : '-' ?><?= $moneda($mov['monto']) ?>
                                </td>
                                <td class="text-right c-acc">
                                    <a href="edit.php?id=<?= (int) $mov['id_movimiento'] ?>"
                                        class="btn-action btn-edit" title="Editar movimiento"
                                        aria-label="Editar el movimiento del <?= $h($fecha($mov['fecha'])) ?>">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="policy-note">
            Por políticas del sistema los movimientos no se pueden eliminar.
            Si te equivocaste, usa <strong>Editar</strong> para corregirlo.
        </p>
    </main>

    <script src="../../public/js/transactions.js?v=<?= filemtime(__DIR__ . '/../../public/js/transactions.js') ?>"></script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>