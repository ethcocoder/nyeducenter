/**
 * Admin User Management
 * Handles CRUD operations for users (students, teachers, admins)
 */
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const userTable = document.getElementById('users-table');
    const userTableBody = document.getElementById('users-table-body');
    const addUserBtn = document.getElementById('add-user-btn');
    const userModal = document.getElementById('user-modal');
    const userForm = document.getElementById('user-form');
    const searchInput = document.getElementById('search-users');
    const roleFilter = document.getElementById('role-filter');
    const closeModalBtn = document.querySelector('.close-modal');
    const modalTitle = document.getElementById('modal-title');
    
    // State
    let users = [];
    let filteredUsers = [];
    let editingUserId = null;
    
    // Initialize validation
    const validator = new FormValidator(userForm, {
        validateOnInput: true,
        validateOnBlur: true,
        errorClass: 'is-invalid',
        successClass: 'is-valid'
    });
    
    // Add validation rules
    validator.addRule('username', FormValidator.rules.required, null, 'የተጠቃሚ ስም አስፈላጊ ነው');
    validator.addRule('firstName', FormValidator.rules.required, null, 'ስም አስፈላጊ ነው');
    validator.addRule('lastName', FormValidator.rules.required, null, 'የአባት ስም አስፈላጊ ነው');
    validator.addRule('email', FormValidator.rules.required, null, 'ኢሜይል አስፈላጊ ነው');
    validator.addRule('email', FormValidator.rules.email, null, 'እባክዎ ትክክለኛ ኢሜይል አድራሻ ያስገቡ');
    validator.addRule('password', FormValidator.rules.required, null, 'የይለፍ ቃል አስፈላጊ ነው');
    validator.addRule('password', FormValidator.rules.minLength, 6, 'የይለፍ ቃል ቢያንስ 6 ቁምፊዎች መሆን አለበት');
    validator.addRule('role', FormValidator.rules.required, null, 'እባክዎ ሚና ይምረጡ');
    
    // Optional grade validation for students and teachers
    const validateGrade = () => {
        const role = document.getElementById('role').value;
        const gradeField = document.getElementById('grade');
        
        if ((role === 'student' || role === 'teacher') && !gradeField.value) {
            validator.showError(gradeField, 'እባክዎ ክፍል ይምረጡ');
            return false;
        }
        
        validator.clearError(gradeField);
        return true;
    };

    // Show/hide the modal
    function toggleModal(show = true) {
        userModal.style.display = show ? 'block' : 'none';
    }

    // Close modal when clicking the close button
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => toggleModal(false));
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === userModal) {
            toggleModal(false);
        }
    });

    // Fetch users from API
    async function fetchUsers() {
        try {
            showLoading();
            
            const result = await api.get('/admin/users');
            hideLoading();
            
            if (!result.users) {
                showNotification('Failed to fetch users data', 'error');
                return;
            }
            
            users = result.users;
            filteredUsers = [...users];
            renderUsers();
        } catch (error) {
            hideLoading();
            console.error('Error fetching users:', error);
            showNotification('Failed to fetch users', 'error');
        }
    }

    // Render users table
    function renderUsers() {
        if (!userTableBody) return;
        
        userTableBody.innerHTML = '';
        
        if (filteredUsers.length === 0) {
            userTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center">ምንም ተጠቃሚዎች አልተገኙም</td>
                </tr>
            `;
            return;
        }
        
        filteredUsers.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.firstName} ${user.lastName}</td>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${user.phone || '-'}</td>
                <td>
                    <span class="badge ${getRoleBadgeClass(user.role)}">
                        ${getRoleTranslation(user.role)}
                    </span>
                </td>
                <td>${user.grade || '-'}</td>
                <td>
                    <span class="status-badge ${user.isActive ? 'active' : 'inactive'}">
                        ${user.isActive ? 'ንቁ' : 'የተዘጋ'}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon edit" data-id="${user.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon delete" data-id="${user.id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <button class="btn-icon toggle-status" data-id="${user.id}" data-active="${user.isActive}">
                            ${user.isActive 
                                ? '<i class="fas fa-ban"></i>' 
                                : '<i class="fas fa-check-circle"></i>'
                            }
                        </button>
                    </div>
                </td>
            `;
            
            userTableBody.appendChild(row);
        });
        
        // Add event listeners to action buttons
        document.querySelectorAll('.btn-icon.edit').forEach(btn => {
            btn.addEventListener('click', () => editUser(btn.dataset.id));
        });
        
        document.querySelectorAll('.btn-icon.delete').forEach(btn => {
            btn.addEventListener('click', () => deleteUser(btn.dataset.id));
        });
        
        document.querySelectorAll('.btn-icon.toggle-status').forEach(btn => {
            btn.addEventListener('click', () => toggleUserStatus(btn.dataset.id, btn.dataset.active === 'true'));
        });
    }

    // Helper functions for user rendering
    function getRoleBadgeClass(role) {
        switch(role) {
            case 'admin': return 'badge-red';
            case 'teacher': return 'badge-green';
            case 'student': return 'badge-blue';
            default: return 'badge-gray';
        }
    }
    
    function getRoleTranslation(role) {
        switch(role) {
            case 'admin': return 'አስተዳዳሪ';
            case 'teacher': return 'መምህር';
            case 'student': return 'ተማሪ';
            default: return role;
        }
    }

    // Filter users
    function filterUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        const roleValue = roleFilter.value;
        
        filteredUsers = users.filter(user => {
            // Role filter
            if (roleValue && user.role !== roleValue) {
                return false;
            }
            
            // Search filter
            if (searchTerm) {
                return (
                    user.firstName.toLowerCase().includes(searchTerm) ||
                    user.lastName.toLowerCase().includes(searchTerm) ||
                    user.username.toLowerCase().includes(searchTerm) ||
                    user.email.toLowerCase().includes(searchTerm) ||
                    (user.phone && user.phone.toLowerCase().includes(searchTerm))
                );
            }
            
            return true;
        });
        
        renderUsers();
    }

    // Add event listeners for filtering
    if (searchInput) {
        searchInput.addEventListener('input', filterUsers);
    }
    
    if (roleFilter) {
        roleFilter.addEventListener('change', filterUsers);
    }

    // Handle role change in form
    document.getElementById('role')?.addEventListener('change', function() {
        const gradeContainer = document.getElementById('grade-container');
        if (this.value === 'student' || this.value === 'teacher') {
            gradeContainer.style.display = 'block';
        } else {
            gradeContainer.style.display = 'none';
        }
    });

    // Open modal to add a new user
    function showAddUserModal() {
        editingUserId = null;
        modalTitle.textContent = 'አዲስ ተጠቃሚ ይጨምሩ';
        userForm.reset();
        
        // Default role selection
        const roleSelect = document.getElementById('role');
        if (roleSelect) {
            roleSelect.value = 'student';
            // Trigger change event to show/hide grade field
            roleSelect.dispatchEvent(new Event('change'));
        }
        
        // Show password field for new users
        const passwordContainer = document.getElementById('password-container');
        if (passwordContainer) {
            passwordContainer.style.display = 'block';
        }
        
        toggleModal(true);
    }

    // Edit existing user
    async function editUser(userId) {
        try {
            showLoading();
            
            const result = await api.get(`/admin/users/${userId}`);
            hideLoading();
            
            if (!result) {
                showNotification('ተጠቃሚውን ማግኘት አልተቻለም', 'error');
                return;
            }
            
            const user = result;
            editingUserId = userId;
            
            // Set form data from user
            userForm.username.value = user.username || '';
            userForm.firstName.value = user.firstName || '';
            userForm.lastName.value = user.lastName || '';
            userForm.email.value = user.email || '';
            userForm.phone.value = user.phone || '';
            userForm.password.value = ''; // Don't show password
            userForm.role.value = user.role || 'student';
            userForm.isActive.checked = user.isActive !== false; // Default to true if not specified
            
            // Handle grade for students and teachers
            if (user.role === 'student' || user.role === 'teacher') {
                document.getElementById('grade-container').style.display = 'block';
                userForm.grade.value = user.grade || '';
            } else {
                document.getElementById('grade-container').style.display = 'none';
                userForm.grade.value = '';
            }
            
            // Password is required for creation but not for editing
            validator.removeRule('password', FormValidator.rules.required);
            
            // Change modal title
            modalTitle.textContent = 'ተጠቃሚ አስተካክል';
            
            // Show modal
            toggleModal(true);
        } catch (error) {
            hideLoading();
            console.error('Error fetching user details:', error);
            showNotification('ተጠቃሚውን ማግኘት አልተቻለም', 'error');
        }
    }

    // Submit form handler
    userForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!validator.validateAll() || !validateGrade()) {
            return;
        }
        
        // Get form data
        const formData = {
            username: userForm.username.value,
            firstName: userForm.firstName.value,
            lastName: userForm.lastName.value,
            email: userForm.email.value,
            phone: userForm.phone.value || null,
            password: userForm.password.value,
            role: userForm.role.value,
            isActive: userForm.isActive.checked
        };
        
        // Add grade for students and teachers
        if (formData.role === 'student' || formData.role === 'teacher') {
            formData.grade = userForm.grade.value;
        }
        
        try {
            showLoading();
            
            if (editingUserId) {
                // Update existing user
                const result = await api.put(`/admin/users/${editingUserId}`, formData);
                hideLoading();
                
                if (result.user) {
                    showNotification('ተጠቃሚው በተሳካ ሁኔታ ተዘምኗል', 'success');
                    toggleModal(false);
                    await fetchUsers();
                } else {
                    showNotification('ተጠቃሚውን ማዘመን አልተቻለም', 'error');
                }
            } else {
                // Create new user
                const result = await api.post('/admin/users', formData);
                hideLoading();
                
                if (result.user) {
                    showNotification('ተጠቃሚው በተሳካ ሁኔታ ተፈጥሯል', 'success');
                    toggleModal(false);
                    await fetchUsers();
                } else {
                    showNotification('ተጠቃሚውን መፍጠር አልተቻለም', 'error');
                }
            }
        } catch (error) {
            hideLoading();
            console.error('Error saving user:', error);
            showNotification(error.message || 'ተጠቃሚውን ማስቀመጥ አልተቻለም', 'error');
        }
    });

    // Delete user
    async function deleteUser(userId) {
        if (!confirm('እርግጠኛ ነዎት ይህን ተጠቃሚ መሰረዝ ይፈልጋሉ?')) {
            return;
        }
        
        try {
            showLoading();
            
            const result = await api.delete(`/admin/users/${userId}`);
            hideLoading();
            
            if (result.message) {
                showNotification('ተጠቃሚው በተሳካ ሁኔታ ተሰርዟል', 'success');
                await fetchUsers();
            } else {
                showNotification('ተጠቃሚውን መሰረዝ አልተቻለም', 'error');
            }
        } catch (error) {
            hideLoading();
            console.error('Error deleting user:', error);
            showNotification('ተጠቃሚውን መሰረዝ አልተቻለም', 'error');
        }
    }

    // Toggle user active status
    async function toggleUserStatus(userId, isCurrentlyActive) {
        const newStatus = !isCurrentlyActive;
        const statusText = newStatus ? 'ንቁ' : 'የተዘጋ';
        
        try {
            showLoading();
            
            const result = await api.put(`/admin/users/${userId}`, { isActive: newStatus });
            hideLoading();
            
            if (result.user) {
                showNotification(`ተጠቃሚ ሁኔታ ወደ ${statusText} ተቀይሯል`, 'success');
                await fetchUsers();
            } else {
                showNotification('ተጠቃሚ ሁኔታን መቀየር አልተቻለም', 'error');
            }
        } catch (error) {
            hideLoading();
            console.error('Error toggling user status:', error);
            showNotification('ተጠቃሚ ሁኔታን መቀየር አልተቻለም', 'error');
        }
    }

    // Show notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="close-notification">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Add close button event listener
        notification.querySelector('.close-notification').addEventListener('click', () => {
            notification.remove();
        });
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 500);
        }, 5000);
    }

    // Helper for notification icons
    function getNotificationIcon(type) {
        switch(type) {
            case 'success': return 'fa-check-circle';
            case 'error': return 'fa-exclamation-circle';
            case 'warning': return 'fa-exclamation-triangle';
            default: return 'fa-info-circle';
        }
    }

    // Add event listener to Add User button
    if (addUserBtn) {
        addUserBtn.addEventListener('click', showAddUserModal);
    }

    // Helper function to show loading state
    function showLoading() {
        // Add loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = `
            <div class="spinner">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    }
    
    // Helper function to hide loading state
    function hideLoading() {
        // Remove loading overlay
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
            document.body.removeChild(loadingOverlay);
        }
    }

    // Initialize
    fetchUsers();
}); 