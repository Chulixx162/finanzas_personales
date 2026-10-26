<?php
// public/index.php
// Página de inicio. Visible para visitantes y para usuarios con sesión.

session_start();

$base     = '../';   // desde public/ hasta la raíz del proyecto
$active   = '';
$logueado = isset($_SESSION['user_id']);

$ctaRuta  = $base . ($logueado ? 'views/users/dashboard.php' : 'views/auth/register.php');
$ctaTexto = $logueado ? 'Ir a mi panel' : 'Crear mi cuenta';
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
    <link rel="stylesheet" href="<?= $base ?>public/css/index.css">
    <link rel="icon" type="image/x-icon" href="<?= $base ?>public/img/icon.webp">
</head>

<body class="layout">

    <?php require __DIR__ . '/../views/layouts/navbar.php'; ?>

    <main>
        <!-- Portada -->
        <section class="hero on-dark">
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
                            <a class="btn btn-outline-light" href="<?= $base ?>views/auth/login.php">Ya tengo cuenta</a>
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
                                <div class="plan-track"><div class="plan-fill" style="--w:78%; --d:.15s"></div></div>
                                <p class="plan-status">78 % usado. Quedan $88.000.</p>
                            </li>
                            <li class="plan-item is-over">
                                <div class="plan-row">
                                    <span class="plan-name">Transporte</span>
                                    <span class="plan-amounts">$230.000 de $200.000</span>
                                </div>
                                <div class="plan-track"><div class="plan-fill" style="--w:100%; --d:.3s"></div></div>
                                <p class="plan-status">Límite excedido por $30.000.</p>
                            </li>
                            <li class="plan-item">
                                <div class="plan-row">
                                    <span class="plan-name">Educación</span>
                                    <span class="plan-amounts">$90.000 de $250.000</span>
                                </div>
                                <div class="plan-track"><div class="plan-fill" style="--w:36%; --d:.45s"></div></div>
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
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17V7m0 0L4 10m3-3 3 3M17 7v10m0 0 3-3m-3 3-3-3" /></svg>
                        </span>
                        <h3>Ingresos y gastos</h3>
                        <p>Anota cada movimiento con fecha, monto, descripción y categoría. Edítalo o elimínalo si te equivocas.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 3.5h7.6l9.4 9.4-7.6 7.6-9.4-9.4V3.5z" /><circle cx="7.5" cy="7.5" r="1" /></svg>
                        </span>
                        <h3>Categorías propias</h3>
                        <p>Crea las que uses: alimentación, transporte, educación, entretenimiento. Son solo tuyas.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="15" rx="2" /><path d="M3.5 10h17M8 3v4M16 3v4" /></svg>
                        </span>
                        <h3>Presupuestos por periodo</h3>
                        <p>Define cuánto vas a gastar entre dos fechas y repártelo entre varias categorías.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 20V10M12 20V4M19 20v-7" /></svg>
                        </span>
                        <h3>Lo planeado contra lo real</h3>
                        <p>Cada categoría muestra lo presupuestado, lo gastado y lo disponible. Si te pasas del límite, se marca en rojo.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16l-6 7.5V19l-4 1.5v-8L4 5z" /></svg>
                        </span>
                        <h3>Filtros</h3>
                        <p>Busca por rango de fechas, tipo de movimiento o categoría. Los totales se ajustan a lo que filtras.</p>
                    </li>
                    <li class="feature">
                        <span class="feature-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10.5" width="14" height="10" rx="2" /><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5" /></svg>
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
        
        
        <!-- Equipo de desarrollo -->
        <section class="team" id="equipo" aria-labelledby="equipo-titulo">
            <div class="wrap">
                <div class="team-head">
                    <p class="team-eyebrow">Nuestro proyecto</p>
                    <h2 class="section-title" id="equipo-titulo">
                        Equipo de desarrollo
                    </h2>
                    <p class="section-lead">
                        Finanzas personales es un proyecto académico creado
                        para practicar el análisis, diseño y desarrollo de
                        una aplicación web para organizar ingresos y gastos.
                    </p>
                </div>

                <div class="team-grid">
                    <article class="team-card">
                        <span class="team-symbol" aria-hidden="true">01</span>
                        <h3>Análisis y organización</h3>
                        <p>
                            Definición de las necesidades del usuario,
                            requisitos y estructura de las funcionalidades.
                        </p>
                    </article>

                    <article class="team-card">
                        <span class="team-symbol" aria-hidden="true">02</span>
                        <h3>Desarrollo web</h3>
                        <p>
                            Construcción de las vistas y la lógica de la
                            aplicación con PHP, HTML, CSS y JavaScript.
                        </p>
                    </article>

                    <article class="team-card">
                        <span class="team-symbol" aria-hidden="true">03</span>
                        <h3>Base de datos y pruebas</h3>
                        <p>
                            Organización de la información en MySQL y
                            verificación del funcionamiento de los módulos.
                        </p>
                    </article>
                </div>

                <p class="team-note">
                    Proyecto académico del programa ADSO del SENA.
                </p>
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
                        <a class="btn btn-outline-light" href="<?= $base ?>views/auth/login.php">Iniciar sesión</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../views/layouts/footer.php'; ?>

</body>

</html>