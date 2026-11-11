// public/js/rubros.js

// Muestra un aviso con el estilo único de alerts.js (o alert() si no cargó).
function avisar(tipo, texto) {
    if (typeof mostrarAviso === 'function') {
        return mostrarAviso(tipo, texto);
    }
    console.error('mostrarAviso no existe: alerts.js está desactualizado o no cargó.');
    window.alert(texto);
}

// Límite del presupuesto: los rubros de un mes no pueden sumar más que el
// presupuesto mensual. El servidor es quien manda; esto solo avisa antes de enviar.
// Devuelve una función que pinta el aviso y dice si el formulario puede enviarse.
function configurarCupo(form) {
    if (!form || !form.dataset.cupo) {
        return () => true;
    }

    let cupo;
    try {
        cupo = JSON.parse(form.dataset.cupo);
    } catch (e) {
        return () => true;
    }

    const monto = form.querySelector('#monto_proyectado');
    const inicio = form.querySelector('#fecha_inicio');
    const fin = form.querySelector('#fecha_fin');
    const hint = form.querySelector('#cupoHint');

    const pesos = (n) => '$' + Math.round(n).toLocaleString('es-CO');
    const indiceMes = (f) => /^\d{4}-\d{2}-\d{2}$/.test(f)
        ? parseInt(f.slice(0, 4), 10) * 12 + parseInt(f.slice(5, 7), 10)
        : null;

    // Presupuesto menos lo que suman los otros rubros en el mes más cargado del periodo.
    function disponible(ini, finMes) {
        ini = Math.max(ini, cupo.mesActual);
        if (finMes < ini) {
            return Infinity;
        }

        const puntos = [ini];
        cupo.rubros.forEach((r) => {
            if (r.ini > ini && r.ini <= finMes) {
                puntos.push(r.ini);
            }
        });

        let maxOtros = 0;
        puntos.forEach((mes) => {
            let suma = 0;
            cupo.rubros.forEach((r) => {
                if (r.ini <= mes && mes <= r.fin) {
                    suma += r.monto;
                }
            });
            maxOtros = Math.max(maxOtros, suma);
        });

        return cupo.presupuesto - maxOtros;
    }

    function evaluar() {
        const ini = indiceMes(inicio.value);
        const finMes = indiceMes(fin.value);
        const valor = parseFloat(monto.value) || 0;

        if (ini === null || finMes === null || finMes < ini) {
            return { ok: true, texto: '', estado: '' };
        }

        // Dejar igual o bajar un rubro nunca empeora las cosas: se permite.
        const a = cupo.actual;
        const esReduccion = a && valor <= a.monto + 0.005 && ini >= a.ini && finMes <= a.fin;

        if (cupo.presupuesto === null) {
            return {
                ok: esReduccion,
                texto: 'Primero define tu presupuesto mensual en «Historial de Presupuesto» para poder asignar rubros.',
                estado: esReduccion ? '' : 'is-over'
            };
        }

        const disp = disponible(ini, finMes);

        if (disp === Infinity) {
            return { ok: true, texto: 'Ese periodo ya terminó: no afecta tu presupuesto de este mes.', estado: '' };
        }

        const libre = Math.max(0, disp);
        const cabe = esReduccion || valor <= disp + 0.005;

        if (!cabe) {
            return {
                ok: false,
                texto: 'Este monto supera tu presupuesto mensual de ' + pesos(cupo.presupuesto)
                    + '. Para ese periodo solo te quedan ' + pesos(libre) + ' por asignar.',
                estado: 'is-over'
            };
        }

        return {
            ok: true,
            texto: 'Disponible para asignar en ese periodo: ' + pesos(libre)
                + ' (presupuesto mensual: ' + pesos(cupo.presupuesto) + ').',
            estado: 'is-ok'
        };
    }

    function pintar() {
        const r = evaluar();
        if (hint) {
            hint.textContent = r.texto;
            hint.classList.toggle('is-over', r.estado === 'is-over');
            hint.classList.toggle('is-ok', r.estado === 'is-ok');
        }
        return r;
    }

    [monto, inicio, fin].forEach((campo) => {
        if (campo) {
            campo.addEventListener('input', pintar);
            campo.addEventListener('change', pintar);
        }
    });
    pintar();

    return function validar() {
        const r = pintar();
        if (!r.ok) {
            avisar('error', r.texto);
        }
        return r.ok;
    };
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#rubrosTable tbody tr');
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const editRubroForm = document.getElementById('editRubroForm');
    const validarCupoEdicion = configurarCupo(editRubroForm);

    // 1. Buscador en tiempo real
    if (searchInput) {
        searchInput.addEventListener('keyup', () => {
            const term = searchInput.value.toLowerCase().trim();

            tableRows.forEach(row => {
                if (row.classList.contains('empty-row')) return;

                const rowText = row.textContent.toLowerCase();

                if (rowText.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 2. Confirmación previa al ELIMINAR con SweetAlert2
    deleteButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const rubroName = button.getAttribute('data-name') || 'este rubro';
            const deleteUrl = button.getAttribute('href');

            const confirmed = await alerta(
                '¿Eliminar rubro?',
                `¿Estás seguro de que deseas eliminar "${rubroName}"? Solo se puede eliminar si no tiene movimientos asociados. Esta acción no se puede deshacer.`,
                'warning',
                'Sí, eliminar',
                'Cancelar'
            );

            if (confirmed && deleteUrl) {
                window.location.href = deleteUrl;
            }
        });
    });

    // 3. Confirmación al GUARDAR CAMBIOS en la edición mediante alerta()
    if (editRubroForm) {
        editRubroForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!validarCupoEdicion()) {
                return;
            }

            const nombreInput = document.getElementById('nombre');
            const nombreRubro = nombreInput ? nombreInput.value.trim() : 'este rubro';

            const confirmed = await alerta(
                '¿Guardar cambios?',
                `¿Confirmas que deseas actualizar los datos de "${nombreRubro}"?`,
                'question',
                'Sí, guardar',
                'Cancelar'
            );

            if (confirmed) {
                editRubroForm.submit();
            }
        });
    }

    // 3b. Confirmación al CREAR un rubro
    const createRubroForm = document.getElementById('createRubroForm');
    const validarCupoCreacion = configurarCupo(createRubroForm);
    if (createRubroForm) {
        createRubroForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nombre = createRubroForm.querySelector('#nombre').value.trim();
            const inicio = createRubroForm.querySelector('#fecha_inicio').value;
            const fin = createRubroForm.querySelector('#fecha_fin').value;

            if (nombre === '') {
                avisar('error', 'El nombre del rubro es obligatorio.');
                return;
            }
            if (fin < inicio) {
                avisar('error', 'La fecha fin no puede ser anterior a la fecha inicio.');
                return;
            }
            if (!validarCupoCreacion()) {
                return;
            }

            const confirmed = await alerta(
                '¿Crear rubro?',
                `¿Confirmas que deseas crear el rubro "${nombre}"?`,
                'question',
                'Sí, crear',
                'Cancelar'
            );

            if (confirmed) {
                createRubroForm.submit();
            }
        });
    }

    // 4. Procesar respuestas del servidor (?success=... / ?error=...)
    // Usa los mismos avisos que Categorías (alerts.js), sin colores propios.
    const urlParams = new URLSearchParams(window.location.search);
    const exito = urlParams.get('success');
    const mensajesExito = {
        created: 'El rubro se creó correctamente.',
        updated: 'Los cambios del rubro se guardaron correctamente.',
        deleted: 'El rubro se eliminó correctamente.'
    };

    if (urlParams.has('error')) {
        avisar('error', urlParams.get('error'));
        window.history.replaceState(null, '', window.location.pathname);
    } else if (mensajesExito[exito]) {
        avisar('ok', mensajesExito[exito]);
        window.history.replaceState(null, '', window.location.pathname);
    }
});