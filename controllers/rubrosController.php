<?php
// controllers/rubrosController.php
// Solo maneja la sesión y las rutas; las consultas están en el modelo.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['user_id']) ||
    !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)
) {
    header('Location: /finanzas_personales/views/auth/login.php?error=sesion');
    exit;
}

require_once __DIR__ . '/../models/rubrosModel.php';

$idUsuario = (int) $_SESSION['user_id'];
$action    = $_GET['action'] ?? 'index';

switch ($action) {
    default: // index: consultar y listar rubros
        $rubros = obtener_rubros_por_usuario($idUsuario);
        break;
}