<?php
session_start();

require_once __DIR__ . '/../../models/passwordResetModel.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = (string) ($_GET['token'] ?? '');
$error = $_GET['error'] ?? '';
$valido = token_recuperacion_valido($token);

$mensajesError = [
    'password'     => 'La contraseña debe tener entre 8 y 72 caracteres.',
    'confirmacion' => 'Las contraseñas no coinciden.',
    'csrf'         => 'La sesión expiró. Vuelve a intentarlo.',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Evita que el token viaje en el encabezado Referer hacia otros sitios. -->
    <meta name="referrer" content="no-referrer">
    <title>Nueva contraseña | Finanzas Personales</title>
    <link rel="stylesheet" href="../../public/css/register.css">
    <link rel="stylesheet" href="../../public/css/login.css">
</head>
<body>
<div class="page">
    <aside class="brand">
        <div class="logo">Finanzas</div>
        <div class="brand-body">
            <h2>Crea una contraseña nueva.</h2>
            <p>Elige una contraseña segura que no uses en otros sitios.</p>
        </div>
    </aside>

    <main class="panel">
        <section class="card">
            <?php if (!$valido): ?>
                <h1>Enlace no válido</h1>
                <p class="sub">El enlace venció, ya se usó o no es correcto.</p>
                <a class="submit" href="forgot-password.php"
                   style="text-align:center;text-decoration:none;display:block">Solicitar un enlace nuevo</a>
                <a class="btn-secondary" href="login.php">Volver al inicio de sesión</a>
            <?php else: ?>
                <h1>Nueva contraseña</h1>
                <p class="sub">Escríbela dos veces para confirmarla.</p>

                <?php if (isset($mensajesError[$error])): ?>
                    <p class="error" role="alert"><?= htmlspecialchars($mensajesError[$error], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <form action="../../controllers/resetPasswordController.php" method="POST">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="field">
                        <label for="password">Contraseña nueva</label>
                        <input id="password" name="password" type="password"
                               autocomplete="new-password" minlength="8" maxlength="72" required>
                    </div>

                    <div class="field">
                        <label for="confirm">Confirmar contraseña</label>
                        <input id="confirm" name="confirm" type="password"
                               autocomplete="new-password" minlength="8" maxlength="72" required>
                    </div>

                    <button type="submit" class="submit">Guardar contraseña</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>