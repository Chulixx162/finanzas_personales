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
    <style>
        .notice { background:#ede9fe; color:#3b1d8f; border-radius:8px; padding:12px 14px; margin:0 0 16px; }
    </style>
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
                <p class="notice" role="status">
                    Si el correo está registrado, recibirás un enlace en unos minutos.
                    Revisa también la carpeta de spam.
                </p>
            <?php endif; ?>

            <?php if (!empty($_SESSION['dev_enlace'])): ?>
                <p class="notice">
                    <strong>DA CLICK ACÁ:</strong>
                    <a href="<?= htmlspecialchars($_SESSION['dev_enlace'], ENT_QUOTES, 'UTF-8') ?>">abrir enlace de recuperación</a>
                </p>
                <?php unset($_SESSION['dev_enlace']); ?>
            <?php endif; ?>

            <?php if (isset($mensajesError[$error])): ?>
                <p class="error" role="alert"><?= htmlspecialchars($mensajesError[$error], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="../../controllers/forgotPasswordController.php" method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" autocomplete="email" maxlength="100" required>
                </div>

                <button type="submit" class="submit">Enviar enlace de recuperación</button>

                <a class="btn-secondary" href="login.php">Volver al inicio de sesión</a>
            </form>
        </section>
    </main>
</div>
</body>
</html>