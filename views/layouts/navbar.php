<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base     = $base   ?? '../';
$active   = $active ?? '';
$logueado = isset($_SESSION['user_id']);

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

$nombre       = $_SESSION['user_nombre'] ?? '';
$primerNombre = explode(' ', trim($nombre))[0] ?? '';
$inicial      = $nombre !== '' ? mb_strtoupper(mb_substr($nombre, 0, 1)) : '?';

$enlacesInternos = [
    'inicio'       => ['Inicio', 'controllers/dashboardController.php'],
    'movimientos'  => ['Movimientos', 'views/movimientos/index.php'],
    'categorias'   => ['Categorías', 'views/categorias/index.php'],
    'presupuestos' => ['Presupuestos', 'views/presupuestos/index.php'],
];

$logoDestino = $logueado ? 'views/users/dashboard.php' : 'public/index.php';
$logoDestino = $logueado
    ? 'controllers/dashboardController.php'
    : 'public/index.php';
?>
<header class="site-header">
    <nav class="navbar" aria-label="Navegación principal">
        <a class="brand-link" href="<?= $h($base . $logoDestino) ?>">
            <span class="brand-mark" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <rect x="4" y="12" width="4" height="8" rx="1" fill="#fff" />
                    <rect x="10" y="8" width="4" height="12" rx="1" fill="#fff" />
                    <rect x="16" y="4" width="4" height="16" rx="1" fill="#FBBF24" />
                </svg>
            </span>
            <span class="brand-name">Finanzas</span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-menu" aria-label="Abrir menú">
            <svg class="icon-open" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
            <svg class="icon-close" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
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
                                <?= $active === $clave ? 'aria-current="page"' : '' ?>><?= $h($etiqueta) ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><a class="nav-link" href="<?= $h($base) ?>public/index.php#funciones">Funciones</a></li>
                    <li><a class="nav-link" href="<?= $h($base) ?>public/index.php#como-funciona">Cómo funciona</a></li>
                    <li><a class="nav-link"href="<?= $h($base) ?>public/index.php#equipo">Equipo</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-actions">
                <?php if ($logueado): ?>

                    <span class="user-chip">
                        <a href="<?= $h($base) ?>views/users/perfile.php" class="user-avatar" aria-label="Ir a mi perfil"><?= $h($inicial) ?></a>

                        <span class="user-name"><?= $h($primerNombre) ?></span>
                    </span>

                    <a class="btn btn-sm btn-ghost" href="<?= $h($base) ?>controllers/logoutController.php">Cerrar sesión</a>
                <?php else: ?>
                    <a class="btn btn-sm btn-ghost" href="<?= $h($base) ?>views/auth/login.php">Iniciar sesión</a>
                    <a class="btn btn-sm btn-primary" href="<?= $h($base) ?>views/auth/register.php">Crear cuenta</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
<script src="<?= $h($base) ?>public/js/navbar.js" defer></script>