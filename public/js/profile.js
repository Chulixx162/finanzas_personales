// public/js/profile.js
// Guarda los cambios del perfil sin salir de la página y muestra un aviso.
// Si algo impide usar fetch, el formulario se envía de la forma tradicional.

(function () {
  'use strict';

  /* ---------- Mostrar / ocultar contraseña ---------- */
  // Se activa antes que lo demás para que funcione también en el formulario de eliminar.
  function cambiarVisibilidad(boton, mostrar) {
    var campo = document.getElementById(boton.getAttribute('data-target'));
    if (!campo) return;

    campo.type = mostrar ? 'text' : 'password';
    boton.textContent = mostrar ? 'Ocultar' : 'Mostrar';
    boton.setAttribute(
      'aria-label',
      (boton.getAttribute('aria-label') || '').replace(/^(Mostrar|Ocultar)/, mostrar ? 'Ocultar' : 'Mostrar')
    );
  }

  // Vuelve a ocultar las contraseñas del contenedor indicado (por defecto, toda la página).
  function ocultarContrasenas(contenedor) {
    var botones = (contenedor || document).querySelectorAll('.toggle');
    for (var i = 0; i < botones.length; i++) cambiarVisibilidad(botones[i], false);
  }

  var botonesToggle = document.querySelectorAll('.toggle');
  for (var b = 0; b < botonesToggle.length; b++) {
    botonesToggle[b].addEventListener('click', function () {
      var campo = document.getElementById(this.getAttribute('data-target'));
      if (campo) cambiarVisibilidad(this, campo.type === 'password');
    });
  }

  /* ---------- Guardado por fetch ---------- */
  var form = document.getElementById('form-profile');
  var region = document.getElementById('toast-region');
  var datos = document.getElementById('profile-mensajes');

  if (!form || !region || !datos || !window.fetch) return;

  var mensajes = { exito: '', errores: {} };
  try {
    mensajes = JSON.parse(datos.textContent);
  } catch (e) { /* se usan los textos por defecto de abajo */ }

  var enviando = false;

  function textoError(codigo) {
    return (mensajes.errores && mensajes.errores[codigo]) || 'Ocurrió un error.';
  }

  /* ---------- Aviso ---------- */
  function mostrarAviso(tipo, texto) {
    region.textContent = '';   // un solo aviso a la vez

    var aviso = document.createElement('div');
    aviso.className = 'toast toast-' + tipo;
    aviso.setAttribute('role', tipo === 'error' ? 'alert' : 'status');

    var contenido = document.createElement('span');
    contenido.className = 'toast-text';
    contenido.textContent = texto;

    var cerrar = document.createElement('button');
    cerrar.type = 'button';
    cerrar.className = 'toast-close';
    cerrar.setAttribute('aria-label', 'Cerrar aviso');
    cerrar.textContent = '×';
    cerrar.addEventListener('click', function () { aviso.remove(); });

    aviso.appendChild(contenido);
    aviso.appendChild(cerrar);
    region.appendChild(aviso);

    if (tipo === 'success') {
      setTimeout(function () { aviso.remove(); }, 6000);
    }
  }

  /* ---------- Refrescar lo que se ve en pantalla ---------- */
  function ponerTexto(selector, texto) {
    var elementos = document.querySelectorAll(selector);
    for (var i = 0; i < elementos.length; i++) {
      elementos[i].textContent = texto;
    }
  }

  function actualizarInterfaz(nombre, correo) {
    var inicial = (Array.from(nombre)[0] || '?').toUpperCase();
    var primerNombre = nombre.trim().split(/\s+/)[0] || '';

    ponerTexto('.profile-id-name', nombre);
    ponerTexto('.profile-id-mail', correo);
    ponerTexto('.profile-avatar', inicial);
    ponerTexto('.user-avatar', inicial);   // navbar
    ponerTexto('.user-name', primerNombre); // navbar

    form.elements['nombre'].value = nombre;
    form.elements['correo'].value = correo;
    form.elements['contrasena_actual'].value = '';
    form.elements['contrasena_nueva'].value = '';
    ocultarContrasenas(form);   // solo las de este formulario

    // Quitar avisos que el servidor dibujó en una carga anterior.
    var viejos = document.querySelectorAll('.profile-wrap > .alert');
    for (var i = 0; i < viejos.length; i++) viejos[i].remove();

    if (window.location.search) {
      history.replaceState(null, '', window.location.pathname);
    }
  }

  /* ---------- Envío ---------- */
  form.addEventListener('submit', function (evento) {

    var nombre = form.elements['nombre'].value.trim();
    var correo = form.elements['correo'].value.trim();
    var actual = form.elements['contrasena_actual'].value;
    var nueva = form.elements['contrasena_nueva'].value;

    if (
      nombre.length < 2 ||
      nombre.length > 50 ||
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo) ||
      correo.length > 100
    ) {
      mostrarAviso(
        'error',
        'Escribe un nombre de 2 a 50 caracteres y un correo válido.'
      );
      return;
    }

    if (!actual) {
      mostrarAviso(
        'error',
        'Escribe tu contraseña actual para confirmar los cambios.'
      );
      form.elements['contrasena_actual'].focus();
      return;
    }

    if (nueva !== '' && nueva.length < 8) {
      mostrarAviso(
        'error',
        'La nueva contraseña debe tener al menos 8 caracteres.'
      );
      form.elements['contrasena_nueva'].focus();
      return;
    }

    evento.preventDefault();
    if (enviando) return;

    enviando = true;
    var boton = form.querySelector('button[type="submit"]');
    var textoBoton = boton.textContent;
    boton.disabled = true;
    boton.textContent = 'Guardando…';
    form.setAttribute('aria-busy', 'true');

    fetch(form.getAttribute('action'), {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
      .then(function (respuesta) {
        return respuesta.text().then(function (texto) {
          try {
            return JSON.parse(texto);
          } catch (e) {
            // La respuesta no era JSON: se deja en la consola para poder ver qué llegó.
            console.error('Respuesta inesperada del servidor (HTTP ' + respuesta.status + '):', texto);
            return null;
          }
        });
      })
      .then(function (json) {
        if (json && json.ok) {
          actualizarInterfaz(json.nombre, json.correo);
          mostrarAviso('success', mensajes.exito || 'Tu perfil se actualizó correctamente.');
          return;
        }

        var codigo = (json && json.error) || 'actualizacion';
        mostrarAviso('error', textoError(codigo));

        if (codigo === 'contrasena') {
          form.elements['contrasena_actual'].focus();
          form.elements['contrasena_actual'].select();
        } else if (codigo === 'correo') {
          form.elements['correo'].focus();
        } else if (codigo === 'sesion') {
          setTimeout(function () {
            window.location.href = form.getAttribute('data-login');
          }, 1800);
        }
      })
      .catch(function () {
        mostrarAviso('error', textoError('red'));
      })
      .then(function () {
        enviando = false;
        boton.disabled = false;
        boton.textContent = textoBoton;
        form.removeAttribute('aria-busy');
      });
  });
})();