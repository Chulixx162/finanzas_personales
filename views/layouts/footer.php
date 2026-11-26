<?php
// views/layouts/footer.php
// Pie de página. Carga config/app.php por sí mismo para que funcione
// también en login y registro, que no lo incluyen.

require_once __DIR__ . '/../../config/app.php';

$logueado = isset($_SESSION['user_id']);
$base     = app_base();

$h = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

$logoDestino = $logueado ? 'views/users/dashboard.php' : 'public/index.php';

if ($logueado) {
    $navegacion = [
        ['Inicio',       'views/users/dashboard.php'],
        ['Movimientos',  'views/transactions/index.php'],
        ['Categorías',   'views/categories/index.php'],
        ['Rubros',       'views/rubros/index.php'],
        ['Presupuestos', 'views/presupuestos/index.php'],
    ];
    $cuenta = [
        ['Mi perfil', 'views/users/profile.php'],
    ];
} else {
    $navegacion = [
        ['Inicio',               'public/index.php'],
        ['Funciones',            'public/index.php#funciones'],
        ['Cómo funciona',        'public/index.php#como-funciona'],
        ['Equipo de desarrollo', 'public/index.php#equipo'],
    ];
    $cuenta = [
        ['Crea tu cuenta', 'views/auth/register.php'],
        ['Inicia sesión',  'views/auth/login.php'],
    ];
}

$legal = [
    ['Términos de uso',        'views/legal/terms_of_use.php'],
    ['Política de privacidad', 'views/legal/privacy_policies.php'],
];

$columnas = [
    'Navegación' => $navegacion,
    'Cuenta'     => $cuenta,
    'Legal'      => $legal,
];
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

        <?php foreach ($columnas as $titulo => $enlaces): ?>
            <nav class="footer-col" aria-label="<?= $h($titulo) ?>">
                <h2><?= $h($titulo) ?></h2>
                <ul>
                    <?php foreach ($enlaces as [$etiqueta, $ruta]): ?>
                        <li><a href="<?= $h($base . $ruta) ?>"><?= $h($etiqueta) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endforeach; ?>
    </div>

    <div class="footer-bottom">
        <div class="footer-bottom-inner">
            <p>© <?= date('Y') ?> Finanzas. Proyecto académico del programa ADSO, SENA, ficha 3407799.</p>
            <p>Valores en COP · Datos tratados según la Ley 1581 de 2012 · Neiva, Huila</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $h($base) ?>public/js/alerts.js" defer></script>