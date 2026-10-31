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
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; background-color: #F5F3FF; font-family: \'Plus Jakarta Sans\', Arial, sans-serif; -webkit-font-smoothing: antialiased;">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F5F3FF; padding: 40px 10px;">
        <tr>
          <td align="center">
            <!-- Contenedor Principal (Tarjeta) -->
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 520px; background-color: #FFFFFF; border-radius: 12px; border: 1px solid #E4DFF7; box-shadow: 0 10px 25px -5px rgba(30, 27, 75, 0.05); overflow: hidden;">
              
              <!-- Cabecera / Branding -->
              <tr>
                <td style="background-color: #1E1B4B; padding: 32px 40px; text-align: center; background-image: linear-gradient(135deg, #1E1B4B 0%, #5B21B6 100%);">
                  <h1 style="margin: 0; color: #FFFFFF; font-size: 24px; font-weight: 800; tracking-spacing: -0.5px; display: inline-flex; align-items: center; gap: 8px;">
                    Finanzas<span style="color: #FBBF24;">.</span>
                  </h1>
                </td>
              </tr>

              <!-- Cuerpo del Correo -->
              <tr>
                <td style="padding: 40px 40px 32px 40px;">
                  <h2 style="margin: 0 0 16px 0; color: #1F1B3A; font-size: 20px; font-weight: 700; line-height: 1.3;">
                    Restablecer contraseña
                  </h2>
                  
                  <p style="margin: 0 0 16px 0; color: #5B5580; font-size: 15px; line-height: 1.6;">
                    Hola <strong style="color: #1F1B3A;">' . $nombreSeguro . '</strong>,
                  </p>
                  
                  <p style="margin: 0 0 28px 0; color: #5B5580; font-size: 15px; line-height: 1.6;">
                    Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>Finanzas Personales</strong>. Haz clic en el siguiente botón para continuar:
                  </p>

                  <!-- Botón de Acción -->
                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 28px 0;">
                    <tr>
                      <td align="center">
                        <a href="' . $urlSegura . '" target="_blank" style="display: inline-block; background-color: #6D28D9; color: #FFFFFF; font-size: 15px; font-weight: 600; text-decoration: none; padding: 14px 28px; border-radius: 8px; box-shadow: 0 4px 12px rgba(109, 40, 217, 0.25); text-align: center;">
                          Crear nueva contraseña
                        </a>
                      </td>
                    </tr>
                  </table>

                  <!-- Caja Informativa de Expiración -->
                  <div style="background-color: #EDE9FE; border-left: 4px solid #6D28D9; border-radius: 6px; padding: 14px 16px; margin-bottom: 24px;">
                    <p style="margin: 0; color: #5B21B6; font-size: 13px; line-height: 1.5; font-weight: 600;">
                      ⏱️ Este enlace es válido por 60 minutos y solo puede usarse una vez.
                    </p>
                  </div>

                  <p style="margin: 0; color: #5B5580; font-size: 14px; line-height: 1.5;">
                    Si no solicitaste este cambio, puedes ignorar este mensaje de forma segura. Tu contraseña actual no cambiará.
                  </p>
                </td>
              </tr>

              <!-- Separador -->
              <tr>
                <td style="padding: 0 40px;">
                  <div style="border-top: 1px solid #E4DFF7;"></div>
                </td>
              </tr>

              <!-- Pie de Página -->
              <tr>
                <td style="padding: 24px 40px; background-color: #FFFFFF; text-align: center;">
                  <p style="margin: 0 0 8px 0; color: #5B5580; font-size: 12px; line-height: 1.4;">
                    Este correo fue generado automáticamente por <strong>Finanzas Personales</strong>. Por favor, no respondas a este mensaje.
                  </p>
                  <p style="margin: 0; color: #C4B5FD; font-size: 11px;">
                    &copy; ' . date('Y') . ' Finanzas Personales. Todos los derechos reservados.
                  </p>
                </td>
              </tr>

            </table>
          </td>
        </tr>
      </table>
    </body>
    </html>';

    $mail->AltBody = "Hola " . $nombreSeguro . ",\n\n"
        . "Recibimos una solicitud para restablecer tu contraseña en Finanzas Personales.\n\n"
        . "Para restablecer tu contraseña abre este enlace:\n" . $urlSegura . "\n\n"
        . "Este enlace vence en 60 minutos y solo puede usarse una vez.\n"
        . "Si no solicitaste este cambio, ignora este mensaje: tu contraseña permanecerá sin cambios.";

    $mail->send();
} catch (Throwable $e) {
    // Se registra en el log; la persona siempre ve la respuesta genérica.
    error_log('Error en recuperación de contraseña: ' . $e->getMessage());
}

header('Location: ' . $destino);
exit;
