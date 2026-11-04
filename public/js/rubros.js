// public/js/rubros.js

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#rubrosTable tbody tr');
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const editRubroForm = document.getElementById('editRubroForm');

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