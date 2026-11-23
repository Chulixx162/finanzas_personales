// public/js/session.js
// Seguridad de la sesión en el navegador (solo se carga con usuario logueado):
//   1. Inactividad: tras 30 minutos sin actividad se cierra la sesión y se redirige.
//   2. Botón "atrás": si el navegador restaura una página privada desde su caché
//      (por ejemplo tras cerrar sesión), se recarga para que el servidor decida.
// El servidor es quien manda: este script solo lo consulta y redirige.

(() => {
  const script = document.currentScript;
  const urlEstado = script.dataset.statusUrl;
  const urlInicio = script.dataset.inicioUrl;
  const urlLogin = script.dataset.loginUrl;

  const LIMITE_MS = 30 * 60 * 1000; // 30 minutos
  const AVISO_SERVIDOR_MS = 60 * 1000; // cada cuánto se avisa al servidor que hay actividad
  const CLAVE = "finanzas_ultima_actividad"; // compartida entre pestañas

  let ultimoAviso = Date.now();
  let temporizador = null;
  let saliendo = false;

  /* ---------- Actividad compartida entre pestañas ---------- */

  const leerActividad = () => {
    try {
      return Number(localStorage.getItem(CLAVE)) || Date.now();
    } catch (e) {
      return Date.now();
    }
  };

  const guardarActividad = (ahora) => {
    try {
      localStorage.setItem(CLAVE, String(ahora));
    } catch (e) {
      /* sin localStorage: cada pestaña lleva su propia cuenta */
    }
  };

  /* ---------- Salida ---------- */

  function salir(motivo) {
    if (saliendo) {
      return;
    }

    saliendo = true;
    clearTimeout(temporizador);

    // Inactividad -> login con aviso. Sesión ya cerrada -> página de inicio.
    window.location.replace(motivo === "expirada" ? urlLogin : urlInicio);
  }

  /* ---------- Conversación con el servidor ---------- */

  async function consultar(accion, metodo) {
    const respuesta = await fetch(`${urlEstado}?action=${accion}`, {
      method: metodo,
      cache: "no-store",
      credentials: "same-origin",
      headers: { Accept: "application/json" },
    });

    let datos = {};
    try {
      datos = await respuesta.json();
    } catch (e) {
      /* respuesta que no es JSON: se trata como sesión no válida */
    }

    return { ok: respuesta.ok, datos };
  }

  // Pregunta al servidor si la sesión sigue viva y reprograma la siguiente revisión.
  async function verificar() {
    if (saliendo) {
      return;
    }

    try {
      const { datos } = await consultar("status", "GET");

      if (!datos.activa) {
        salir(datos.motivo);
        return;
      }

      // La sesión sigue viva (hubo actividad en otra pestaña o equipo):
      // se vuelve a revisar justo cuando le toque expirar.
      programar(Math.max(datos.restante * 1000, 1000) + 500);
    } catch (e) {
      // Sin conexión: se reintenta en un minuto, sin cerrar la sesión por error.
      programar(60 * 1000);
    }
  }

  function programar(milisegundos) {
    clearTimeout(temporizador);
    temporizador = setTimeout(verificar, milisegundos);
  }

  /* ---------- Detección de actividad ---------- */

  let ultimoRegistro = 0;

  function registrarActividad() {
    const ahora = Date.now();

    // Limita el trabajo: como máximo una vez por segundo.
    if (ahora - ultimoRegistro < 1000) {
      return;
    }

    ultimoRegistro = ahora;
    guardarActividad(ahora);
    programar(LIMITE_MS + 500);

    // Mantiene viva la sesión en el servidor mientras el usuario trabaja.
    if (ahora - ultimoAviso > AVISO_SERVIDOR_MS) {
      ultimoAviso = ahora;
      consultar("keepalive", "POST")
        .then(({ datos }) => {
          if (datos && datos.activa === false) {
            salir(datos.motivo);
          }
        })
        .catch(() => {
          /* se ignora: la próxima actividad lo reintenta */
        });
    }
  }

  ["mousemove", "mousedown", "keydown", "scroll", "touchstart", "wheel"].forEach(
    (evento) => window.addEventListener(evento, registrarActividad, { passive: true }),
  );

  // Otra pestaña registró actividad: se reinicia la cuenta de esta.
  window.addEventListener("storage", (evento) => {
    if (evento.key === CLAVE) {
      programar(LIMITE_MS + 500);
    }
  });

  /* ---------- Botón "atrás" y pestañas dormidas ---------- */

  // Si el navegador muestra la página desde su caché (botón atrás/adelante),
  // se recarga: el servidor ve que ya no hay sesión y redirige al inicio.
  window.addEventListener("pageshow", (evento) => {
    if (evento.persisted) {
      window.location.reload();
    }
  });

  // Al volver a la pestaña (o despertar el equipo) se revisa la sesión de inmediato.
  document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible") {
      verificar();
    }
  });

  guardarActividad(Date.now());
  programar(Math.max(LIMITE_MS - (Date.now() - leerActividad()), 1000) + 500);
})();