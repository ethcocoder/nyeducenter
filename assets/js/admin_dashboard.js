document.addEventListener('DOMContentLoaded', function () {

    // ===============================
    // Helper Functions
    // ===============================

    function showLoading(button, text = 'Processing...') {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`;
    }

    function hideLoading(button) {
        button.disabled = false;
        if (button.dataset.originalText) button.innerHTML = button.dataset.originalText;
    }

    function handleForm(formId, apiUrl, isFormData = false, successCallback) {
        const form = document.getElementById(formId);
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitButton = form.querySelector('button[type="submit"]');
            showLoading(submitButton, 'Uploading...');

            let body;
            let headers = {};

            if (isFormData) {
                body = new FormData(form);
            } else {
                body = JSON.stringify(Object.fromEntries(new FormData(form).entries()));
                headers['Content-Type'] = 'application/json';
            }

            fetch(apiUrl, { method: 'POST', headers, body })
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message || 'Operation completed!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        if (successCallback) successCallback();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'An error occurred!',
                        });
                    }
                })
                .catch(err => {
                    console.error(`${formId} error:`, err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again.'
                    });
                })
                .finally(() => hideLoading(submitButton));
        });
    }

    function populateSelect(selectId, items, valueKey, textKey) {
        const select = document.getElementById(selectId);
        select.innerHTML = '';
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = item[textKey];
            select.appendChild(option);
        });
    }

    function attachEventListener(selector, event, callback) {
        document.querySelectorAll(selector).forEach(el => el.addEventListener(event, callback));
    }

    // ===============================
    // Load Functions
    // ===============================

    function loadCategories(callback) {
        const loadingIndicator = document.getElementById('categoriesTableLoadingIndicator');
        if (loadingIndicator) loadingIndicator.style.display = 'flex';

        fetch('../../api/admin-get-categories.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('categoriesTableBody');
                tbody.innerHTML = '';

                if (data.success && data.categories) {
                    data.categories.forEach(cat => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${cat.id}</td>
                                <td>${cat.grade_name}</td>
                                <td>${cat.description}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-category-btn" data-id="${cat.id}" data-name="${cat.grade_name}" data-description="${cat.description}">Edit</button>
                                    <button class="btn btn-sm btn-danger delete-category-btn" data-id="${cat.id}">Delete</button>
                                </td>
                            </tr>`;
                    });
                    attachCategoryEventListeners();
                    if (callback) callback(data.categories);
                } else {
                    tbody.innerHTML = '<tr><td colspan="4">No categories available.</td></tr>';
                }
            })
            .catch(err => console.error('Error loading categories:', err))
            .finally(() => {
                if (loadingIndicator) loadingIndicator.style.display = 'none';
            });
    }

    function loadBooks() {
        fetch('../../api/admin-get-books.php')
            .then(res => {
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(data => {
                const tbody = document.getElementById('booksTableBody');
                tbody.innerHTML = '';

                if (data.success && data.books) {
                    data.books.forEach(book => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${book.id}</td>
                                <td>${book.title}</td>
                                <td>${book.author}</td>
                                <td>${book.grade_name}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-book-btn" data-book='${JSON.stringify(book)}'>Edit</button>
                                    <button class="btn btn-sm btn-danger delete-book-btn" data-id="${book.id}">Delete</button>
                                </td>
                            </tr>`;
                    });
                    attachBookEventListeners();
                } else {
                    tbody.innerHTML = '<tr><td colspan="5">No books available.</td></tr>';
                }
            })
            .catch(err => console.error('Error loading books:', err));
    }

    function loadCategoriesIntoBookModals() {
        fetch('../../api/admin-get-categories.php')
            .then(res => res.json())
            .then(data => {
                if (data.categories) {
                    populateSelect('bookCategory', data.categories, 'id', 'grade_name');
                    populateSelect('editBookCategory', data.categories, 'id', 'grade_name');
                }
            })
            .catch(err => console.error('Error loading categories into book modals:', err));
    }

    function loadRecentActivity(page = 1, limit = 10) {
        fetch(`../../api/admin-get-activity.php?page=${page}&limit=${limit}`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('activityTableBody');
                tbody.innerHTML = '';
                if (data.activities) {
                    data.activities.forEach(act => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${act.timestamp}</td>
                                <td>${act.username}</td>
                                <td>${act.action}</td>
                                <td>${act.book_title}</td>
                            </tr>`;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="4">No activity available.</td></tr>';
                }
            })
            .catch(err => console.error('Error loading recent activity:', err));
    }

    // ===============================
    // Event Handlers
    // ===============================

    // Forms
    handleForm('editUserForm', '../../api/admin-update-user.php', false, () => {
        bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
        loadUsers();
    });

    handleForm('addUserForm', '../../api/add-user.php', false, () => {
        bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
        loadUsers();
    });

    handleForm('editCategoryForm', '../../api/admin-update-category.php', false, () => {
        bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
        loadCategories();
        loadCategoriesIntoBookModals();
    });

    handleForm('addCategoryForm', '../../api/add-category.php', false, () => {
        bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
        loadCategories();
        loadCategoriesIntoBookModals();
    });

    handleForm('editBookForm', '../../api/admin-update-book.php', false, () => {
        bootstrap.Modal.getInstance(document.getElementById('editBookModal')).hide();
        loadBooks();
    });

    handleForm('addBookForm', '../../api/add-book.php', true, () => {
        bootstrap.Modal.getInstance(document.getElementById('addBookModal')).hide();
        loadBooks();
    });

    // Edit Book Modal
    window.populateEditBookModal = function (book) {
        document.getElementById('editBookId').value = book.id;
        document.getElementById('editBookTitle').value = book.title;
        document.getElementById('editBookAuthor').value = book.author;
        document.getElementById('editBookCategory').value = book.category_id;
        document.getElementById('editBookDescription').value = book.description;
        $('#editBookModal').modal('show');
    };

    // Attach buttons
    function attachBookEventListeners() {
        attachEventListener('.edit-book-btn', 'click', function () {
            const book = JSON.parse(this.dataset.book);
            populateEditBookModal(book);
        });
        attachEventListener('.delete-book-btn', 'click', function () {
            const bookId = this.dataset.id;
            if (confirm('Are you sure you want to delete this book?')) {
                deleteBook(bookId);
            }
        });
    }

    function attachCategoryEventListeners() {
        attachEventListener('.edit-category-btn', 'click', function () {
            populateEditCategoryModal(this.dataset.id, this.dataset.name, this.dataset.description);
        });
        attachEventListener('.delete-category-btn', 'click', function () {
            const id = this.dataset.id;
            if (confirm('Are you sure you want to delete this category?')) deleteCategory(id);
        });
    }

    // ===============================
    // Initial Loads
    // ===============================
    loadCategories(loadCategoriesIntoBookModals);
    loadBooks();
    loadRecentActivity();

});
