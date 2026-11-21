<?php
// public/index.php
// Página de inicio. Visible para visitantes y para usuarios con sesión.

require_once __DIR__ . '/../config/session.php';
iniciar_sesion_segura();

$base = app_base();  // prefijo de la app en la URL ('/' o '/subcarpeta/'), ver config/app.php
$active = '';
$logueado = isset($_SESSION['user_id']);

// Con sesión iniciada la página muestra datos del usuario: que el navegador no la guarde.
if ($logueado) {
    enviar_cabeceras_privadas();
}

$ctaRuta = $logueado ? $base . 'views/users/dashboard.php' : $base . 'views/auth/register.php';
$ctaTexto = $logueado ? 'Ir a mi panel' : 'Crear mi cuenta';

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
$nombre = $_SESSION['user_nombre'] ?? '';
$primerNombre = explode(' ', trim($nombre))[0] ?? '';

$inicial = $nombre !== ''
    ? mb_strtoupper(mb_substr($nombre, 0, 1))
    : '?';

// Video de presentación: usa presentation.mp4 y, si no existe, el primer .mp4 de public/video/
$videoCarpeta = __DIR__ . '/video/';
$videoArchivo = 'presentation.mp4';
if (!is_file($videoCarpeta . $videoArchivo)) {
    $videosMp4 = glob($videoCarpeta . '*.mp4') ?: [];
    $videoArchivo = $videosMp4 ? basename($videosMp4[0]) : '';
}
$videoUrl = $videoArchivo !== '' ? $base . 'public/video/' . rawurlencode($videoArchivo) : '';

$enlacesInternos = [
    'inicio'       => ['Inicio', 'views/users/dashboard.php'],
    'movimientos'  => ['Movimientos', 'views/transactions/index.php'],
    'categorias'   => ['Categorías', 'views/categories/index.php'],
    'rubros'       => ['Rubros', 'views/rubros/index.php'],
    'presupuestos' => ['Presupuestos', 'views/presupuestos/index.php'],
    'historial'    => ['Historial', 'views/budget_history/index.php'],
    'reportes'     => ['Reportes', 'views/reports/category_report.php'],
];

$logoDestino = $logueado
    ? 'views/users/dashboard.php'
    : 'public/index.php';
if ($logueado) {
    $enlaces = [
        ['Inicio', $base . 'views/users/dashboard.php'],
        ['Movimientos', $base . 'views/transactions/index.php'],
        ['Categorías', $base . 'views/categories/index.php'],
        ['Rubros', $base . 'views/rubros/index.php'],
        ['Presupuestos', $base . 'views/presupuestos/index.php'],
        ['Terminos de uso', $base . 'views/legal/terms_of_use.php'],
        ['Política de privacidad', $base . 'views/legal/privacy_policies.php']
    ];
} else {
    $enlaces = [
        ['Inicio', $base . 'public/index.php'],
        ['Funciones', $base . 'public/index.php#funciones'],
        ['Cómo funciona', $base . 'public/index.php#como-funciona'],
        ['Video', $base . 'public/index.php#video'],
        ['Equipo de desarrollo', $base . 'public/index.php#equipo'],
        ['Crea tu cuenta', $base . 'views/auth/register.php'],
        ['Inicia sesión', $base . 'views/auth/login.php'],
        ['Terminos de uso', $base . 'views/legal/terms_of_use.php'],
        ['Política de privacidad', $base . 'views/legal/privacy_policies.php']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Finanzas · Controla tu dinero y tus presupuestos</title>
    <meta name="description" content="Registra tus ingresos y gastos, reparte tu presupuesto entre categorías y mira cuánto llevas gastado.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>public/css/layout.css">
    <link rel="stylesheet" href="<?= $base ?>public/css/index.css?v=<?= filemtime(__DIR__ . '/css/index.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= $base ?>public/img/icon.webp">
    <link rel="stylesheet" href="<?= $base ?>public/css/alerts.css">
</head>

<body class="layout">

    <header class="site-header">
        <nav class="navbar" aria-label="Navegación principal">

            <a class="brand-link"
                href="<?= $h($base . $logoDestino) ?>">

                <span class="brand-mark" aria-hidden="true">
                    <svg width="20" height="20"
                        viewBox="0 0 24 24"
                        fill="none">

                        <rect x="4" y="12" width="4" height="8"
                            rx="1" fill="#fff" />

                        <rect x="10" y="8" width="4" height="12"
                            rx="1" fill="#fff" />

                        <rect x="16" y="4" width="4" height="16"
                            rx="1" fill="#FBBF24" />

                    </svg>
                </span>

                <span class="brand-name">Finanzas</span>
            </a>

            <button class="nav-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="nav-menu"
                aria-label="Abrir menú">

                <svg class="icon-open"
                    width="22" height="22"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    aria-hidden="true">

                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>

                <svg class="icon-close"
                    width="22" height="22"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    aria-hidden="true">

                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>

            </button>

            <div class="nav-menu" id="nav-menu">

                <ul class="nav-links">

                    <?php if ($logueado): ?>

                        <?php foreach ($enlacesInternos as $clave => [$etiqueta, $ruta]): ?>

                            <li>
                                <a class="nav-link"
                                    href="<?= $h($base . $ruta) ?>"
                                    <?= $active === $clave
                                        ? 'aria-current="page"'
                                        : '' ?>>

                                    <?= $h($etiqueta) ?>

                                </a>
                            </li>

                        <?php endforeach; ?>

                    <?php else: ?>
                        <li><a class="nav-link" href="<?= $h($base) ?>public/index.php#inicio">Inicio</a></li>
                        <li><a class="nav-link" href="<?= $h($base) ?>public/index.php#funciones">Funciones</a></li>
                        <li><a class="nav-link" href="<?= $h($base) ?>public/index.php#como-funciona">Cómo funciona</a></li>
                        <li><a class="nav-link" href="<?= $h($base) ?>public/index.php#video">Video</a></li>
                        <li><a class="nav-link" href="<?= $h($base) ?>public/index.php#equipo">Equipo</a></li>
                    <?php endif; ?>

                </ul>

                <div class="nav-actions">

                    <?php if ($logueado): ?>

                        <span class="user-chip">

                            <a href="<?= $h($base . 'views/users/profile.php') ?>"
                                class="user-avatar"
                                aria-label="Ir a mi perfil">

                                <?= $h($inicial) ?>

                            </a>

                            <span class="user-name">
                                <?= $h($primerNombre) ?>
                            </span>

                        </span>
                        <form id="logout-form" action="<?= $h($base) ?>controllers/logoutController.php" method="POST" style="display: inline;">
                            <button type="submit" class="btn btn-sm btn-ghost">Cerrar sesión</button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-sm btn-ghost" href="<?= $h($base) ?>views/auth/login.php">Iniciar sesión</a>
                        <a class="btn btn-sm btn-primary" href="<?= $h($base) ?>views/auth/register.php">Crear cuenta</a>
                    <?php endif; ?>

                </div>

            </div>

        </nav>
    </header>

    <main>
        <!-- Portada -->
        <section class="hero on-dark" id="inicio">
            <div class="wrap hero-grid">
                <div class="hero-copy">
                    <h1>Sabe en qué se va tu dinero antes de que termine el mes.</h1>
                    <p class="hero-lead">
                        Registra tus ingresos y gastos, reparte un presupuesto entre categorías
                        y mira al instante cuánto llevas gastado y cuánto te queda.
                    </p>
                    <div class="hero-actions">
                        <a class="btn btn-gold" href="<?= $ctaRuta ?>"><?= $ctaTexto ?></a>
                        <?php if (!$logueado): ?>
                            <a class="btn btn-outline-light" href="<?= $h($base) ?>views/auth/login.php">Ya tengo cuenta</a>
                        <?php endif; ?>
                    </div>
                    <?php if (!$logueado): ?>
                        <p class="hero-note">Es gratis y toma un minuto.</p>
                    <?php endif; ?>
                </div>

                <div class="hero-demo">
                    <article class="plan" aria-labelledby="plan-titulo">
                        <header class="plan-head">
                            <div>
                                <h2 class="plan-title" id="plan-titulo">Presupuesto de octubre</h2>
                                <p class="plan-period">1 al 31 de octubre de 2026</p>
                            </div>
                            <p class="plan-total">
                                <strong>$1.000.000</strong>
                                <span>monto total</span>
                            </p>
                        </header>

                        <ul class="plan-list">
                            <li class="plan-item">
                                <div class="plan-row">
                                    <span class="plan-name">Alimentación</span>
                                    <span class="plan-amounts">$312.000 de $400.000</span>
                                </div>
                                <div class="plan-track">
                                    <div class="plan-fill" style="--w:78%; --d:.15s"></div>
                                </div>
                                <p class="plan-status">78 % usado. Quedan $88.000.</p>
                            </li>
                            <li class="plan-item is-over">
                                <div class="plan-row">
                                    <span class="plan-name">Transporte</span>
                                    <span class="plan-amounts">$230.000 de $200.000</span>
                                </div>
                                <div class="plan-track">
                                    <div class="plan-fill" style="--w:100%; --d:.3s"></div>
                                </div>
                                <p class="plan-status">Límite excedido por $30.000.</p>
                            </li>
                            <li class="plan-item">
                                <div class="plan-row">
                                    <span class="plan-name">Educación</span>
                                    <span class="plan-amounts">$90.000 de $250.000</span>
                                </div>
                                <div class="plan-track">
                                    <div class="plan-fill" style="--w:36%; --d:.45s"></div>
                                </div>
                                <p class="plan-status">36 % usado. Quedan $160.000.</p>
                            </li>
                        </ul>

                        <p class="plan-free">Sin asignar a ninguna categoría: $150.000</p>

                        <dl class="plan-totals">
                            <div>
                                <dt>Ingresos</dt>
                                <dd class="is-in">$1.850.000</dd>
                            </div>
                            <div>
                                <dt>Gastos</dt>
                                <dd class="is-out">$632.000</dd>
                            </div>
                            <div>
                                <dt>Saldo</dt>
                                <dd>$1.218.000</dd>
                            </div>
                        </dl>
                    </article>
                    <p class="hero-caption">Ejemplo con datos ficticios.</p>
                </div>
            </div>
        </section>

        <!-- Funciones -->
        <section class="features" id="funciones">
            <div class="wrap features-grid">
                <div class="features-intro">
                    <h2 class="section-title">Todo lo que necesitas para llevar tus cuentas</h2>
                    <p class="section-lead">
                        Sin hojas de cálculo ni cuentas a mano. Tú anotas lo que entra y sale;
                        el sistema hace los cálculos.
                    </p>
                </div>

                <ul class="feature-list">
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7 17V7m0 0L4 10m3-3 3 3M17 7v10m0 0 3-3m-3 3-3-3" />
                            </svg>
                        </span>
                        <h3>Ingresos y gastos</h3>
                        <p>Anota cada movimiento con fecha, monto, descripción y categoría. Edítalo o elimínalo si te equivocas.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3.5 3.5h7.6l9.4 9.4-7.6 7.6-9.4-9.4V3.5z" />
                                <circle cx="7.5" cy="7.5" r="1" />
                            </svg>
                        </span>
                        <h3>Categorías propias</h3>
                        <p>Crea las que uses: alimentación, transporte, educación, entretenimiento. Son solo tuyas.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3.5" y="5" width="17" height="15" rx="2" />
                                <path d="M3.5 10h17M8 3v4M16 3v4" />
                            </svg>
                        </span>
                        <h3>Presupuestos por periodo</h3>
                        <p>Define cuánto vas a gastar entre dos fechas y repártelo entre varias categorías.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 20V10M12 20V4M19 20v-7" />
                            </svg>
                        </span>
                        <h3>Lo planeado contra lo real</h3>
                        <p>Cada categoría muestra lo presupuestado, lo gastado y lo disponible. Si te pasas del límite, se marca en rojo.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 5h16l-6 7.5V19l-4 1.5v-8L4 5z" />
                            </svg>
                        </span>
                        <h3>Filtros</h3>
                        <p>Busca por rango de fechas, tipo de movimiento o categoría. Los totales se ajustan a lo que filtras.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="10.5" width="14" height="10" rx="2" />
                                <path d="M8 10.5V8a4 4 0 0 1 8 0v2.5" />
                            </svg>
                        </span>
                        <h3>Tus datos son privados</h3>
                        <p>Nadie más puede ver ni modificar tu información. Tu contraseña se guarda cifrada.</p>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Cómo funciona -->
        <section class="steps" id="como-funciona">
            <div class="wrap">
                <div class="steps-head">
                    <h2 class="section-title">Empieza en tres pasos</h2>
                    <p class="section-lead">No necesitas conocimientos de finanzas ni de tecnología.</p>
                </div>

                <ol class="step-list">
                    <li class="step">
                        <span class="step-num" aria-hidden="true">1</span>
                        <h3>Crea tu cuenta</h3>
                        <p>Solo necesitas tu nombre, un correo y una contraseña de al menos 8 caracteres.</p>
                    </li>
                    <li class="step">
                        <span class="step-num" aria-hidden="true">2</span>
                        <h3>Registra lo que entra y sale</h3>
                        <p>Añade tus ingresos y gastos y clasifícalos por categoría.</p>
                    </li>
                    <li class="step">
                        <span class="step-num" aria-hidden="true">3</span>
                        <h3>Define un presupuesto</h3>
                        <p>Reparte tu dinero entre categorías y revisa cuánto llevas gastado en cada una.</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Video demostrativo -->
        <section class="video-demo" id="video" aria-labelledby="video-titulo">
            <div class="wrap">
                <div class="video-head">
                    <h2 class="section-title" id="video-titulo">Mira la app en acción</h2>
                    <p class="section-lead">Recorrido corto: movimientos y presupuesto.</p>
                </div>

                <?php if ($videoUrl !== ''): ?>
                    <figure class="video-frame">
                        <video controls preload="metadata" playsinline
                            aria-label="Video de presentación de Finanzas personales">
                            <source src="<?= $h($videoUrl) ?>" type="video/mp4">
                            Tu navegador no soporta la reproducción de video.
                        </video>
                    </figure>
                <?php else: ?>
                    <p class="video-caption">El video todavía no está disponible.</p>
                <?php endif; ?>
            </div>
        </section>


        <!-- Equipo de desarrollo -->
        <section class="team" id="equipo" aria-labelledby="equipo-titulo">
            <div class="wrap">
                <div class="team-head">
                    <h2 class="section-title" id="equipo-titulo">Equipo de desarrollo</h2>
                    <p class="section-lead">
                        Aplicación web multiusuario para controlar ingresos, gastos y presupuestos,
                        construida en PHP con arquitectura MVC y base de datos MySQL.
                        Proyecto académico del programa ADSO del SENA.
                    </p>
                </div>

                <div class="team-grid">
                    <article class="team-card">
                        <h3>Arquitectura y backend</h3>
                        <ul>
                            <li>PHP organizado en modelos, controladores y vistas.</li>
                            <li>Acceso a MySQL con mysqli y sentencias preparadas.</li>
                            <li>Composer: phpdotenv para la configuración y PHPMailer para recuperar la contraseña.</li>
                        </ul>
                    </article>

                    <article class="team-card">
                        <h3>Interfaz y frontend</h3>
                        <ul>
                            <li>HTML5, CSS3 y JavaScript sin frameworks, con diseño responsive.</li>
                            <li>Filtros y búsqueda en tiempo real sobre ingresos, gastos, categorías y rubros.</li>
                            <li>Alertas con SweetAlert2 y reportes exportables en CSV y PDF.</li>
                        </ul>
                    </article>

                    <article class="team-card">
                        <h3>Datos, seguridad y calidad</h3>
                        <ul>
                            <li>MySQL (InnoDB) con 6 tablas relacionadas por llaves foráneas.</li>
                            <li>Contraseñas con bcrypt, sesiones regeneradas y token CSRF en acciones críticas.</li>
                            <li>Documentación: SRS, modelo entidad-relación, manuales y plan de calidad (PAC).</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>


        <!-- Llamado final -->
        <section class="cta on-dark">
            <div class="wrap cta-grid">
                <div>
                    <h2>Tu próximo mes puede empezar con un plan.</h2>
                    <p>Ordena tus ingresos y gastos hoy y llega a fin de mes sabiendo dónde quedó tu dinero.</p>
                </div>
                <div class="cta-actions">
                    <a class="btn btn-white" href="<?= $ctaRuta ?>"><?= $ctaTexto ?></a>
                    <?php if (!$logueado): ?>
                        <a class="btn btn-outline-light" href="<?= $h($base) ?>views/auth/login.php">Iniciar sesión</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer on-dark">
        <div class="footer-inner">
            <div class="footer-brand">
                <a class="brand-link brand-link-light" href="<?= $h($base . $logoDestino) ?>">
                    <span class="brand-mark" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="12" width="4" height="8" rx="1" fill="#fff" />
                            <rect x="10" y="8" width="4" height="12" rx="1" fill="#fff" />
                            <rect x="16" y="4" width="4" height="16" rx="1" fill="#FBBF24" />
                        </svg>
                    </span>
                    <span class="brand-name">Finanzas</span>
                </a>
                <p>Registra tus ingresos y gastos, reparte tu presupuesto entre categorías y sabe cuánto te queda.</p>
            </div>

            <nav class="footer-col" aria-label="Enlaces del sitio">
                <h2>Navegación</h2>
                <ul>
                    <?php foreach ($enlaces as [$etiqueta, $ruta]): ?>
                        <li><a href="<?= $h($ruta) ?>"><?= $h($etiqueta) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="footer-col">
                <h2>Información</h2>
                <ul class="footer-facts">
                    <li>Valores en pesos colombianos (COP)</li>
                    <li>Datos personales tratados según la Ley 1581 de 2012</li>
                    <li>Neiva, Huila</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?= date('Y') ?> Finanzas. Proyecto académico del programa ADSO, SENA, ficha 3407799.</p>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="<?= $base ?>public/js/alerts.js" defer></script>
        <script src="<?= $base ?>public/js/navbar.js" defer></script>
    </footer>

</body>

</html>