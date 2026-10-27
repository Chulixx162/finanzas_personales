<?php
// controllers/resetPasswordController.php
// Paso 2: la persona llega desde el correo y escribe la contraseña nueva.

session_start();

require_once __DIR__ . '/../models/passwordResetModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/auth/forgot-password.php');
    exit;
}

$token = (string) ($_POST['token'] ?? '');
$nueva = (string) ($_POST['password'] ?? '');
$confirm = (string) ($_POST['confirm'] ?? '');

if (!token_recuperacion_tiene_formato($token)) {
    header('Location: ../views/auth/forgot-password.php?error=enlace');
    exit;
}

$volver = '../views/auth/reset-password.php?token=' . $token;

// Protección CSRF.
$tokenSesion = $_SESSION['csrf_token'] ?? '';
$tokenForm = $_POST['csrf_token'] ?? '';

if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenForm)) {
    header('Location: ' . $volver . '&error=csrf');
    exit;
}

// Mismas reglas que el registro (mínimo 8). bcrypt solo usa los primeros 72 bytes.
if (strlen($nueva) < 8 || strlen($nueva) > 72) {
    header('Location: ' . $volver . '&error=password');
    exit;
}

if ($nueva !== $confirm) {
    header('Location: ' . $volver . '&error=confirmacion');
    exit;
}

if (!restablecer_contrasena_con_token($token, $nueva)) {
    // Token vencido, ya usado o inexistente.
    header('Location: ../views/auth/forgot-password.php?error=enlace');
    exit;
}

// Cierra cualquier sesión abierta y renueva el token CSRF.
unset($_SESSION['csrf_token'], $_SESSION['user_id'], $_SESSION['user_nombre'], $_SESSION['user_email']);
session_regenerate_id(true);

header('Location: ../views/auth/login.php?status=restablecido');
exit;