<?php
// controllers/budgetHistoryController.php
// Solo maneja la sesión y prepara los datos; las consultas están en el modelo.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Comprobar que la persona haya iniciado sesión.
if (
    !isset($_SESSION['user_id']) ||
    !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)
) {
    header('Location: /finanzas_personales/views/auth/login.php?error=sesion');
    exit;
}

require_once __DIR__ . '/../models/budgetHistoryModel.php';

// 2. El id sale SIEMPRE de la sesión, nunca de la URL ni de un formulario.
//    Así cada persona solo puede ver su propio historial.
$idUsuario = (int) $_SESSION['user_id'];

// 3. Datos que usa la vista.
$historial         = obtener_historial_presupuesto($idUsuario);
$presupuestoActual = obtener_presupuesto_actual($idUsuario);

$totalCambios = count($historial);
$ultimoCambio = $historial[0]['fecha_cambio'] ?? null;