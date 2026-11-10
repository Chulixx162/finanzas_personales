<?php
// controllers/categoryReportController.php
// Maneja la sesión, valida el periodo, calcula los totales y verifica los cálculos.
// Las consultas están en el modelo; la vista solo muestra lo que se prepara aquí.

require_once __DIR__ . '/../config/session.php';

// 1. Comprobar que la persona haya iniciado sesión.
//    El id sale SIEMPRE de la sesión: cada persona solo ve su propio reporte.
$idUsuario = requerir_login();

require_once __DIR__ . '/../models/categoryReportModel.php';

// 2. Periodos disponibles en el selector.
$periodos = [
    'mes_actual'      => 'Este mes',
    'mes_anterior'    => 'Mes anterior',
    'ultimos_3_meses' => 'Últimos 3 meses',
    'anio_actual'     => 'Este año',
    'todo'            => 'Todo el historial',
    'personalizado'   => 'Personalizado',
];

/**
 * Devuelve true si el texto es una fecha real con formato Y-m-d (ej. 2026-11-09).
 */
function es_fecha_valida(string $texto): bool
{
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $texto);
    return $fecha !== false && $fecha->format('Y-m-d') === $texto;
}

/**
 * Convierte el periodo elegido en [desde, hasta, error].
 * Los periodos fijos se calculan en el servidor; el personalizado se valida.
 */
function resolver_periodo(string $periodo, string $desdeTexto, string $hastaTexto): array
{
    $hoy = new DateTimeImmutable('today');

    switch ($periodo) {
        case 'mes_anterior':
            $inicio = $hoy->modify('first day of last month');
            return [$inicio->format('Y-m-d'), $inicio->format('Y-m-t'), null];

        case 'ultimos_3_meses':
            $inicio = $hoy->modify('first day of this month')->modify('-2 months');
            return [$inicio->format('Y-m-d'), $hoy->format('Y-m-d'), null];

        case 'anio_actual':
            return [$hoy->format('Y-01-01'), $hoy->format('Y-m-d'), null];

        case 'todo':
            // Rango amplio para incluir todos los movimientos (incluso los de fechas futuras).
            return ['2000-01-01', '2100-12-31', null];

        case 'personalizado':
            if ($desdeTexto === '' || $hastaTexto === '') {
                return [$desdeTexto, $hastaTexto, 'Elige la fecha inicial y la fecha final del periodo.'];
            }
            if (!es_fecha_valida($desdeTexto) || !es_fecha_valida($hastaTexto)) {
                return [$desdeTexto, $hastaTexto, 'Las fechas no son válidas.'];
            }
            if ($desdeTexto > $hastaTexto) {
                return [$desdeTexto, $hastaTexto, 'La fecha inicial no puede ser posterior a la fecha final.'];
            }
            return [$desdeTexto, $hastaTexto, null];

        default: // mes_actual
            return [$hoy->format('Y-m-01'), $hoy->format('Y-m-t'), null];
    }
}

/**
 * Pesos a centavos enteros, para comparar dinero sin errores de decimales.
 */
function a_centavos(float $valor): int
{
    return (int) round($valor * 100);
}

// 3. Leer y validar los filtros (GET).
$periodo = is_string($_GET['periodo'] ?? null) ? $_GET['periodo'] : 'anio_actual';
if (!array_key_exists($periodo, $periodos)) {
    $periodo = 'anio_actual';
}

$desdeEntrada = is_string($_GET['desde'] ?? null) ? trim($_GET['desde']) : '';
$hastaEntrada = is_string($_GET['hasta'] ?? null) ? trim($_GET['hasta']) : '';

[$desde, $hasta, $errorPeriodo] = resolver_periodo($periodo, $desdeEntrada, $hastaEntrada);

// 4. Datos que usa la vista (vacíos si el periodo no es válido).
$reporte       = [];
$totalIngresos = 0.0;
$totalGastos   = 0.0;
$saldo         = 0.0;
$totalMovs     = 0;
$segmentos     = [];
$maxBarra      = 0.0;
$verificacion  = null;
$errorReporte  = null;   // mensaje si falla la consulta a la base de datos
$directo       = null;   // totales generales del procedimiento (se llena más abajo)

// Colores de la gráfica de distribución de gastos (se repiten si hay más categorías).
$paleta = ['#6D28D9', '#FBBF24', '#10B981', '#EF4444', '#3B82F6', '#EC4899', '#14B8A6', '#F97316', '#8B5CF6', '#64748B'];

if ($errorPeriodo === null) {
    $reporte = obtener_reporte_por_categoria($idUsuario, $desde, $hasta);

    // 5. Obtener los totales generales mediante el procedimiento almacenado.
    $directo = obtener_totales_periodo($idUsuario, $desde, $hasta);

    // Si algún procedimiento falló, el modelo ya guardó el detalle en el log de PHP.
    // Se avisa en pantalla en lugar de mostrar un reporte vacío.
    if ($reporte === null || $directo === null) {
        $errorReporte = 'No se pudo generar el reporte en este momento. Inténtalo de nuevo; si el problema continúa, avisa al equipo de desarrollo.';
        $reporte      = [];
    }
}

if ($errorPeriodo === null && $errorReporte === null) {

    // Sumar los grupos en centavos para comparar sin errores de decimales.
    $centIngresos = 0;
    $centGastos   = 0;
    $centMovs     = 0;

    foreach ($reporte as $fila) {
        $centIngresos += a_centavos($fila['total_ingresos']);
        $centGastos   += a_centavos($fila['total_gastos']);
        $centMovs     += $fila['total_movimientos'];
    }

    // Las tarjetas resumen utilizan los totales generales del procedimiento.
    $totalIngresos = $directo['ingresos'];
    $totalGastos   = $directo['gastos'];
    $saldo         = $totalIngresos - $totalGastos;
    $totalMovs     = $directo['movimientos'];

    // 6. Cálculos por categoría: saldo, porcentaje del gasto total y segmentos de la gráfica.
    $acumulado = 0.0;

    foreach ($reporte as $i => $fila) {
        $reporte[$i]['saldo'] = (a_centavos($fila['total_ingresos']) - a_centavos($fila['total_gastos'])) / 100;
        $reporte[$i]['pct_gastos'] = $centGastos > 0
            ? a_centavos($fila['total_gastos']) / $centGastos * 100
            : 0.0;
        $reporte[$i]['color'] = $paleta[$i % count($paleta)];

        $maxBarra = max($maxBarra, $fila['total_ingresos'], $fila['total_gastos']);

        if ($fila['total_gastos'] > 0) {
            $inicio     = $acumulado;
            $acumulado += $reporte[$i]['pct_gastos'];
            $segmentos[] = [
                'color'  => $reporte[$i]['color'],
                'inicio' => round($inicio, 4),
                'fin'    => round($acumulado, 4),
            ];
        }
    }

    // 7. Validar los cálculos: la suma de los grupos debe coincidir con
    //    los totales generales obtenidos por el segundo procedimiento almacenado.
    $comparaciones = [
        ['Ingresos',    $centIngresos, a_centavos($directo['ingresos']), true],
        ['Gastos',      $centGastos,   a_centavos($directo['gastos']),   true],
        ['Movimientos', $centMovs,     $directo['movimientos'],          false],
    ];

    $verificacion = ['coincide' => true, 'filas' => []];

    foreach ($comparaciones as [$nombre, $sumaGrupos, $totalDirecto, $esDinero]) {
        $ok = $sumaGrupos === $totalDirecto;
        $verificacion['coincide'] = $verificacion['coincide'] && $ok;
        $verificacion['filas'][] = [
            'nombre' => $nombre,
            'grupos' => $esDinero ? $sumaGrupos / 100 : $sumaGrupos,
            'directo' => $esDinero ? $totalDirecto / 100 : $totalDirecto,
            'dinero' => $esDinero,
            'ok'     => $ok,
        ];
    }
}