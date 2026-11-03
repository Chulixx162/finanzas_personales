<?php
// controllers/logoutController.php

// Iniciar o reanudar la sesión existente
session_start();

// Validar que la petición sea estrictamente por método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si intentan acceder directamente por URL (GET), redirigir al panel o login
    header("Location: ../views/dashboard.php");
    exit;
}

// 1. Limpiar todas las variables de la sesión superglobal
$_SESSION = [];

// 2. Si se desea destruir la cookie de sesión del navegador (seguridad adicional)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destruir la sesión completamente en el servidor
session_destroy();

// 4. Redirigir al formulario de inicio de sesión
header("Location: ../views/auth/login.php");
exit;