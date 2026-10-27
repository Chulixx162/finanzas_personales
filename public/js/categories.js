
 // public/js/categories.js

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#categoriesTable tbody tr');
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const editCategoryForm = document.getElementById('editCategoryForm');

    // Filtro interactivo en tiempo real
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();

            tableRows.forEach(row => {
                if (row.classList.contains('empty-row')) return;

                const name = row.querySelector('.category-name')?.textContent.toLowerCase() || '';
                const desc = row.children[1]?.textContent.toLowerCase() || '';

                if (name.includes(term) || desc.includes(term)) {
                    row.classList.remove('row-hidden');
                    row.classList.add('row-fade-in');
                } else {
                    row.classList.add('row-hidden');
                    row.classList.remove('row-fade-in');
                }
            });
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
                'Cancelar',
                '#d33',
                '#6c757d',
                '#ffffff',
                '#333333'
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

            const nombreInput = document.getElementById('nombre');
            const nombreCat = nombreInput
                ? nombreInput.value.trim()
                : 'esta categoría';

            const confirmed = await alerta(
                '¿Guardar cambios?',
                `¿Confirmas que deseas guardar los cambios de "${nombreCat}"?`,
                'question',
                'Sí, guardar',
                'Cancelar',
                '#3085d6',
                '#6c757d',
                '#ffffff',
                '#333333'
            );

            if (confirmed) {
                editCategoryForm.submit();
            }
        });
    }
});
