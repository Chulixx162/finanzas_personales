// public/js/filters.js
// Botón "Limpiar filtros" de los buscadores en tiempo real (categorías, rubros).
// Vacía el campo de búsqueda y avisa a la página para que vuelva a mostrar todas las filas.

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-filter-clear]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const tarjeta = boton.closest('.filter-card');
            const campo = tarjeta ? tarjeta.querySelector('input[type="text"]') : null;

            if (!campo) {
                return;
            }

            campo.value = '';
            // categories.js escucha "input" y rubros.js escucha "keyup".
            campo.dispatchEvent(new Event('input', { bubbles: true }));
            campo.dispatchEvent(new Event('keyup', { bubbles: true }));
            campo.focus();
        });
    });
});