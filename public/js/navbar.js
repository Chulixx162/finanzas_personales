// public/js/navbar.js
// Abre y cierra el menú en pantallas pequeñas y confirma el cierre de sesión.
(() => {
  const navbar = document.querySelector(".navbar");
  const toggle = document.querySelector(".nav-toggle");

  if (navbar && toggle) {
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
    window
      .matchMedia("(min-width: 861px)")
      .addEventListener("change", (event) => {
        if (event.matches) setOpen(false);
      });
  }

  // Confirmación de cerrar sesión
  const logoutForm = document.getElementById("logout-form");

  if (logoutForm) {
    logoutForm.addEventListener("submit", async function (event) {
      event.preventDefault(); // Detiene el envío automático POST

      // 1. Verificación si SweetAlert/alerta no está cargado
      if (typeof alerta !== "function") {
        if (window.confirm("¿Seguro que deseas cerrar sesión?")) {
          this.submit();
        }
        return;
      }

      // 2. Ejecución asíncrona de la alerta usando tu función en alerts.js
      const confirmado = await alerta(
        "¿Cerrar sesión?",
        "Se cerrará tu sesión actual y volverás al inicio de sesión.",
        "warning",
        "Sí, cerrar sesión",
        "Cancelar",
      );

      // 3. Si el usuario confirma, enviamos el formulario nativo POST
      if (confirmado) {
        logoutForm.submit();
      }
    });
  }
})();