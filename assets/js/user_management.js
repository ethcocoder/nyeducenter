let addUserModal;
let editUserModal;

document.addEventListener('DOMContentLoaded', function() {
        const usersTableBody = document.getElementById('usersTableBody');

        if (usersTableBody) {
            usersTableBody.addEventListener('click', function(event) {
                const target = event.target.closest('.edit-user-btn, .delete-user-btn');
                if (target) {
                    const userId = target.dataset.id;
                    if (target.classList.contains('edit-user-btn')) {
                        editUser(userId);
                    } else if (target.classList.contains('delete-user-btn')) {
                        deleteUser(userId);
                    }
                }
            });
        }
    addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
    editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
});

function showAddUserModal() {
    addUserModal.show();
}

function editUser(userId) {
    fetch(`../../api/admin-get-user.php?id=${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('editUserId').value = data.user.id;
                document.getElementById('editUsername').value = data.user.username;
                document.getElementById('editEmail').value = data.user.email;
                document.getElementById('editRole').value = data.user.role;
                editUserModal.show();
            } else {
                Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to fetch user data: ' + (data.message || 'Unknown error')
            });
            }
        })
        .catch(error => {
            console.error('Error fetching user data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while fetching user data.'
            });
        });
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('../../api/admin-delete-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
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
                text: 'User deleted successfully',
                showConfirmButton: false,
                timer: 1500
            });
                loadUsers(1, 10); // Reload users after deletion
            } else {
                Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to delete user: ' + (data.message || 'Unknown error')
            });
            }
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while deleting the user.'
            });
        });
    }
}

function loadUsers(page = 1, limit = 10) {
    const usersTableBody = document.getElementById('usersTableBody');

    if (usersTableBody) {
        usersTableBody.innerHTML = ''; // Clear table body while loading
    }

    fetch(`../../api/admin-get-users.php?page=${page}&limit=${limit}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            usersTableBody.innerHTML = '';
            data.users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>
                        <span class="badge ${user.role === 'admin' ? 'bg-danger' : 'bg-primary'}">
                            ${user.role}
                        </span>
                    </td>
                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-primary edit-user-btn" data-id="${user.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-user-btn" data-id="${user.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                usersTableBody.appendChild(row);
            });
            renderPagination(data.totalPages, data.currentPage, 'usersPagination', loadUsers);
        })
        .catch(error => {
            console.error('Error loading users:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while loading users.'
            });
        });
}

function validateUserForm(formId) {
    const form = document.getElementById(formId);
    const username = form.querySelector('[name="username"]');
    const email = form.querySelector('[name="email"]');
    const password = form.querySelector('[name="password"]'); // Only for addUserForm
    const role = form.querySelector('[name="role"]');
    let isValid = true;

    // Clear previous errors
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    if (!username.value.trim()) {
        isValid = false;
        username.classList.add('is-invalid');
        username.parentNode.insertAdjacentHTML('beforeend', '<div class="invalid-feedback">Username is required.</div>');
    }

    if (!email.value.trim()) {
        isValid = false;
        email.classList.add('is-invalid');
        email.parentNode.insertAdjacentHTML('beforeend', '<div class="invalid-feedback">Email is required.</div>');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        isValid = false;
        email.classList.add('is-invalid');
        email.parentNode.insertAdjacentHTML('beforeend', '<div class="invalid-feedback">Please enter a valid email address.</div>');
    }

    if (password && !password.value.trim()) { // Password is required only for add user
        isValid = false;
        password.classList.add('is-invalid');
        password.parentNode.insertAdjacentHTML('beforeend', '<div class="invalid-feedback">Password is required.</div>');
    } else if (password && password.value.trim().length < 6) {
        isValid = false;
        password.classList.add('is-invalid');
        password.parentNode.insertAdjacentHTML('beforeend', '<div class="invalid-feedback">Password must be at least 6 characters long.</div>');
    }

    if (!role.value.trim()) {
        isValid = false;
        role.classList.add('is-invalid');
        role.parentNode.insertAdjacentHTML('beforeend', '<div class="invalid-feedback">Role is required.</div>');
    }

    return isValid;
}


function renderPagination(totalPages, currentPage, paginationElementId, loadFunction) {
    const paginationElement = document.getElementById(paginationElementId);
    if (!paginationElement) {
        console.error(`Pagination element with ID ${paginationElementId} not found.`);
        return;
    }

    paginationElement.innerHTML = ''; // Clear existing pagination

    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center';

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
    prevLi.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage > 1) {
            loadFunction(currentPage - 1);
        }
    });
    ul.appendChild(prevLi);

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        li.addEventListener('click', (e) => {
            e.preventDefault();
            loadFunction(i);
        });
        ul.appendChild(li);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
    nextLi.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage < totalPages) {
            loadFunction(currentPage + 1);
        }
    });
    ul.appendChild(nextLi);

    paginationElement.appendChild(ul);
}