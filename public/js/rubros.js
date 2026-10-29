// public/js/rubros.js

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#rubrosTable tbody tr');
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const editRubroForm = document.getElementById('editRubroForm');

    // 1. Buscador en tiempo real sobre todo el texto de cada fila
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

    // 2. Confirmación al ELIMINAR usando el helper alerta()
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

    // 3. Confirmación al EDITAR
    if (editRubroForm) {
        editRubroForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nombreInput = document.getElementById('nombre');
            const nombreRubro = nombreInput ? nombreInput.value.trim() : 'este rubro';

            const confirmed = await alerta(
                '¿Guardar cambios?',
                `¿Confirmas que deseas guardar los cambios en "${nombreRubro}"?`,
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
});