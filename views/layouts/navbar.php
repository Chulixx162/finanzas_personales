<?php


require_once __DIR__ . '/../../config/session.php';
iniciar_sesion_segura();

$base     = '../../';   // desde views/layouts/navbar.php hasta la raíz del proyecto
$active   = $active ?? '';
$logueado = isset($_SESSION['user_id']);

$active = $active ?? '';

$h = fn($texto) => htmlspecialchars(
    (string) $texto,
    ENT_QUOTES,
    'UTF-8'
);

$nombre = $_SESSION['user_nombre'] ?? '';
$primerNombre = explode(' ', trim($nombre))[0] ?? '';

$inicial = $nombre !== ''
    ? mb_strtoupper(mb_substr($nombre, 0, 1))
    : '?';

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

?>

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

        <?php if ($logueado): ?>
            <a class="nav-profile"
                href="<?= $h($base . 'views/users/profile.php') ?>"
                aria-label="Ir a mi perfil"
                <?= $active === 'perfil' ? 'aria-current="page"' : '' ?>>
                <span class="user-avatar" aria-hidden="true"><?= $h($inicial) ?></span>
            </a>
        <?php endif; ?>
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
                    <li><a class="nav-link" href="../public/index.php#inicio">Inicio</a></li>
                    <li><a class="nav-link" href="../public/index.php#funciones">Funciones</a></li>
                    <li><a class="nav-link" href="../public/index.php#como-funciona">Cómo funciona</a></li>
                    <li><a class="nav-link" href="../public/index.php#equipo">Equipo</a></li>
                <?php endif; ?>

            </ul>

            <div class="nav-actions">

                <?php if ($logueado): ?>

                    <a class="user-chip"
                        href="<?= $h($base . 'views/users/profile.php') ?>"
                        aria-label="Ir a mi perfil"
                        <?= $active === 'perfil' ? 'aria-current="page"' : '' ?>>
                        <span class="user-avatar" aria-hidden="true"><?= $h($inicial) ?></span>
                        <span class="user-name"><?= $h($primerNombre) ?></span>
                    </a>
                    <form id="logout-form" action="<?= $h($base) ?>controllers/logoutController.php" method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-sm btn-ghost">Cerrar sesión</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-sm btn-ghost" href="../views/auth/login.php">Iniciar sesión</a>
                    <a class="btn btn-sm btn-primary" href="../views/auth/register.php">Crear cuenta</a>
                <?php endif; ?>

            </div>

        </div>

    </nav>
</header>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $h($base) ?>public/js/alerts.js"></script>
<script src="<?= $h($base) ?>public/js/navbar.js"></script>