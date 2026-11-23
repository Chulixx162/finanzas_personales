<?php
// views/errors/error.php
// Vista amigable de errores. La carga controllers/errorController.php (mostrar_error).
//
// Variables que recibe:
//   $codigo      código HTTP (404, 500...)
//   $titulo      título corto
//   $mensaje     explicación para la persona
//   $referencia  ERR-AAAAMMDD-XXXXXX (vacío en páginas de cortesía como el 404)
//   $detalle     detalle técnico: solo viene con valor si APP_DEBUG=true
//   $base        prefijo de la app en la URL (app_base())
//   $destino     URL de "Ir al inicio"
//   $h           función de escape HTML
//
// Es independiente a propósito: no incluye navbar.php ni footer.php, porque si esos
// archivos fueran la causa del error la página de error también fallaría.
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= $h($titulo) ?> · Finanzas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $h($base) ?>public/css/layout.css">
    <link rel="stylesheet" href="<?= $h($base) ?>public/css/error.css">
    <link rel="icon" type="image/x-icon" href="<?= $h($base) ?>public/img/icon.webp">
</head>

<body class="layout">

    <main class="error-page">
        <section class="error-card" aria-labelledby="error-titulo" role="alert">

            <p class="error-code"><?= (int) $codigo ?></p>

            <h1 id="error-titulo"><?= $h($titulo) ?></h1>
            <p class="error-text"><?= $h($mensaje) ?></p>

            <?php if ($referencia !== ''): ?>
                <p class="error-ref">
                    Si necesitas ayuda, comparte esta referencia:
                    <strong><?= $h($referencia) ?></strong>
                </p>
            <?php endif; ?>

            <div class="error-actions">
                <a class="btn btn-primary" href="<?= $h($destino) ?>">Ir al inicio</a>
                <button class="btn btn-ghost" type="button" onclick="window.history.back()">Volver atrás</button>
            </div>

            <?php if (!empty($detalle)): ?>
                <details class="error-debug">
                    <summary>Detalle técnico (solo visible en modo depuración)</summary>
                    <pre><?= $h($detalle) ?></pre>
                </details>
            <?php endif; ?>

        </section>
    </main>

</body>

</html>