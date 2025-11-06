let addCategoryModal;
let editCategoryModal;

document.addEventListener('DOMContentLoaded', function() {
        const categoriesTableBody = document.getElementById('categoriesTableBody');

        if (categoriesTableBody) {
            categoriesTableBody.addEventListener('click', function(event) {
                const target = event.target.closest('.edit-category-btn, .delete-category-btn');
                if (target) {
                    const categoryId = target.dataset.id;
                    if (target.classList.contains('edit-category-btn')) {
                        editCategory(categoryId);
                    } else if (target.classList.contains('delete-category-btn')) {
                        deleteCategory(categoryId);
                    }
                }
            });
        }
    addCategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
    editCategoryModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
});

function showAddCategoryModal() {
    addCategoryModal.show();
}

function editCategory(categoryId) {
    fetch(`../../api/admin-get-category.php?id=${categoryId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('editCategoryId').value = data.category.id;
                document.getElementById('editCategoryName').value = data.category.grade_name;
                document.getElementById('editCategoryDescription').value = data.category.description;
                editCategoryModal.show();
            } else {
                Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to fetch category data: ' + (data.message || 'Unknown error')
            });
            }
        })
        .catch(error => {
            console.error('Error fetching category data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while fetching category data.'
            });
        });
}

function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this category?')) {
        fetch('../../api/admin-delete-category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ category_id: categoryId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Category deleted successfully',
                showConfirmButton: false,
                timer: 1500
            });
                loadCategories(); // Reload categories after deletion
            } else {
                Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to delete category: ' + (data.message || 'Unknown error')
            });
            }
        })
        .catch(error => {
            console.error('Error deleting category:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while deleting the category.'
            });
        });
    }
}

function loadCategories() {
    fetch('../../api/admin-get-categories.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('categoriesTableBody');
            tbody.innerHTML = '';
            
            if (data.success && data.categories && Array.isArray(data.categories) && data.categories.length > 0) {
                data.categories.forEach(category => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${category.id}</td>
                        <td>${category.grade_name}</td>
                        <td>${category.description || 'N/A'}</td>
                        <td>${category.book_count}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary edit-category-btn" data-id="${category.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-category-btn" data-id="${category.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5">No categories available.</td></tr>';
                console.error('API returned an error or no categories:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
}

function validateCategoryForm(formId) {
    const form = document.getElementById(formId);
    const categoryName = form.querySelector('[name="grade_name"]');

    if (!categoryName.value.trim()) {
        Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Category Name is required.'
            });
        categoryName.focus();
        return false;
    }
    return true;
}