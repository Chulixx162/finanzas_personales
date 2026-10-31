<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_GET['error'] ?? '';
$status = $_GET['status'] ?? '';

$mensajesError = [
    'email'  => 'Introduce un correo electrónico válido.',
    'csrf'   => 'La sesión expiró. Vuelve a intentarlo.',
    'enlace' => 'El enlace no es válido o ya venció. Solicita uno nuevo.',
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar contraseña | Finanzas Personales</title>
    <link rel="stylesheet" href="../../public/css/register.css">
    <link rel="stylesheet" href="../../public/css/login.css">
    <link rel="stylesheet" href="../../public/css/alerts.css">
    <link rel="icon" type="image/x-icon" href="../../public/img/icon.webp">
</head>

<body>
    <div class="page">
        <aside class="brand">
            <div class="logo">Finanzas</div>
            <div class="brand-body">
                <h2>Recupera el acceso a tus finanzas.</h2>
                <p>Te enviaremos un enlace seguro a tu correo.</p>
            </div>
        </aside>

        <main class="panel">
            <section class="card">
                <h1>¿Olvidaste tu contraseña?</h1>
                <p class="sub">Escribe el correo asociado a tu cuenta.</p>

                <?php if ($status === 'solicitado'): ?>
                    <p class="notice notice-success" role="status">
                        Si el correo está registrado, recibirás un enlace en unos minutos.
                        Revisa también la carpeta de spam.
                    </p>
                <?php endif; ?>


                <?php if (isset($mensajesError[$error])): ?>
                    <p class="error" role="alert"><?= htmlspecialchars($mensajesError[$error], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($status !== 'solicitado'): ?>
                    <form action="../../controllers/forgotPasswordController.php" method="POST">
                        <input type="hidden" name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                        <div class="field">
                            <label for="email">Correo electrónico</label>
                            <input id="email" name="email" type="email" autocomplete="email" maxlength="100" required>
                        </div>

                        <button type="submit" class="submit">Enviar enlace de recuperación</button>


                    </form>
                <?php endif; ?>
                <a class="btn-secondary" href="login.php">Volver al inicio de sesión</a>
            </section>
        </main>
    </div>
</body>

</html>