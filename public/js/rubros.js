// public/js/rubros.js

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
            mostrarAviso('error', r.texto);
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
    
    // 1. Buscador en tiempo real con mensaje cuando no hay resultados
    if (searchInput) {

        const tbody = document.querySelector('#rubrosTable tbody');

        // Excluir las filas de mensajes vacíos existentes
        const rows = Array.from(tableRows).filter(row =>
            !row.classList.contains('empty-row')
        );

        // Crear fila para mostrar cuando no existan coincidencias
        const emptySearchRow = document.createElement('tr');
        emptySearchRow.classList.add('empty-row');
        emptySearchRow.style.display = 'none';

        const emptyCell = document.createElement('td');
        emptyCell.colSpan =
            document.querySelectorAll('#rubrosTable thead th').length || 1;

        emptyCell.textContent =
            'No se encontraron rubros con ese texto';

        emptyCell.style.textAlign = 'center';
        emptyCell.style.padding = '25px';

        emptySearchRow.appendChild(emptyCell);

        if (tbody) {
            tbody.appendChild(emptySearchRow);
        }

        // Normalizar texto para ignorar mayúsculas y tildes
        function normalizarTexto(texto) {
            return texto
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();
        }

        // Ejecutar búsqueda
        function filtrarRubros() {

            const term = normalizarTexto(searchInput.value);

            let coincidencias = 0;

            rows.forEach(row => {

                const rowText = normalizarTexto(row.textContent);

                const coincide = rowText.includes(term);

                row.style.display = coincide ? '' : 'none';

                if (coincide) {
                    coincidencias++;
                }

            });

            // Mostrar mensaje solamente cuando hay texto
            // de búsqueda y no existen coincidencias
            emptySearchRow.style.display =
                term !== '' && coincidencias === 0 ? '' : 'none';
        }

        // Buscar mientras se escribe
        searchInput.addEventListener('input', filtrarRubros);
    }


    // 2. Confirmación previa al ELIMINAR con SweetAlert2
    deleteButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const rubroName = button.getAttribute('data-name') || 'este rubro';
            const deleteUrl = button.getAttribute('href');

            const confirmed = await alerta(
                '¿Eliminar rubro?',
                `¿Estás seguro de que deseas eliminar "${rubroName}"?`,
                'warning',
                'Sí, eliminar',
                'Cancelar',
                '#ef4444'
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
                'Cancelar',
                '#6366f1'
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
                mostrarAviso('error', 'El nombre del rubro es obligatorio.');
                return;
            }
            if (fin < inicio) {
                mostrarAviso('error', 'La fecha fin no puede ser anterior a la fecha inicio.');
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

    // 4. Procesar respuestas del servidor (Éxito o Error de URL)
    const urlParams = new URLSearchParams(window.location.search);
    const isLight = document.documentElement.getAttribute('data-theme') === 'light';
    const swalBg = isLight ? '#ffffff' : '#1e1e24';
    const swalColor = isLight ? '#0f172a' : '#ffffff';

    if (urlParams.has('error')) {
        Swal.fire({
            title: 'Acción denegada',
            text: urlParams.get('error'),
            icon: 'error',
            confirmButtonColor: '#ef4444',
            background: swalBg,
            color: swalColor
        });
        window.history.replaceState(null, '', window.location.pathname);
    } else if (urlParams.get('success') === 'deleted') {
        Swal.fire({
            title: '¡Eliminado!',
            text: 'El rubro ha sido eliminado correctamente.',
            icon: 'success',
            confirmButtonColor: '#10b981',
            background: swalBg,
            color: swalColor
        });
        window.history.replaceState(null, '', window.location.pathname);
    } else if (urlParams.get('success') === 'created') {
        mostrarAviso('ok', 'El rubro se creó correctamente.');
        window.history.replaceState(null, '', window.location.pathname);
    } else if (urlParams.get('success') === 'updated') {
        Swal.fire({
            title: '¡Actualizado!',
            text: 'Los cambios del rubro se han guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#10b981',
            background: swalBg,
            color: swalColor
        });
        window.history.replaceState(null, '', window.location.pathname);
    }
});