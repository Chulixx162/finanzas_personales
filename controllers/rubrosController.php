<?php
// controllers/rubrosController.php
// Solo maneja la sesión y las rutas; las consultas están en el modelo.

require_once __DIR__ . '/../config/session.php';

$idUsuario = requerir_login();

require_once __DIR__ . '/../models/rubrosModel.php';

$action    = $_GET['action'] ?? 'index';

switch ($action) {
    default: // index: consultar y listar rubros
        $rubros = obtener_rubros_por_usuario($idUsuario);
        break;
}