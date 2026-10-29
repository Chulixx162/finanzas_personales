<?php
// views/users/profile.php
// Perfil del usuario: editar datos de la cuenta y eliminarla.
$base   = '../../';   // desde views/users/ hasta la raíz del proyecto
$active = '';         // el perfil no está en el menú principal

// La vista se abre primero y llama al controlador: este valida la sesión
// (redirige al login si hace falta) y deja listos $usuario y $csrf.
require_once __DIR__ . '/../../controllers/profileController.php';

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

$nombre  = $usuario['nombre'];
$correo  = $usuario['correo'];
$inicial = $nombre !== '' ? mb_strtoupper(mb_substr($nombre, 0, 1)) : '?';
$mensaje = is_string($_GET['mensaje'] ?? null) ? $_GET['mensaje'] : '';
$error   = is_string($_GET['error'] ?? null) ? $_GET['error'] : '';

$textoExito = 'Tu perfil se actualizó correctamente.';

$errores = [
    'datos'         => 'Verifica los datos ingresados.',
    'correo'        => 'Ese correo ya está registrado o no es válido.',
    'contrasena'    => 'La contraseña actual es incorrecta.',
    'actualizacion' => 'No se pudo actualizar el perfil.',
    'eliminacion'   => 'No se pudo eliminar la cuenta.',
    'csrf'          => 'La solicitud no es válida. Recarga la página.',
    'sesion'        => 'Tu sesión expiró. Inicia sesión de nuevo.',
    'red'           => 'No se pudo conectar con el servidor. Inténtalo de nuevo.',
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi perfil · Finanzas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>public/css/layout.css">
    <link rel="stylesheet" href="<?= $base ?>public/css/profile.css">
    <link rel="stylesheet" href="<?= $base ?>public/css/alerts.css">
    <link rel="icon" type="image/x-icon" href="<?= $base ?>public/img/icon.webp">
</head>

<body class="layout">

    <?php require __DIR__ . '/../layouts/navbar.php'; ?>

    <main class="profile">
        <div class="profile-wrap">

            <header class="profile-head">
                <h1>Mi perfil</h1>
                <p class="profile-sub">Consulta y administra los datos de tu cuenta.</p>
            </header>

            <?php if ($mensaje === 'actualizado'): ?>
                <p class="alert alert-success" role="status"><?= $h($textoExito) ?></p>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <p class="alert alert-error" role="alert">
                    <?= $h($errores[$error] ?? 'Ocurrió un error.') ?>
                </p>
            <?php endif; ?>

            <div class="profile-grid">

                <!-- Resumen de la cuenta -->
                <aside class="profile-id" aria-label="Resumen de la cuenta">
                    <div class="profile-id-main">
                        <span class="profile-avatar" aria-hidden="true"><?= $h($inicial) ?></span>
                        <div>
                            <p class="profile-id-name"><?= $h($nombre) ?></p>
                            <p class="profile-id-mail"><?= $h($correo) ?></p>
                        </div>
                    </div>
                    <p class="profile-id-note">Tus datos son privados y tu contraseña se guarda cifrada.</p>
                </aside>

                <div class="profile-stack">

                    <!-- Datos de la cuenta -->
                    <section class="profile-card" aria-labelledby="titulo-datos">
                        <header class="card-head">
                            <h2 id="titulo-datos">Datos de la cuenta</h2>
                            <p>Por seguridad, confirma tu contraseña actual para guardar cambios.</p>
                        </header>

                        <form class="profile-form" id="form-profile" action="../../controllers/profileController.php" method="POST"
                            data-login="<?= $h($base) ?>views/auth/login.php">
                            <input type="hidden" name="csrf_token" value="<?= $h($csrf) ?>">
                            <input type="hidden" name="accion" value="actualizar">

                            <div class="form-grid">
                                <div class="field">
                                    <label for="nombre">Nombre</label>
                                    <input id="nombre" name="nombre" type="text" minlength="2" maxlength="50" required
                                        value="<?= $h($nombre) ?>">
                                </div>

                                <div class="field">
                                    <label for="correo">Correo electrónico</label>
                                    <input id="correo" name="correo" type="email" maxlength="100" required
                                        value="<?= $h($correo) ?>">
                                </div>

                                <div class="field">
                                    <label for="actual">Contraseña actual</label>
                                    <div class="input-wrap">
                                        <input id="actual" name="contrasena_actual" type="password" required
                                            autocomplete="current-password">
                                        <button type="button" class="toggle" data-target="actual"
                                            aria-label="Mostrar contraseña actual">Mostrar</button>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="nueva">Nueva contraseña (opcional)</label>
                                    <div class="input-wrap">
                                        <input id="nueva" name="contrasena_nueva" type="password" minlength="8"
                                            autocomplete="new-password" aria-describedby="ayuda-nueva">
                                        <button type="button" class="toggle" data-target="nueva"
                                            aria-label="Mostrar nueva contraseña">Mostrar</button>
                                    </div>
                                    <p class="hint" id="ayuda-nueva">Mínimo 8 caracteres. Déjala vacía para conservar la actual.</p>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button class="btn btn-primary" type="submit">Guardar cambios</button>
                            </div>
                        </form>
                    </section>

                    <!-- Eliminar cuenta -->
                    <section class="profile-card danger-card" aria-labelledby="titulo-eliminar">
                        <header class="card-head">
                            <h2 id="titulo-eliminar">Eliminar cuenta</h2>
                            <p>Esta acción puede borrar permanentemente tus datos y no se puede deshacer.</p>
                        </header>

                        <form class="profile-form" id="form-eliminar" action="../../controllers/profileController.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $h($csrf) ?>">
                            <input type="hidden" name="accion" value="eliminar">

                            <div class="field">
                                <label for="confirmar">Confirma tu contraseña</label>
                                <div class="input-wrap">
                                    <input id="confirmar" name="contrasena_actual" type="password" required
                                        autocomplete="current-password">
                                    <button type="button" class="toggle" data-target="confirmar"
                                        aria-label="Mostrar contraseña de confirmación">Mostrar</button>
                                </div>
                            </div>

                            <label class="check">
                                <input type="checkbox" name="confirmar_eliminacion" value="si" required>
                                Entiendo que deseo eliminar mi cuenta.
                            </label>

                            <div class="form-actions">
                                <button class="btn btn-danger" type="submit">Eliminar mi cuenta</button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

        </div>
    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

    <!-- Avisos de la página (el JS escribe aquí) -->
    <div class="toast-region" id="toast-region" aria-live="polite"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base ?>public/js/alerts.js" defer></script>
    <script type="application/json" id="profile-mensajes"><?= json_encode(
        ['exito' => $textoExito, 'errores' => $errores],
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
    ) ?></script>
    <script src="<?= $base ?>public/js/profile.js" defer></script>
</body>

</html>