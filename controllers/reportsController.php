<?php
// controllers/reportsController.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = '/finanzas_personales/';

if (!isset($_SESSION['user_id'])) {
    header("Location: /finanzas_personales/views/auth/login.php");
    exit();
}

require_once __DIR__ . '/../config/connection.php';
/** @var mysqli $conn */
require_once __DIR__ . '/../models/reportsModel.php';

$user_id = $_SESSION['user_id'];
$action  = $_GET['action'] ?? 'index';

switch ($action) {
    case 'export_csv':
        $movimientos = obtenerMovimientosParaReporte($conn, $user_id);

        // Cabeceras HTTP para forzar la descarga de un archivo CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_movimientos_' . date('Y-m-d') . '.csv');

        // Abrir salida directa de PHP
        $output = fopen('php://output', 'w');

        // BOM para asegurar compatibilidad de tildes y caracteres en Microsoft Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeceras de las columnas (separadas por punto y coma)
        fputcsv($output, ['ID Movimiento', 'Tipo', 'Monto ($)', 'Fecha', 'Rubro', 'Categoría', 'Descripción'], ';');

        // Recorrer los resultados e insertarlos en el CSV
        foreach ($movimientos as $m) {
            fputcsv($output, [
                $m['id_movimiento'],
                ucfirst($m['tipo']),
                $m['monto'],
                $m['fecha'],
                $m['rubro_nombre'],
                $m['categoria_nombre'],
                $m['descripcion']
            ], ';');
        }

        fclose($output);
        exit(); // Detener ejecución para que no se imprima HTML en el archivo
        break;

    default: // 'index'
        break;
}
?>