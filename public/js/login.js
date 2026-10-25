(() => {
  const form = document.getElementById("login-form");
  const formError = document.getElementById("form-error");

  const fields = {
    email: document.getElementById("email"),
    password: document.getElementById("password"),
  };

  const validators = {
    email: (v) => {
      if (!v.trim()) return "Escribe tu correo electrónico.";
      return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim())
        ? ""
        : "Revisa el formato del correo, por ejemplo nombre@correo.com.";
    },
    password: (v) => (v ? "" : "Escribe tu contraseña."),
  };

  function setError(key, message) {
    const input = fields[key];
    input.closest(".field").classList.toggle("invalid", Boolean(message));
    input.setAttribute("aria-invalid", message ? "true" : "false");
    document.getElementById(key + "-error").textContent = message;
    if (message) input.setAttribute("aria-describedby", key + "-error");
  }

  function validate(key) {
    const message = validators[key](fields[key].value);
    setError(key, message);
    return !message;
  }

  function showFormError(message) {
    if (!formError) return;
    formError.textContent = message;
    formError.hidden = !message;
  }

  // Mostrar / ocultar contraseña
  document.querySelectorAll(".toggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = document.getElementById(btn.dataset.target);
      const show = input.type === "password";
      input.type = show ? "text" : "password";
      btn.textContent = show ? "Ocultar" : "Mostrar";
      btn.setAttribute("aria-label", show ? "Ocultar contraseña" : "Mostrar contraseña");
    });
  });

  // Validaciones al salir y al escribir
  Object.keys(fields).forEach((key) => {
    fields[key].addEventListener("blur", () => {
      if (fields[key].value || fields[key].closest(".field").classList.contains("invalid")) {
        validate(key);
      }
    });
    fields[key].addEventListener("input", () => {
      showFormError("");
      if (fields[key].closest(".field").classList.contains("invalid")) validate(key);
    });
  });

  // Validación previa antes del envío por POST nativo
  form.addEventListener("submit", (event) => {
    showFormError("");

    const results = Object.keys(fields).map((key) => [key, validate(key)]);
    const firstInvalid = results.find(([, ok]) => !ok);

    if (firstInvalid) {
      event.preventDefault(); // Detiene el envío POST si hay errores locales
      fields[firstInvalid[0]].focus();
    }
  });

  // Procesar respuestas de error del servidor pasadas desde PHP al cargar la página
  if (window.SERVER_DATA) {
    if (window.SERVER_DATA.errorMessage) {
      if (window.SERVER_DATA.errorField && fields[window.SERVER_DATA.errorField]) {
        setError(window.SERVER_DATA.errorField, window.SERVER_DATA.errorMessage);
        fields[window.SERVER_DATA.errorField].focus();
      } else {
        showFormError(window.SERVER_DATA.errorMessage);
      }
    }
  }
})();