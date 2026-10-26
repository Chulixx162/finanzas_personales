
<?php
// controllers/dashboardController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Comprobar que el usuario haya iniciado sesión.
if (
    !isset($_SESSION['user_id']) ||
    !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)
) {
    header('Location: ../views/auth/login.php?error=sesion');
    exit;
}

// 2. Consultar el usuario en la base de datos.
require_once __DIR__ . '/../models/usersModel.php';

$idUsuario = (int) $_SESSION['user_id'];
$usuario = obtener_usuario_por_id($idUsuario);

// 3. Si el usuario ya no existe, cerrar la sesión.
if (!$usuario) {
    $_SESSION = [];
    session_destroy();

    header('Location: ../views/auth/login.php?error=sesion');
    exit;
}

// 4. Actualizar los datos guardados en la sesión.
$_SESSION['user_nombre'] = $usuario['nombre'];
$_SESSION['user_email'] = $usuario['correo'];


require_once __DIR__ . '/../models/dashboardModel.php';

// Periodo actual y últimos seis meses.
$hoy = new DateTimeImmutable('today');
$inicioMes = $hoy->modify('first day of this month')->format('Y-m-d');
$finMes = $hoy->modify('first day of next month')->format('Y-m-d');
$inicioSeisMeses = $hoy
    ->modify('first day of this month')
    ->modify('-5 months')
    ->format('Y-m-d');

// Resumen y movimientos reales.
$resumen = dashboard_resumen_mensual(
    $idUsuario,
    $inicioMes,
    $finMes
);

$ingresos = $resumen['ingresos'];
$gastos = $resumen['gastos'];
$saldo = $ingresos - $gastos;

$movimientos = dashboard_movimientos_recientes($idUsuario, 5);

// Datos de los gráficos.
$filasMensuales = dashboard_balance_mensual(
    $idUsuario,
    $inicioSeisMeses,
    $finMes
);

$filasCategorias = dashboard_gastos_por_categoria(
    $idUsuario,
    $inicioMes,
    $finMes
);

$filasPresupuesto = dashboard_presupuesto_por_categoria(
    $idUsuario,
    $inicioMes,
    $finMes
);

// Preparar los seis meses, incluso si alguno no tiene movimientos.
$mapaMeses = [];

foreach ($filasMensuales as $fila) {
    $clave = sprintf('%04d-%02d', $fila['anio'], $fila['mes']);
    $mapaMeses[$clave] = $fila;
}

$etiquetasMeses = [];
$ingresosMeses = [];
$gastosMeses = [];

$cursor = new DateTimeImmutable($inicioSeisMeses);

for ($i = 0; $i < 6; $i++) {
    $clave = $cursor->format('Y-m');
    $etiquetasMeses[] = $cursor->format('M');

    $ingresosMeses[] = (float) ($mapaMeses[$clave]['ingresos'] ?? 0);
    $gastosMeses[] = (float) ($mapaMeses[$clave]['gastos'] ?? 0);

    $cursor = $cursor->modify('+1 month');
}

$datosGraficos = [
    'meses' => [
        'labels' => $etiquetasMeses,
        'ingresos' => $ingresosMeses,
        'gastos' => $gastosMeses,
    ],
    'categorias' => [
        'labels' => array_column($filasCategorias, 'categoria'),
        'valores' => array_column($filasCategorias, 'total'),
    ],
    'presupuesto' => [
        'labels' => array_column($filasPresupuesto, 'categoria'),
        'presupuestado' => array_column($filasPresupuesto, 'presupuestado'),
        'gastado' => array_column($filasPresupuesto, 'gastado'),
    ],
];


// 5. Autorizar la carga de la vista.
define('DASHBOARD_CONTROLLER_ACCESS', true);

require __DIR__ . '/../views/users/dashboard.php';
