<?php
// ============================================================
// ARCHIVO: views/users/dashboard.php
// PROYECTO: Finanzas personales
// ============================================================

// La vista se abre primero y llama al controlador, que valida la sesión
// (redirige al login si hace falta) y entrega los datos.
require_once __DIR__ . '/../../controllers/dashboardController.php';


// Ruta base para CSS, JavaScript e imágenes.
$base = '../../';
$active = 'inicio';

// Escapar contenido HTML.
$h = static function ($texto): string {
    return htmlspecialchars(
        (string) $texto,
        ENT_QUOTES,
        'UTF-8'
    );
};

// Usuario autenticado.
$nombre = $_SESSION['user_nombre'] ?? '';
$primerNombre = explode(' ', trim($nombre))[0] ?? '';

// Formatear dinero en pesos colombianos.
$cop = static function ($valor): string {
    return '$' . number_format(
        (float) $valor,
        0,
        ',',
        '.'
    ) . ' COP';
};

// Valores recibidos del controlador.
$ingresos = (float) ($ingresos ?? 0);
$gastos = (float) ($gastos ?? 0);

// Cálculo del balance / saldo (Ingresos menos Gastos) con datos reales
$saldo = (float) ($saldo ?? ($ingresos - $gastos));

$movimientosRecibidos = $movimientos ?? [];
$movimientos = is_array($movimientosRecibidos)
    ? $movimientosRecibidos
    : [];

$datosGraficosRecibidos = $datosGraficos ?? [];

$datosGraficos = is_array($datosGraficosRecibidos)
    ? $datosGraficosRecibidos
    : [];

$datosGraficos = array_replace_recursive(
    [
        'meses' => [
            'labels' => [],
            'ingresos' => [],
            'gastos' => [],
        ],
        'categorias' => [
            'labels' => [],
            'valores' => [],
        ],
        'presupuesto' => [
            'labels' => [],
            'presupuestado' => [],
            'gastado' => [],
        ],
    ],
    $datosGraficos
);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Finanzas personales</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="<?= $h($base) ?>public/css/layout.css">
    <link rel="stylesheet" href="<?= $h($base) ?>public/css/dashboard.css">
    <link rel="stylesheet" href="<?= $h($base) ?>public/css/alerts.css">
    <link rel="icon" type="image/webp" href="<?= $h($base) ?>public/img/icon.webp">
</head>

<body class="layout">

    <?php require __DIR__ . '/../layouts/navbar.php'; ?>

    <main class="dash">
        <div class="wrap">

            <header class="dash-head">
                <div>
                    <h1>
                        Hola,
                        <?= $h($primerNombre !== '' ? $primerNombre : 'usuario') ?>
                    </h1>

<?php
$meses = [
    1 => 'enero',
    2 => 'febrero',
    3 => 'marzo',
    4 => 'abril',
    5 => 'mayo',
    6 => 'junio',
    7 => 'julio',
    8 => 'agosto',
    9 => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre'
];

$fechaActual = new DateTimeImmutable('today');

$mesActual = $meses[(int) $fechaActual->format('n')];

$periodoActual = $mesActual . ' de ' . $fechaActual->format('Y');
?>

<p class="dash-sub">
    Resumen financiero de <?= $h($periodoActual) ?>
</p>
                </div>
            </header>

            <?php if ($ingresos == 0 && $gastos == 0 && $movimientos !== []): ?>
                <p class="notice notice-info" role="status">
                    No hay movimientos en <?= $h($periodoActual) ?>. Las tarjetas y los gráficos
                    solo cuentan el mes actual; la tabla «Últimos movimientos» muestra registros
                    de cualquier fecha.
                </p>
            <?php endif; ?>

            <!-- ========================================================== -->
            <!-- AQUÍ SE IMPLEMENTA EL RESUMEN FINANCIERO (TARJETAS)          -->
            <!-- ========================================================== -->
            <section class="stats" aria-label="Resumen financiero">

                <!-- 1. Total de Ingresos -->
                <article class="stat">
                    <h2>Ingresos</h2>
                    <p class="stat-value is-in">
                        <?= $cop($ingresos) ?>
                    </p>
                    <p class="stat-note">Total recibido en el periodo</p>
                </article>

                <!-- 2. Total de Gastos -->
                <article class="stat">
                    <h2>Gastos</h2>
                    <p class="stat-value is-out">
                        <?= $cop($gastos) ?>
                    </p>
                    <p class="stat-note">Total gastado en el periodo</p>
                </article>

                <!-- 3. Balance / Saldo General -->
                <article class="stat stat-balance">
                    <h2>Saldo</h2>
                    <p class="stat-value">
                        <?= $cop($saldo) ?>
                    </p>
                    <p class="stat-note">Ingresos menos gastos</p>
                </article>

            </section>
            <!-- ========================================================== -->

            <div class="dash-grid">

                <section class="panel-card span-8">
                    <header class="panel-head">
                        <h2>Ingresos y gastos por mes</h2>
                        <p>Comparación de los últimos seis meses</p>
                    </header>

                    <div class="chart-box">
                        <canvas
                            id="chart-meses"
                            role="img"
                            aria-label="Comparación mensual de ingresos y gastos"></canvas>
                    </div>
                </section>

                <section class="panel-card span-4">
                    <header class="panel-head">
                        <h2>Gastos por categoría</h2>
                        <p>Distribución de los gastos del mes</p>
                    </header>

                    <div class="chart-box">
                        <canvas
                            id="chart-categorias"
                            role="img"
                            aria-label="Distribución de gastos por categoría"></canvas>
                    </div>
                </section>

                <section class="panel-card span-6">
                    <header class="panel-head">
                        <h2>Presupuesto contra gasto real</h2>
                        <p>Comparación por categoría</p>
                    </header>

                    <div class="chart-box">
                        <canvas
                            id="chart-presupuesto"
                            role="img"
                            aria-label="Comparación del presupuesto y los gastos"></canvas>
                    </div>
                </section>

                <section class="panel-card span-6">
                    <header class="panel-head panel-head-row">
                        <div>
                            <h2>Últimos movimientos</h2>
                            <p>Movimientos registrados</p>
                        </div>
                    </header>

                    <div class="table-wrap">
                        <table class="mov-table">
                            <thead>
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col" class="num">Monto</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($movimientos !== []): ?>

                                    <?php foreach ($movimientos as $movimiento): ?>
                                        <?php
                                        $fecha = $movimiento[0] ?? '';
                                        $descripcion = $movimiento[1] ?? '';
                                        $categoria = $movimiento[2] ?? '';
                                        $tipo = $movimiento[3] ?? '';
                                        $monto = (float) ($movimiento[4] ?? 0);

                                        $esIngreso = $tipo === 'ingreso';
                                        ?>

                                        <tr>
                                            <td class="mov-date">
                                                <?= $h($fecha) ?>
                                            </td>

                                            <td>
                                                <span class="mov-desc">
                                                    <?= $h($descripcion) ?>
                                                </span>

                                                <span class="mov-cat">
                                                    <?= $h($categoria) ?>
                                                </span>
                                            </td>

                                            <td class="num <?= $esIngreso ? 'is-in' : 'is-out' ?>">
                                                <?= $esIngreso ? '+' : '−' ?><?= $cop($monto) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">
                                            Todavía no hay movimientos registrados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

    <script id="dashboard-data" type="application/json">
        <?=
        json_encode(
            $datosGraficos,
            JSON_HEX_TAG
                | JSON_HEX_APOS
                | JSON_HEX_AMP
                | JSON_HEX_QUOT
                | JSON_UNESCAPED_UNICODE
        )
        ?>
    </script>

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"
        defer></script>

    <script src="<?= $h($base) ?>public/js/dashboard.js" defer></script>

</body>

</html>