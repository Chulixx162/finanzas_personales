<?php
// controllers/movimientosController.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sesión y guardia de ruta privada (igual que el resto de controladores).
require_once __DIR__ . '/../config/session.php';

$user_id = requerir_login();

$base = '/finanzas_personales/';

require_once __DIR__ . '/../config/connection.php';
/** @var mysqli $conn */
require_once __DIR__ . '/../models/movimientosModel.php';
require_once __DIR__ . '/../models/budgetHistoryModel.php';

$action    = $_GET['action'] ?? 'index';
$MAX_MONTO = 9999999999.99; // Límite para DECIMAL(12,2)

switch ($action) {
    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rubro    = $_POST['id_rubro'] ?? null;
            $tipo        = $_POST['tipo'] ?? '';
            $monto       = (float) ($_POST['monto'] ?? 0);
            $fecha       = $_POST['fecha'] ?? date('Y-m-d H:i:s');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $creado      = false;

            if ($id_rubro && in_array($tipo, ['ingreso', 'gasto']) && $monto > 0 && $monto <= $MAX_MONTO) {
                $creado = crearMovimiento($conn, $user_id, $id_rubro, $tipo, $monto, $fecha, $descripcion);
            }

            $destino = $creado
                ? 'success=created'
                : 'error=' . urlencode('No se pudo registrar el movimiento. Revisa los datos.');

            // Los gastos sí pueden superar el presupuesto mensual: se registran
            // igual y se avisa para que aparezcan la alerta y el mensaje.
            if ($creado && $tipo === 'gasto' && substr((string) $fecha, 0, 7) === date('Y-m')) {
                $presupuesto = obtener_presupuesto_actual($user_id);

                if ($presupuesto !== null && $presupuesto > 0) {
                    $gastado = array_sum(array_column(obtener_uso_presupuesto($user_id), 'gastado'));

                    if ($gastado > $presupuesto) {
                        $destino .= '&excede=1&gastado=' . (int) round($gastado)
                                  . '&presupuesto=' . (int) round($presupuesto);
                    }
                }
            }

            header("Location: /finanzas_personales/views/movimientos/index.php?" . $destino);
            exit();
        }
        break;

    case 'create':
        $rubros = obtenerRubrosParaSelect($conn, $user_id);
        break;

    default: // 'index'
        $movimientos = obtenerMovimientosPorUsuario($conn, $user_id);
        break;
}
?>