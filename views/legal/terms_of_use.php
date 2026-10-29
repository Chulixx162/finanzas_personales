<?php
// views/legal/terms_of_use.php
// Página pública: no requiere sesión, se lee antes de registrarse.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base     = '../../';   // desde views/legal/ hasta la raíz del proyecto
$active   = '';         // las páginas legales no están en el menú principal
$logueado = isset($_SESSION['user_id']);

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

// Cambia la fecha solo cuando cambien los términos.
$ultimaActualizacion = '27 de octubre de 2026';
$correoContacto      = 'Equipo7@gmail.com';

// El botón de regreso depende de si hay sesión.
$volverRuta  = $base . ($logueado ? 'controllers/dashboardController.php' : 'views/auth/register.php');
$volverTexto = $logueado ? 'Ir a mi panel' : 'Volver al registro';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Términos de uso | Finanzas Personales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $h($base) ?>public/css/layout.css">
    <link rel="stylesheet" href="<?= $h($base) ?>public/css/legal.css">
    <link rel="icon" type="image/x-icon" href="<?= $h($base) ?>public/img/icon.webp">
</head>

<body class="layout">

    <?php require __DIR__ . '/../layouts/navbar.php'; ?>

    <main class="legal-page">
        <article class="legal">

            <h1>Términos de uso</h1>
            <p class="updated">Última actualización: <?= $h($ultimaActualizacion) ?></p>

            <nav class="toc" aria-label="Contenido">
                <p class="toc-title">Contenido</p>
                <ol>
                    <li><a href="#aceptacion">Aceptación de los términos</a></li>
                    <li><a href="#servicio">Descripción del servicio</a></li>
                    <li><a href="#cuenta">Registro y cuenta</a></li>
                    <li><a href="#uso">Uso permitido</a></li>
                    <li><a href="#responsabilidad">Limitación de responsabilidad</a></li>
                    <li><a href="#cambios">Modificaciones</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ol>
            </nav>

            <section id="aceptacion">
                <h2>1. Aceptación de los términos</h2>
                <p>Al crear una cuenta y usar esta aplicación de finanzas personales,
                    aceptas estos términos. Si no estás de acuerdo, no debes usar el servicio.</p>
            </section>

            <section id="servicio">
                <h2>2. Descripción del servicio</h2>
                <p>La aplicación te permite registrar y consultar tus movimientos
                    financieros, organizados por categorías y rubros, para llevar un
                    control de tus ingresos y gastos.</p>
            </section>

            <section id="cuenta">
                <h2>3. Registro y cuenta</h2>
                <ul>
                    <li>Debes proporcionar información veraz y actualizada.</li>
                    <li>Eres responsable de mantener la confidencialidad de tu contraseña.</li>
                    <li>Eres responsable de toda actividad realizada desde tu cuenta.</li>
                </ul>
            </section>

            <section id="uso">
                <h2>4. Uso permitido</h2>
                <p>Te comprometes a no:</p>
                <ul>
                    <li>Usar el sistema para actividades ilegales.</li>
                    <li>Intentar acceder a cuentas o datos de otros usuarios.</li>
                    <li>Alterar, dañar o sobrecargar el funcionamiento de la aplicación.</li>
                </ul>
            </section>

            <section id="responsabilidad">
                <h2>5. Limitación de responsabilidad</h2>
                <p>La aplicación es una herramienta de apoyo y no ofrece asesoría
                    financiera profesional. No nos hacemos responsables por decisiones
                    tomadas con base en la información que registres.</p>
            </section>

            <section id="cambios">
                <h2>6. Modificaciones</h2>
                <p>Podemos actualizar estos términos. Los cambios se publicarán en
                    esta página junto con la nueva fecha de actualización.</p>
            </section>

            <section id="contacto">
                <h2>7. Contacto</h2>
                <p>Si tienes dudas, escríbenos a
                    <a href="mailto:<?= $h($correoContacto) ?>"><?= $h($correoContacto) ?></a>.</p>
            </section>

            <div class="actions">
                <a class="btn btn-primary" href="<?= $h($volverRuta) ?>"><?= $h($volverTexto) ?></a>
                <a href="privacy_policies.php">Ver política de privacidad</a>
            </div>

        </article>
    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>