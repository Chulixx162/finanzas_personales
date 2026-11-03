<?php
// views/layouts/footer.php
// Pie de página. Usa $base igual que el navbar.

$base     = $base ?? '../';
$logueado = isset($_SESSION['user_id']);

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

$logoDestino = $logueado
    ? 'views/users/dashboard.php'
    : 'public/index.php';
if ($logueado) {
    $enlaces = [
        ['Inicio', 'views/users/dashboard.php'],
        ['Movimientos',  'views/movimientos/index.php'],
        ['Categorías',   'views/categories/index.php'],
        ['Presupuestos', 'views/presupuestos/index.php'],
        ['Cerrar sesión', 'controllers/logoutController.php'],
    ];
} else {
    $enlaces = [
        ['Funciones',      'public/index.php#funciones'],
        ['Cómo funciona',  'public/index.php#como-funciona'],
        ['Crear cuenta',   'views/auth/register.php'],
        ['Equipo de desarrollo', 'public/index.php#equipo'],
        ['Iniciar sesión', 'views/auth/login.php'],
    ];
}
?>
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
                    <li><a href="<?= $h($base . $ruta) ?>"><?= $h($etiqueta) ?></a></li>
                <?php endforeach; ?>
                <li><a href="<?= $h($base) ?>views/legal/terms_of_use.php">Términos de uso</a></li>
                <li><a href="<?= $h($base) ?>views/legal/privacy_policies.php">Política de privacidad</a></li>
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
</footer>