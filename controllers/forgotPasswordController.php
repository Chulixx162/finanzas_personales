<?php
// controllers/forgotPasswordController.php
// Paso 1: la persona escribe su correo y se le envía el enlace.

session_start();

require_once __DIR__ . '/../models/usersModel.php';
require_once __DIR__ . '/../models/passwordResetModel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/auth/forgot-password.php');
    exit;
}

// Protección CSRF (el formulario envía el token de la sesión).
$tokenSesion = $_SESSION['csrf_token'] ?? '';
$tokenForm = $_POST['csrf_token'] ?? '';

if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenForm)) {
    header('Location: ../views/auth/forgot-password.php?error=csrf');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../views/auth/forgot-password.php?error=email');
    exit;
}

// Respuesta idéntica exista o no el correo (no revela qué correos están registrados).
$destino = '../views/auth/forgot-password.php?status=solicitado';

$usuario = obtener_usuario_por_email($email);

if (!$usuario || empty($usuario['id_usuario'])) {
    header('Location: ' . $destino);
    exit;
}

try {
    // connection.php ya cargó el .env en $_ENV.
    $appUrl   = rtrim($_ENV['APP_URL'] ?? '', '/');
    $appEnv   = $_ENV['APP_ENV'] ?? 'production';
    $mailHost = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    $mailPort = (int) ($_ENV['MAIL_PORT'] ?? 587);
    $mailUser = $_ENV['MAIL_USERNAME'] ?? '';
    $mailPass = $_ENV['MAIL_PASSWORD'] ?? '';
    $from     = $_ENV['MAIL_FROM_ADDRESS'] ?? $mailUser;
    $fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Finanzas Personales';

    if ($appUrl === '') {
        error_log('Recuperación: falta APP_URL en .env.');
        header('Location: ' . $destino);
        exit;
    }

    // El correo solo se envía si MAIL_* está completo y PHPMailer está instalado.
    $correoListo = $mailUser !== ''
        && $mailPass !== ''
        && filter_var($from, FILTER_VALIDATE_EMAIL)
        && class_exists(PHPMailer::class);

    // Token aleatorio: se envía por correo; en la BD solo se guarda su hash.
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    // La expiración (60 min) se calcula dentro de MySQL.
    if (!crear_token_recuperacion((int) $usuario['id_usuario'], $tokenHash, 60)) {
        error_log('Recuperación: no se creó el token (error o límite por hora).');
        header('Location: ' . $destino);
        exit;
    }

    $url = $appUrl . '/views/auth/reset-password.php?token=' . $token;

    // Solo en desarrollo: deja el enlace en el log y lo muestra en pantalla.
    if ($appEnv === 'development') {
        error_log('[DEV] Enlace de recuperación: ' . $url);
        $_SESSION['dev_enlace'] = $url;
    }

    if (!$correoListo) {
        error_log('Recuperación: MAIL_* vacío o PHPMailer sin instalar; no se envió correo.');
        header('Location: ' . $destino);
        exit;
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $mailHost;
    $mail->SMTPAuth = true;
    $mail->Username = $mailUser;
    $mail->Password = $mailPass;
    $mail->SMTPSecure = $mailPort === 465
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $mailPort;
    $mail->Timeout = 15;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($from, $fromName);
    $mail->addAddress($email, $usuario['nombre'] ?? '');

    $urlSegura = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $nombreSeguro = htmlspecialchars($usuario['nombre'] ?? '', ENT_QUOTES, 'UTF-8');

    $mail->isHTML(true);
    $mail->Subject = 'Restablece tu contraseña - Finanzas Personales';
    $mail->Body = '
        <div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#1f1b3a">
            <h2 style="color:#6D28D9">Recuperar contraseña</h2>
            <p>Hola ' . $nombreSeguro . ',</p>
            <p>Recibimos una solicitud para restablecer tu contraseña de Finanzas Personales.</p>
            <p>
                <a href="' . $urlSegura . '"
                   style="display:inline-block;background:#6D28D9;color:#ffffff;
                          padding:13px 20px;border-radius:8px;text-decoration:none">
                    Crear nueva contraseña
                </a>
            </p>
            <p>Este enlace vence en 60 minutos y solo puede usarse una vez.</p>
            <p>Si no solicitaste este cambio, ignora este mensaje: tu contraseña no cambiará.</p>
            <p>Finanzas Personales</p>
        </div>';
    $mail->AltBody = "Para restablecer tu contraseña abre este enlace:\n" . $url
        . "\nVence en 60 minutos y solo puede usarse una vez."
        . "\nSi no lo solicitaste, ignora este correo.";

    $mail->send();
} catch (Throwable $e) {
    // Se registra en el log; la persona siempre ve la respuesta genérica.
    error_log('Error en recuperación de contraseña: ' . $e->getMessage());
}

header('Location: ' . $destino);
exit;