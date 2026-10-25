<?php
// login.php (parte superior)
$error = $_GET['error'] ?? '';

$error_messages = [
    'email_invalido' => ['field' => 'email', 'msg' => 'Formato de correo no válido.'],
    'credenciales'   => ['field' => '', 'msg' => 'El correo o la contraseña no son correctos.'],
    'campos_vacios'  => ['field' => '', 'msg' => 'Por favor, completa todos los campos.']
];

$server_error_field = $error_messages[$error]['field'] ?? '';
$server_error_msg   = $error_messages[$error]['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · Finanzas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/register.css">
    <link rel="stylesheet" href="../../public/css/login.css">
    <link rel="icon" type="image/x-icon" href="../../public/img/icon.webp">
</head>

<body>
    <div class="page">
        <aside class="brand">
            <div class="logo"><span class="logo-mark" aria-hidden="true"></i></span>Finanzas</div>
            <div class="brand-body">
                <h2>Tu dinero, claro y bajo control.</h2>
                <p>Entra para ver tu saldo, tus movimientos del mes y el avance de tus metas.</p>
                <div class="goal" aria-hidden="true">
                    <div class="goal-row"><span>Meta: Fondo de emergencia</span><span>30%</span></div>
                    <div class="goal-track">
                        <div class="goal-fill" style="width:30%"></div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="panel">
            <div class="card">
                <form id="login-form" action="../../controllers/authController.php" method="POST" novalidate>
                    <h1>Inicia sesión</h1>
                    <p class="sub">Qué bueno verte de nuevo.</p>

                    <p class="form-error" id="form-error" role="alert" hidden></p>

                    <div class="field">
                        <label for="email">Correo electrónico</label>
                        <input id="email" name="email" type="email" autocomplete="email" required>
                        <p class="error" id="email-error" role="alert"></p>
                    </div>

                    <div class="field">
                        <label for="password">Contraseña</label>
                        <div class="input-wrap">
                            <input id="password" name="password" type="password" autocomplete="current-password" required>
                            <button type="button" class="toggle" data-target="password" aria-label="Mostrar contraseña">Mostrar</button>
                        </div>
                        <p class="error" id="password-error" role="alert"></p>
                    </div>

                    <div class="row">
                        <label class="check" for="remember">
                            <input id="remember" name="remember" type="checkbox">
                            <span>Recordarme</span>
                        </label>
                        <a href="#">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="submit" id="submit">Iniciar sesión</button>
                    <p class="login">¿Aún no tienes cuenta? <a href="register.php">Crear cuenta</a></p>
                </form>
            </div>
        </main>
    </div>
    <script>
        window.SERVER_DATA = {
            errorField: "<?php echo $server_error_field; ?>",
            errorMessage: "<?php echo $server_error_msg; ?>"
        };
    </script>
    <script src="../../public/js/login.js"></script>
</body>

</html>