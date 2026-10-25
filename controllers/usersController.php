<?php
// controllers/userController.php

// Verificar que la petición llegue por el método POST tradicional
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/auth/register.php");
    exit;
}

require_once __DIR__ . '/../models/usersModel.php';

// Capturar los valores enviados desde $_POST
$nombre   = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm'] ?? '';
$terms    = isset($_POST['terms']);

// 1. Validaciones del lado del servidor
if (empty($nombre) || strlen($nombre) < 2) {
    header("Location: ../views/auth/register.php?error=nombre_invalido");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../views/auth/register.php?error=email_invalido");
    exit;
}

if (strlen($password) < 8 || $password !== $confirm) {
    header("Location: ../views/auth/register.php?error=password_invalida");
    exit;
}

if (!$terms) {
    header("Location: ../views/auth/register.php?error=terminos_no_aceptados");
    exit;
}

// 2. Verificar disponibilidad de correo mediante el modelo
if (obtener_usuario_por_email($email)) {
    header("Location: ../views/auth/register.php?error=email_registrado");
    exit;
}

// 3. Ejecutar el registro en la base de datos
$guardado = crear_usuario($nombre, $email, $password);

if ($guardado) {
    // Redirigir a la vista de éxito o de inicio de sesión
    header("Location: ../views/auth/register.php?status=exito");
    exit;
} else {
    header("Location: ../views/auth/register.php?error=error_bd");
    exit;
}