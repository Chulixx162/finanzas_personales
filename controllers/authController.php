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
    // Credenciales correctas: Iniciar sesión
    $_SESSION['user_id']     = $usuario['id'];
    $_SESSION['user_nombre'] = $usuario['nombre'];
    $_SESSION['user_email']  = $usuario['email'];

    // Redirigir al dashboard/resumen
    // ===================================================CORREGIR==================================
    header("Location: ../views/users/dashboard.php");
    exit;
} else {
    // Credenciales incorrectas
    header("Location: ../views/auth/login.php?error=credenciales");
    exit;
}