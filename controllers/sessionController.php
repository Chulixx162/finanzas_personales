<?php
// controllers/sessionController.php
// Endpoint JSON que usa public/js/session.js para controlar la inactividad.
//
//   GET  ?action=status     -> consulta cuánto le queda a la sesión (NO la renueva)
//   POST ?action=keepalive  -> avisa que el usuario sigue activo (renueva el contador)

require_once __DIR__ . '/../config/session.php';
iniciar_sesion_segura();

header('Content-Type: application/json; charset=utf-8');
enviar_cabeceras_privadas();

function responder_sesion(array $datos, int $estado = 200): void
{
    http_response_code($estado);
    echo json_encode($datos);
    exit;
}

$accion = $_GET['action'] ?? 'status';

// Sin sesión válida (cerrada en otra pestaña o expirada por inactividad).
if (segundos_restantes_sesion() <= 0 || !filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT)) {
    $motivo = !empty($_SESSION['sesion_expirada']) ? 'expirada' : 'cerrada';
    unset($_SESSION['sesion_expirada']);

    responder_sesion(['activa' => false, 'motivo' => $motivo, 'restante' => 0], 401);
}

if ($accion === 'keepalive') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder_sesion(['activa' => true, 'error' => 'metodo'], 405);
    }

    registrar_actividad();
}

responder_sesion([
    'activa'   => true,
    'restante' => segundos_restantes_sesion(),
]);