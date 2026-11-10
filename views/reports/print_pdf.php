<?php
// views/reports/print_pdf.php
if (!isset($movimientos)) {
    exit('Acceso denegado');
}

$h      = fn($t) => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');
$moneda = fn($v) => '$' . number_format((float) $v, 0, ',', '.');
$fecha  = fn($v) => date('d/m/Y H:i', strtotime($v));

// Totales para el resumen contable
$totalIngresos = 0;
$totalGastos   = 0;

foreach ($movimientos as $m) {
    if ($m['tipo'] === 'ingreso') {
        $totalIngresos += (float)$m['monto'];
    } else {
        $totalGastos += (float)$m['monto'];
    }
}
$balanceNeto = $totalIngresos - $totalGastos;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte_Movimientos_<?= date('Ymd_His') ?></title>
    <link rel="icon" href="/finanzas_personales/public/img/icon.webp" type="image/x-icon">
    <link rel="stylesheet" href="/finanzas_personales/public/css/print_pdf.css?v=<?= time() ?>">
</head>

<body>

    <div class="actions-bar">
        <button class="btn btn-print" onclick="window.print()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                <path d="M6 14h12v8H6z" />
            </svg>
            Imprimir o Guardar como PDF
        </button>
        <button class="btn btn-close" onclick="window.close()">Cerrar</button>
    </div>

    <div class="report-wrapper">
        <header class="header">
            <div class="brand">
                <h1>Reporte de Movimientos</h1>
                <p>Historial Financiero Personal</p>
            </div>
            <div class="meta-info">
                <p>Fecha de emisión: <strong><?= date('d/m/Y h:i A') ?></strong></p>
                <p>Registros analizados: <strong><?= count($movimientos) ?></strong></p>
            </div>
        </header>

        <!-- Bloque de Totales -->
        <section class="summary-grid">
            <div class="summary-box">
                <div class="label">Total Ingresos</div>
                <div class="amount amount-ingreso">+<?= $moneda($totalIngresos) ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Total Gastos</div>
                <div class="amount amount-gasto">-<?= $moneda($totalGastos) ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Balance Neto</div>
                <div class="amount <?= $balanceNeto >= 0 ? 'amount-neto-pos' : 'amount-neto-neg' ?>">
                    <?= ($balanceNeto >= 0 ? '+' : '') . $moneda($balanceNeto) ?>
                </div>
            </div>
        </section>

        <!-- Filtros activos -->
        <?php if (!empty($filtros['desde']) || !empty($filtros['hasta']) || !empty($filtros['tipo'])): ?>
            <div class="filters-pill">
                <strong>Filtros aplicados:</strong>
                <?= !empty($filtros['tipo']) ? 'Tipo: ' . ucfirst($filtros['tipo']) . ' &bull; ' : '' ?>
                <?= !empty($filtros['desde']) ? 'Desde: ' . date('d/m/Y', strtotime($filtros['desde'])) . ' &bull; ' : '' ?>
                <?= !empty($filtros['hasta']) ? 'Hasta: ' . date('d/m/Y', strtotime($filtros['hasta'])) : '' ?>
            </div>
        <?php endif; ?>

        <!-- Tabla estructurada -->
        <table>
            <thead>
                <tr>
                    <th style="width: 16%;">Fecha</th>
                    <th style="width: 12%;">Tipo</th>
                    <th style="width: 32%;">Rubro / Categoría</th>
                    <th style="width: 24%;">Descripción</th>
                    <th class="text-right" style="width: 16%;">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">
                            No hay movimientos registrados que coincidan con los criterios seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                        <tr>
                            <td class="date-col"><?= $fecha($m['fecha']) ?></td>
                            <td>
                                <span class="badge-type badge-<?= $m['tipo'] ?>">
                                    <?= ucfirst($m['tipo']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="item-title"><?= $h($m['rubro_nombre']) ?></div>
                                <div class="item-category"><?= $h($m['categoria_nombre']) ?></div>
                            </td>
                            <td style="color: #64748b;"><?= $h($m['descripcion'] ?: '—') ?></td>
                            <td class="text-right amount-cell <?= $m['tipo'] === 'ingreso' ? 'amount-ingreso' : 'amount-gasto' ?>">
                                <?= $m['tipo'] === 'ingreso' ? '+' : '-' ?><?= $moneda($m['monto']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <footer class="footer">
            <span>Generado automáticamente desde el módulo de Finanzas Personales</span>
            <span>Documento Oficial</span>
        </footer>
    </div>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 300);
        });
    </script>
</body>

</html>