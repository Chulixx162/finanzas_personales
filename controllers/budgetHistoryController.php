<?php
// controllers/budgetHistoryController.php
// Solo maneja la sesión y prepara los datos; las consultas están en el modelo.

require_once __DIR__ . '/../config/session.php';

// 1. Comprobar que la persona haya iniciado sesión.
$idUsuario = requerir_login();

require_once __DIR__ . '/../models/budgetHistoryModel.php';

// 2. El id sale SIEMPRE de la sesión, nunca de la URL ni de un formulario.
//    Así cada persona solo puede ver su propio historial.
//    ($idUsuario ya lo devolvió requerir_login() arriba.)

// 3. Datos que usa la vista.
$historial         = obtener_historial_presupuesto($idUsuario);
$presupuestoActual = obtener_presupuesto_actual($idUsuario);

$totalCambios = count($historial);
$ultimoCambio = $historial[0]['fecha_cambio'] ?? null;