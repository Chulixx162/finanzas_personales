<?php
$error  = $_GET['error'] ?? '';
$status = $_GET['status'] ?? '';

$error_messages = [
    'nombre_invalido'       => ['field' => 'name', 'msg' => 'Escribe un nombre válido.'],
    'email_invalido'        => ['field' => 'email', 'msg' => 'El correo electrónico no es válido.'],
    'password_invalida'     => ['field' => 'password', 'msg' => 'La contraseña debe tener al menos 8 caracteres.'],
    'terminos_no_aceptados' => ['field' => 'terms', 'msg' => 'Debes aceptar los términos para continuar.'],
    'email_registrado'      => ['field' => 'email', 'msg' => 'Este correo ya está registrado.'],
    'error_bd'              => ['field' => '', 'msg' => 'Ocurrió un error en el servidor. Inténtalo de nuevo.']
];

$server_error_field = '';
$server_error_msg   = '';

if (!empty($error) && isset($error_messages[$error])) {
    $server_error_field = $error_messages[$error]['field'];
    $server_error_msg   = $error_messages[$error]['msg'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta · Finanzas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/register.css">
    <link rel="icon" type="image/x-icon" href="../../public/img/icon.webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="page">
        <aside class="brand">

            <div class="logo">
                <span class="logo-mark" aria-hidden="true">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                        <rect x="4" y="12" width="4" height="8" rx="1" fill="#fff" />
                        <rect x="10" y="8" width="4" height="12" rx="1" fill="#fff" />
                        <rect x="16" y="4" width="4" height="16" rx="1" fill="#FBBF24" />
                    </svg>
                </span>
                Finanzas
            </div>

            <div class="brand-body">
                <h2>Ordena tu dinero sin complicarte.</h2>
                <p>Registra tus movimientos, define metas y mira cuánto te falta para alcanzarlas.</p>
                <div class="goal" aria-hidden="true">
                    <div class="goal-row"><span>Meta: Viaje</span><span>62%</span></div>
                    <div class="goal-track">
                        <div class="goal-fill"></div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="panel">
            <div class="card">
                <form id="register-form" action="../../controllers/usersController.php" method="POST" novalidate>
                    <h1>Crea tu cuenta</h1>
                    <p class="sub">Empieza gratis. Solo toma un minuto.</p>

                    <div class="field">
                        <label for="name">Nombre completo</label>
                        <input id="name" name="name" type="text" autocomplete="name" required>
                        <p class="error" id="name-error" role="alert"></p>
                    </div>

                    <div class="field">
                        <label for="email">Correo electrónico</label>
                        <input id="email" name="email" type="email" autocomplete="email" required>
                        <p class="error" id="email-error" role="alert"></p>
                    </div>

                    <div class="field">
                        <label for="password">Contraseña</label>
                        <div class="input-wrap">
                            <input id="password" name="password" type="password" autocomplete="new-password" aria-describedby="strength-text" required>
                            <button type="button" class="toggle" data-target="password" aria-label="Mostrar contraseña">Mostrar</button>
                        </div>
                        <div class="strength" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                        <p class="hint" id="strength-text">Mínimo 8 caracteres, con letras y números.</p>
                        <p class="error" id="password-error" role="alert"></p>
                    </div>

                    <div class="field">
                        <label for="confirm">Confirmar contraseña</label>
                        <div class="input-wrap">
                            <input id="confirm" name="confirm" type="password" autocomplete="new-password" required>
                            <button type="button" class="toggle" data-target="confirm" aria-label="Mostrar confirmación de contraseña">Mostrar</button>
                        </div>
                        <p class="error" id="confirm-error" role="alert"></p>
                    </div>

                    <div class="field">
                        <label class="check" for="terms">
                            <input id="terms" name="terms" type="checkbox" required>
                            <span>Acepto los
                                <a href="../legal/terms_of_use.php" target="_blank">términos de uso</a>
                                y la
                            
                                <a href="../legal/privacy_policies.php" target="_blank" >política de privacidad</a>.
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="submit">Crear cuenta</button>

                    <a class="btn-secondary" href="../../public/index.php">Volver al inicio</a>

                    <p class="login">¿Ya tienes cuenta? <a href="./login.php">Inicia sesión</a></p>
                </form>

                <div id="success" class="success" hidden tabindex="-1">
                    <div class="success-icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1>Cuenta creada</h1>
                    <p class="sub">Ya puedes empezar a registrar tus movimientos.</p>
                    <a class="submit link-btn" href="login.php">Iniciar Sesión</a>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Variables enviadas desde PHP al cliente
        window.SERVER_DATA = {
            status: "<?php echo $status; ?>",
            errorField: "<?php echo $server_error_field; ?>",
            errorMessage: "<?php echo $server_error_msg; ?>"
        };
    </script>
    <script src="../../public/js/register.js"></script>
</body>

</html>