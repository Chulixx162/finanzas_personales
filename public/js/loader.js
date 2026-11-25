document.addEventListener('DOMContentLoaded', () => {
  const loader = document.getElementById('global-loader');
  if (!loader) return;

  const textoLoader = loader.querySelector('.global-loader__texto');

  window.mostrarLoader = (mensaje = 'Cargando datos...') => {
    if (textoLoader) textoLoader.textContent = mensaje;
    loader.classList.add('activo');
  };

  window.ocultarLoader = () => {
    loader.classList.remove('activo');
  };

  // 1. Mostrar al navegar entre páginas
  document.addEventListener('click', (e) => {
    const enlace = e.target.closest('a[href]');
    if (!enlace || e.defaultPrevented) return;

    const href = enlace.getAttribute('href');
    if (
      !href ||
      href.startsWith('#') ||
      href.startsWith('javascript:') ||
      enlace.target === '_blank' ||
      e.ctrlKey ||
      e.metaKey
    ) {
      return;
    }

    window.mostrarLoader('Cargando...');
  });

  // 2. Mostrar al enviar formularios (respetando SweetAlert2 y validaciones)
  document.addEventListener('submit', (e) => {
    if (e.defaultPrevented) return;
    if (typeof Swal !== 'undefined' && Swal.isVisible()) return;

    const form = e.target;
    const btn = form.querySelector('button[type="submit"], input[type="submit"]');
    if (btn) {
      btn.disabled = true;
      if (btn.tagName === 'BUTTON') {
        btn.textContent = 'Cargando...';
      } else {
        btn.value = 'Cargando...';
      }
    }

    window.mostrarLoader('Procesando datos...');
  });
});

// 3. Ocultar al terminar de cargar o al volver atrás
window.addEventListener('load', () => {
  if (window.ocultarLoader) window.ocultarLoader();
});

window.addEventListener('pageshow', () => {
  if (window.ocultarLoader) window.ocultarLoader();
});