<?php
require_once __DIR__ . '/../models/usersModel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

// Verificar que la petición llegue por el método POST tradicional
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/auth/register.php");
    exit;
}

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
    // Enviar correo de bienvenida
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'] ?? '';
        $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = $_ENV['MAIL_PORT'] === 465
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['MAIL_PORT'] ?? 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? '', $_ENV['MAIL_FROM_NAME'] ?? 'Finanzas Personales');
        $mail->addAddress($email, $nombre);

        $mail->isHTML(true);
        $mail->Subject = 'Bienvenido a Finanzas Personales';

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
                            <h1 style="margin: 0; color: #FFFFFF; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">
                                Finanzas Personales <span style="color: #FBBF24;">.</span>
                            </h1>
                            </td>
                        </tr>

                        <!-- Cuerpo del Correo -->
                        <tr>
                            <td style="padding: 40px 40px 32px 40px;">
                            <h2 style="margin: 0 0 16px 0; color: #1F1B3A; font-size: 20px; font-weight: 700; line-height: 1.3;">
                                ¡Cuenta creada exitosamente! 
                            </h2>
                            
                            <p style="margin: 0 0 16px 0; color: #5B5580; font-size: 15px; line-height: 1.6;">
                                Hola <strong style="color: #1F1B3A;">' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</strong>,
                            </p>
                            
                            <p style="margin: 0 0 24px 0; color: #5B5580; font-size: 15px; line-height: 1.6;">
                                Gracias por registrarte en <strong>Finanzas Personales</strong>. A continuación encontrarás un resumen de tus datos de acceso:
                            </p>

                            <!-- Tarjeta con Credenciales -->
                            <div style="background-color: #F5F3FF; border: 1px solid #E4DFF7; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding-bottom: 10px; color: #5B5580; font-size: 14px;">
                                    <strong>Correo electrónico:</strong>
                                    </td>
                                    <td style="padding-bottom: 10px; color: #1F1B3A; font-size: 14px; font-weight: 600; text-align: right;">
                                    ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: #5B5580; font-size: 14px;">
                                    <strong>Contraseña:</strong>
                                    </td>
                                    <td style="color: #1F1B3A; font-size: 14px; font-weight: 600; text-align: right;">
                                    ' . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . '
                                    </td>
                                </tr>
                                </table>
                            </div>

                            <!-- Caja Informativa de Seguridad -->
                            <div style="background-color: #EDE9FE; border-left: 4px solid #6D28D9; border-radius: 6px; padding: 14px 16px; margin-bottom: 28px;">
                                <p style="margin: 0; color: #5B21B6; font-size: 13px; line-height: 1.5; font-weight: 600;">
                                Recuerda que puedes actualizar tu contraseña en cualquier momento desde los ajustes de tu perfil.
                                </p>
                            </div>

                            <!-- Botón de Acción para Iniciar Sesión -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 12px 0;">
                                <tr>
                                <td align="center">
                                    <a href="http://localhost/finanzas_personales/views/auth/login.php" target="_blank" style="display: inline-block; background-color: #6D28D9; color: #FFFFFF; font-size: 15px; font-weight: 600; text-decoration: none; padding: 14px 28px; border-radius: 8px; box-shadow: 0 4px 12px rgba(109, 40, 217, 0.25); text-align: center;">
                                    Iniciar sesión ahora
                                    </a>
                                </td>
                                </tr>
                            </table>
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

        $mail->AltBody = "Hola " . $nombre . ",\n\n"
            . "Gracias por registrarte en Finanzas Personales. Aquí tienes tus credenciales de acceso:\n\n"
            . "Correo electrónico: " . $email . "\n"
            . "Contraseña: " . $password . "\n\n"
            . "Recuerda que puedes cambiar tu contraseña desde tu perfil.\n\n"
            . "Saludos,\nEl equipo de Finanzas Personales";

        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar el correo: {$mail->ErrorInfo}";
    }
    // Redirigir a la vista de éxito o de inicio de sesión
    header("Location: ../views/auth/register.php?status=exito");
    exit;
} else {
    header("Location: ../views/auth/register.php?error=error_bd");
    exit;
}