// public/js/categories.js

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#categoriesTable tbody tr');
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const editCategoryForm = document.getElementById('editCategoryForm');
    const createCategoryForm = document.getElementById('createCategoryForm');
    const avisoData = document.getElementById('avisoData');

    // Muestra un aviso con SweetAlert (o alert() si el JS está desactualizado).
    function avisar(tipo, texto) {
        if (typeof mostrarAviso === 'function') {
            return mostrarAviso(tipo, texto);
        }
        console.error('mostrarAviso no existe: alerts.js está desactualizado o no cargó.');
        window.alert(texto);
    }

    // Aviso que envía el servidor (?aviso=...)
    if (avisoData) {
        avisar(avisoData.dataset.tipo, avisoData.dataset.texto);

        // Quitar ?aviso= de la URL para que no se repita al recargar
        const url = new URL(window.location.href);
        url.searchParams.delete('aviso');
        window.history.replaceState({}, '', url);
    }

    // Validación de campos obligatorios y longitud.
    // Devuelve el mensaje de error, o '' si todo está bien.
    function mensajeValidacion(form) {
        const nombre      = form.querySelector('#nombre');
        const descripcion = form.querySelector('#descripcion');

        nombre.value      = nombre.value.trim();
        descripcion.value = descripcion.value.trim();

        if (nombre.value === '') {
            return 'El nombre de la categoría es obligatorio.';
        }
        if (nombre.value.length > 50) {
            return 'El nombre no puede superar los 50 caracteres.';
        }
        if (descripcion.value.length > 255) {
            return 'La descripción no puede superar los 255 caracteres.';
        }
        return '';
    }

    if (createCategoryForm) {
        createCategoryForm.addEventListener('submit', (e) => {
            const mensaje = mensajeValidacion(createCategoryForm);

            if (mensaje) {
                e.preventDefault();
                avisar('error', mensaje);
            }
        });
    }

    // Filtro interactivo en tiempo real
    // Quita tildes y mayúsculas para que "alimentacion" encuentre "Alimentación".
    const normalizar = (texto) => texto
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    if (searchInput) {
        const tbody = document.querySelector('#categoriesTable tbody');
        const hayCategorias = Array.from(tableRows).some(row => !row.classList.contains('empty-row'));

        // Fila que se muestra cuando ninguna categoría coincide.
        const filaSinResultados = document.createElement('tr');
        filaSinResultados.className = 'empty-row';
        filaSinResultados.style.display = 'none';
        filaSinResultados.innerHTML = '<td colspan="3">No se encontraron categorías con ese texto.</td>';
        if (tbody && hayCategorias) {
            tbody.appendChild(filaSinResultados);
        }

        searchInput.addEventListener('input', (e) => {
            const term = normalizar(e.target.value);
            let visibles = 0;

            tableRows.forEach(row => {
                if (row.classList.contains('empty-row')) return;

                const name = normalizar(row.querySelector('.category-name')?.textContent || '');
                const desc = normalizar(row.children[1]?.textContent || '');
                const coincide = name.includes(term) || desc.includes(term);

                // display inline: funciona aunque el CSS esté en caché
                row.style.display = coincide ? '' : 'none';
                if (coincide) visibles++;
            });

            filaSinResultados.style.display = visibles > 0 ? 'none' : '';
        });
    }

    // Confirmación al eliminar
    deleteButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const categoryName = button.getAttribute('data-name') || 'esta categoría';

            const confirmed = await alerta(
                '¿Eliminar categoría?',
                `¿Estás seguro de que deseas eliminar "${categoryName}"?`,
                'warning',
                'Sí, eliminar',
                'Cancelar'
            );

            if (confirmed) {
                window.location.href = button.href;
            }
        });
    });

    // Confirmación al guardar cambios
    if (editCategoryForm) {
        editCategoryForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const mensaje = mensajeValidacion(editCategoryForm);
            if (mensaje) {
                avisar('error', mensaje);
                return;
            }

            const nombreInput = document.getElementById('nombre');
            const nombreCat = nombreInput
                ? nombreInput.value.trim()
                : 'esta categoría';

            const confirmed = await alerta(
                '¿Guardar cambios?',
                `¿Confirmas que deseas guardar los cambios de "${nombreCat}"?`,
                'question',
                'Sí, guardar',
                'Cancelar'
            );

            if (confirmed) {
                editCategoryForm.submit();
            }
        });
    }
});