<?php
// controllers/logoutController.php
// Cierra la sesión y lleva al usuario a la página de inicio.

require_once __DIR__ . '/../config/session.php';
iniciar_sesion_segura();

$raiz = ruta_a_raiz();

// Solo se cierra sesión por POST (el botón "Cerrar sesión" del navbar).
// Si alguien entra escribiendo la URL (GET) no se cierra nada.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $destino = isset($_SESSION['user_id'])
        ? 'views/users/dashboard.php'
        : 'public/index.php';

    header('Location: ' . $raiz . $destino);
    exit;
}

// 1. Borrar variables, cookie y archivo de sesión.
destruir_sesion();

// 2. Que el navegador no conserve ninguna página privada en su caché
//    (así el botón "atrás" no puede mostrar la cuenta).
enviar_cabeceras_privadas();

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');

if ($https && !headers_sent()) {
    // Vacía la caché del sitio en los navegadores que lo soportan (solo HTTPS).
    header('Clear-Site-Data: "cache"');
}

// 3. Volver a la página de inicio.
header('Location: ' . $raiz . 'public/index.php');
exit;