<?php
// views/legal/privacy_policies.php
// Página pública, igual que terms_of_use.php: no requiere sesión.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base     = '../../';   // desde views/legal/ hasta la raíz del proyecto
$active   = '';         // las páginas legales no están en el menú principal
$logueado = isset($_SESSION['user_id']);

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

// Cambia la fecha solo cuando cambie la política (no uses date('d/m/Y'):
// mostraría siempre el día de hoy como si la política se acabara de actualizar).
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
    <title>Política de privacidad | Finanzas Personales</title>
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

            <h1>Política de privacidad</h1>
            <p class="updated">Última actualización: <?= $h($ultimaActualizacion) ?></p>

            <nav class="toc" aria-label="Contenido">
                <p class="toc-title">Contenido</p>
                <ol>
                    <li><a href="#datos">Datos que recopilamos</a></li>
                    <li><a href="#finalidad">Finalidad del tratamiento</a></li>
                    <li><a href="#seguridad">Seguridad</a></li>
                    <li><a href="#comparticion">Compartición de datos</a></li>
                    <li><a href="#derechos">Tus derechos</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ol>
            </nav>

            <section id="datos">
                <h2>1. Datos que recopilamos</h2>
                <ul>
                    <li>Nombre y correo electrónico.</li>
                    <li>Contraseña, almacenada cifrada.</li>
                    <li>La información financiera que tú registres: movimientos, categorías y rubros.</li>
                </ul>
            </section>

            <section id="finalidad">
                <h2>2. Finalidad del tratamiento</h2>
                <p>Usamos tus datos únicamente para crear tu cuenta, autenticarte y
                    mostrarte tu información financiera dentro de la aplicación.</p>
            </section>

            <section id="seguridad">
                <h2>3. Seguridad</h2>
                <p>Las contraseñas se guardan cifradas y aplicamos medidas para
                    proteger tu información contra accesos no autorizados.</p>
            </section>

            <section id="comparticion">
                <h2>4. Compartición de datos</h2>
                <p>No vendemos ni compartimos tus datos con terceros, salvo obligación legal.</p>
            </section>

            <section id="derechos">
                <h2>5. Tus derechos</h2>
                <p>Puedes conocer, actualizar, corregir y solicitar la eliminación de
                    tus datos personales (Ley 1581 de 2012, Colombia). Desde tu perfil
                    puedes cambiar tus datos o eliminar tu cuenta.</p>
            </section>

            <section id="contacto">
                <h2>6. Contacto</h2>
                <p>Para manifestar dudas, quejas o reclamos, escríbenos a
                    <a href="mailto:<?= $h($correoContacto) ?>"><?= $h($correoContacto) ?></a>.</p>
            </section>

            <div class="actions">
                <a class="btn btn-primary" href="<?= $h($volverRuta) ?>"><?= $h($volverTexto) ?></a>
                <a href="terms_of_use.php">Ver términos de uso</a>
            </div>

        </article>
    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>