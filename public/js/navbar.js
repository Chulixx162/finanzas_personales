// public/js/navbar.js
// Abre y cierra el menú en pantallas pequeñas.
(() => {
  const navbar = document.querySelector(".navbar");
  const toggle = document.querySelector(".nav-toggle");
  if (!navbar || !toggle) return;

  function setOpen(open) {
    navbar.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", String(open));
    toggle.setAttribute("aria-label", open ? "Cerrar menú" : "Abrir menú");
  }

  toggle.addEventListener("click", () => {
    setOpen(!navbar.classList.contains("is-open"));
  });

  // Cerrar con Escape y devolver el foco al botón
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && navbar.classList.contains("is-open")) {
      setOpen(false);
      toggle.focus();
    }
  });

  // Cerrar al elegir un enlace del menú
  navbar.querySelectorAll(".nav-menu a").forEach((link) => {
    link.addEventListener("click", () => setOpen(false));
  });

  // Si la pantalla vuelve a ser ancha, dejar el menú en su estado normal
  window.matchMedia("(min-width: 861px)").addEventListener("change", (event) => {
    if (event.matches) setOpen(false);
  });
})();