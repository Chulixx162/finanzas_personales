<?php
// views/users/profile.php
// Perfil del usuario: editar datos de la cuenta y eliminarla.
$base = '../../';  // desde views/users/ hasta la raíz del proyecto
$active = 'perfil';  // el perfil no está en el menú principal, pero se marca en el chip del usuario

// La vista se abre primero y llama al controlador: este valida la sesión
// (redirige al login si hace falta) y deja listos $usuario y $csrf.
require_once __DIR__ . '/../../controllers/profileController.php';

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

$nombre = $usuario['nombre'] ?? '';
$correo = $usuario['correo'] ?? '';
$inicial = $nombre !== '' ? mb_strtoupper(mb_substr($nombre, 0, 1)) : '?';
$mensaje = is_string($_GET['mensaje'] ?? null) ? $_GET['mensaje'] : '';
$error = is_string($_GET['error'] ?? null) ? $_GET['error'] : '';

$textoExito = 'Tu perfil se actualizó correctamente.';

// Íconos de las tarjetas de documentos, según la extensión del archivo.
$svgBase = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">';
$iconos = [
    'DOCX' => $svgBase . '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>',
    'PDF'  => $svgBase . '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>',
    'XLSX' => $svgBase . '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="16" y2="17"></line><line x1="12" y1="11" x2="12" y2="19"></line></svg>',
    'SQL'  => $svgBase . '<ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>',
];

$errores = [
    'datos' => 'Verifica los datos ingresados.',
    'correo' => 'Ese correo ya está registrado o no es válido.',
    'contrasena' => 'La contraseña actual es incorrecta.',
    'actualizacion' => 'No se pudo actualizar el perfil.',
    'eliminacion' => 'No se pudo eliminar la cuenta.',
    'csrf' => 'La solicitud no es válida. Recarga la página.',
    'sesion' => 'Tu sesión expiró. Inicia sesión de nuevo.',
    'red' => 'No se pudo conectar con el servidor. Inténtalo de nuevo.',
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
    <link rel="stylesheet" href="<?= $base ?>public/css/docs.css">
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
                            <input type="hidden" name="csrf_token" value="<?= $h($csrf ?? '') ?>">
                            <input type="hidden" name="accion" value="actualizar">

                            <div class="form-grid">
                                <div class="field">
                                    <label for="nombre">Nombre</label>
                                    <input id="nombre" name="nombre" type="text" minlength="2" maxlength="50" required
                                        value="<?= $h($nombre) ?>">
                                </div>

                                <div class="field">
                                    <label for="correo">Correo electrónico</label>
                                    <input id="correo" name="correo" type="email" maxlength="100"
                                        value="<?= $h($correo) ?>" readonly aria-readonly="true"
                                        aria-describedby="correo-ayuda" tabindex="-1">
                                    <p class="hint" id="correo-ayuda">El correo no se puede cambiar.</p>
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

                    <!-- Manuales y anexos -->
                    <section class="profile-card manx" aria-labelledby="titulo-docs">
                        <details class="manx-details">
                            <summary class="manx-head">
                                <div>
                                    <h2 id="titulo-docs">Manuales y anexos</h2>
                                    <p>Documentos de apoyo para el desarrollo y el uso de la página.</p>
                                </div>
                                <span class="manx-side">
                                    <span class="manx-count"><?= (int) $docsListos ?> de <?= (int) $totalDocs ?> disponibles</span>
                                    <span class="manx-chevron" aria-hidden="true"></span>
                                </span>
                            </summary>

                            <div class="manx-body">
                                <?php foreach ($documentos as $grupo => $lista): ?>
                                    <div class="manx-group">
                                        <h3 class="manx-group-title"><?= $h($grupo) ?> <span><?= count($lista) ?></span></h3>

                                        <ul class="manx-grid">
                                            <?php foreach ($lista as $doc):
                                                $icono = $iconos[$doc['extension']] ?? $iconos['PDF'];
                                                $ruta  = $base . 'controllers/documentsController.php?doc=' . rawurlencode($doc['clave']);
                                            ?>
                                                <li class="manx-item">
                                                    <div class="manx-card<?= $doc['disponible'] ? '' : ' is-pending' ?>">
                                                        <span class="manx-top">
                                                            <span class="manx-icon"><?= $icono ?></span>
                                                            <?php if ($doc['extension'] !== ''): ?>
                                                                <span class="manx-type"><?= $h($doc['extension']) ?></span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="manx-title"><?= $h($doc['titulo']) ?></span>
                                                        <span class="manx-detail"><?= $h($doc['detalle']) ?></span>
                                                        <span class="manx-foot">
                                                            <?php if ($doc['disponible']): ?>
                                                                <span class="manx-links">
                                                                    <?php if ($doc['verse']): ?>
                                                                        <a class="manx-link" href="<?= $h($ruta . '&modo=ver') ?>"
                                                                            target="_blank" rel="noopener"
                                                                            aria-label="Ver <?= $h($doc['titulo']) ?>">Ver</a>
                                                                    <?php endif; ?>
                                                                    <a class="manx-link manx-link-alt" href="<?= $h($ruta . '&modo=descargar') ?>"
                                                                        aria-label="Descargar <?= $h($doc['titulo']) ?>">Descargar</a>
                                                                </span>
                                                                <span class="manx-size"><?= $h($doc['tamano']) ?></span>
                                                            <?php else: ?>
                                                                <span class="manx-soon">Próximamente</span>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    </section>

                    <!-- Eliminar cuenta -->
                    <section class="profile-card danger-card" aria-labelledby="titulo-eliminar">
                        <header class="card-head">
                            <h2 id="titulo-eliminar">Eliminar cuenta</h2>
                            <p>Esta acción puede borrar permanentemente tus datos y no se puede deshacer.</p>
                        </header>

                        <form class="profile-form" id="form-eliminar" action="../../controllers/profileController.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $h($csrf ?? '') ?>">
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
    <script type="application/json" id="profile-mensajes">
        <?= json_encode(
            ['exito' => $textoExito, 'errores' => $errores],
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
        ) ?>
    </script>
    <script src="<?= $base ?>public/js/profile.js" defer></script>
</body>

</html>