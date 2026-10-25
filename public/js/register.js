(() => {
  const form = document.getElementById("register-form");
  const strengthBar = document.querySelector(".strength");
  const strengthText = document.getElementById("strength-text");

  const fields = {
    name: document.getElementById("name"),
    email: document.getElementById("email"),
    password: document.getElementById("password"),
    confirm: document.getElementById("confirm"),
    terms: document.getElementById("terms"),
  };

  const DEFAULT_HINT = "Mínimo 8 caracteres, con letras y números.";
  const LEVELS = ["", "Débil", "Aceptable", "Buena", "Fuerte"];

  const validators = {
    name: (v) => (v.trim().length < 2 ? "Escribe tu nombre completo." : ""),
    email: (v) => {
      if (!v.trim()) return "Escribe tu correo electrónico.";
      return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim())
        ? ""
        : "Revisa el formato del correo, por ejemplo nombre@correo.com.";
    },
    password: (v) => {
      if (v.length < 8)
        return "La contraseña debe tener al menos 8 caracteres.";
      if (!/[A-Za-z]/.test(v) || !/\d/.test(v))
        return "Incluye al menos una letra y un número.";
      return "";
    },
    confirm: (v) => {
      if (!v) return "Confirma tu contraseña.";
      return v === fields.password.value ? "" : "Las contraseñas no coinciden.";
    },
    terms: () =>
      fields.terms.checked ? "" : "Debes aceptar los términos para continuar.",
  };

  function setError(key, message) {
    const input = fields[key];
    const holder = input.closest(".field");
    const output = document.getElementById(key + "-error");
    output.textContent = message;
    holder.classList.toggle("invalid", Boolean(message));
    input.setAttribute("aria-invalid", message ? "true" : "false");
    if (message) input.setAttribute("aria-describedby", key + "-error");
  }

  function validate(key) {
    const value = key === "terms" ? "" : fields[key].value;
    const message = validators[key](value);
    setError(key, message);
    return !message;
  }

  function score(pw) {
    let s = 0;
    if (pw.length >= 8) s++;
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
    if (/\d/.test(pw)) s++;
    if (/[^A-Za-z0-9]/.test(pw) || pw.length >= 12) s++;
    return pw ? Math.max(1, s) : 0;
  }

  function updateStrength() {
    const level = score(fields.password.value);
    strengthBar.dataset.level = level;
    strengthText.textContent = level
      ? "Seguridad: " + LEVELS[level]
      : DEFAULT_HINT;
  }

  // Mostrar / ocultar contraseña
  document.querySelectorAll(".toggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = document.getElementById(btn.dataset.target);
      const show = input.type === "password";
      input.type = show ? "text" : "password";
      btn.textContent = show ? "Ocultar" : "Mostrar";
      btn.setAttribute(
        "aria-label",
        (show ? "Ocultar " : "Mostrar ") +
          (btn.dataset.target === "confirm"
            ? "confirmación de contraseña"
            : "contraseña"),
      );
    });
  });

  // Validación en blur e input
  ["name", "email", "password", "confirm"].forEach((key) => {
    fields[key].addEventListener("blur", () => {
      if (
        fields[key].value ||
        fields[key].closest(".field").classList.contains("invalid")
      ) {
        validate(key);
      }
    });
    fields[key].addEventListener("input", () => {
      if (fields[key].closest(".field").classList.contains("invalid"))
        validate(key);
    });
  });

  fields.password.addEventListener("input", () => {
    updateStrength();
    if (fields.confirm.value) validate("confirm");
  });

  fields.terms.addEventListener("change", () => validate("terms"));

  // Validación previa antes del POST convencional
  form.addEventListener("submit", (event) => {
    const results = Object.keys(fields).map((key) => [key, validate(key)]);
    const firstInvalid = results.find(([, ok]) => !ok);

    if (firstInvalid) {
      event.preventDefault(); // Detiene el POST si hay errores locales
      fields[firstInvalid[0]].focus();
    }
    // Si todo es válido, el formulario continúa con su posteo nativo a action="..."
  });
  // Procesar respuestas del servidor al cargar la página
  if (window.SERVER_DATA) {
    // Si el registro fue exitoso
    if (window.SERVER_DATA.status === "exito") {
      form.hidden = true;
      success.hidden = false;
      success.focus();
    } 
    // Si hubo algún error en el servidor
    else if (window.SERVER_DATA.errorMessage) {
      if (window.SERVER_DATA.errorField && fields[window.SERVER_DATA.errorField]) {
        setError(window.SERVER_DATA.errorField, window.SERVER_DATA.errorMessage);
        fields[window.SERVER_DATA.errorField].focus();
      } else {
        alert(window.SERVER_DATA.errorMessage);
      }
    }
  }
})();
