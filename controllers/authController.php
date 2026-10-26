<?php
// controllers/authController.php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/auth/login.php");
    exit;
}

require_once __DIR__ . '/../models/usersModel.php';

$email    = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

// 1. Validaciones del lado del servidor
if (empty($email) || empty($password)) {
    header("Location: ../views/auth/login.php?error=campos_vacios");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../views/auth/login.php?error=email_invalido");
    exit;
}

// 2. Buscar al usuario en la BD mediante el modelo
$usuario = obtener_usuario_por_email($email);

// 3. Verificar contraseña cifrada

if ($usuario && password_verify($password, $usuario['contrasena'])) {

    // Renovar el identificador de sesión.
    session_regenerate_id(true);

    // Guardar los datos del usuario autenticado.
    $_SESSION['user_id'] = $usuario['id_usuario'];
    $_SESSION['user_nombre'] = $usuario['nombre'];
    $_SESSION['user_email'] = $usuario['correo'];

    // Abrir el Dashboard mediante su controlador.
    header('Location: dashboardController.php');
    exit;

} else {
    header('Location: ../views/auth/login.php?error=credenciales');
    exit;
}
