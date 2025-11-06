// Login functionality for the educational center application

document.addEventListener('DOMContentLoaded', function() {
    console.log('Login.js loaded');
    
    // Handle login form submission
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Handle registration form submission
    const registerForm = document.getElementById('registration-form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegistration);
    }
    
    // Handle tab switching
    const loginTab = document.getElementById('login-tab');
    const registerTab = document.getElementById('register-tab');
    const loginContent = document.getElementById('login-content');
    const registerContent = document.getElementById('register-content');
    
    if (loginTab && registerTab) {
        loginTab.addEventListener('click', function() {
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            if (loginContent) loginContent.style.display = 'block';
            if (registerContent) registerContent.style.display = 'none';
        });
        
        registerTab.addEventListener('click', function() {
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            if (registerContent) registerContent.style.display = 'block';
            if (loginContent) loginContent.style.display = 'none';
        });
    }
});

// Handle login form submission
async function handleLogin(e) {
    e.preventDefault();
    clearErrors();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    if (!username || !password) {
        if (!username) showError('Please enter your username', document.getElementById('username'));
        if (!password) showError('Please enter your password', document.getElementById('password'));
        return;
    }
    
    // Show loading state
    const submitButton = document.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Logging in...';
    submitButton.disabled = true;
    
    try {
        // Call the API using the api helper
        let data;
        try {
            data = await api.auth.login({ username, password });
        } catch (error) {
            console.error('Login failed:', error);
            showError('Login failed. Please check your credentials and try again.', document.getElementById('error-message'));
            submitButton.textContent = originalText;
            submitButton.disabled = false;
            return;
        }
        
        // Store token and user info
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        
        // Initialize grades
        initializeUserGrades({ ...data.user, token: data.token });
        
        // Debug info
        console.log("User role:", data.user.role);
        console.log("User grade:", data.user.grade);
        
        // Extract and normalize grade (remove 'Grade ' prefix if present)
        let userGrade = data.user.grade || '9';
        if (typeof userGrade === 'string' && userGrade.includes('Grade ')) {
            userGrade = userGrade.replace('Grade ', '');
        }
        console.log("Normalized grade:", userGrade);
        
        // Redirect based on role
        switch(data.user.role) {
            case 'admin':
                logAndRedirect('/admin/dashboard.html', data.user.role, userGrade);
                break;
            case 'teacher':
                // Include grade in URL for teachers with 't' suffix
                logAndRedirect(`/teachers/grade${userGrade}t/dashboard.html`, data.user.role, userGrade);
                break;
            case 'student':
                // Include grade in URL for students with 's' suffix
                logAndRedirect(`/students/grade${userGrade}s/dashboard.html`, data.user.role, userGrade);
                break;
            default:
                logAndRedirect('/index.html', data.user.role, userGrade);
        }
    } catch (error) {
        console.error('Login error:', error);
        submitButton.textContent = originalText;
        submitButton.disabled = false;
        showError('Connection to server failed. Please try again.', document.getElementById('error-message'));
    }
}

// Handle registration form submission
async function handleRegistration(e) {
    e.preventDefault();
    clearErrors();
    
    // Get form values
    const fullName = document.getElementById('reg-fullName').value;
    const username = document.getElementById('reg-username').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const role = document.getElementById('role').value;
    const gradeSelect = document.getElementById('grade');
    const grade = gradeSelect ? gradeSelect.value : '';
    
    // Validate form
    if (!validateRegistrationForm(document.getElementById('registration-form'))) {
        return;
    }
    
    // Prepare user data
    const userData = {
        fullName,
        username,
        email,
        password,
        role,
        grade: role === 'student' || role === 'teacher' ? 
               (grade || 'Grade 9').replace('Grade ', '') : null
    };
    
    // Show loading state
    const submitButton = document.querySelector('#registration-form button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Registering...';
    submitButton.disabled = true;
    
    try {
        // Call the registration API
        const data = await api.auth.register(userData);
        
        // Store token and user info
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        
        // Initialize grades
        initializeUserGrades({ ...data.user, token: data.token });
        
        // Debug info
        console.log("Registration success - User role:", data.user.role);
        console.log("Registration success - User grade:", data.user.grade);
        
        // Extract and normalize grade
        let userRegGrade = data.user.grade || '9';
        if (typeof userRegGrade === 'string' && userRegGrade.includes('Grade ')) {
            userRegGrade = userRegGrade.replace('Grade ', '');
        }
        console.log("Registration - Normalized grade:", userRegGrade);
        
        // Redirect based on role
        switch(data.user.role) {
            case 'admin':
                logAndRedirect('/admin/dashboard.html', data.user.role, userRegGrade);
                break;
            case 'teacher':
                logAndRedirect(`/teachers/grade${userRegGrade}t/dashboard.html`, data.user.role, userRegGrade);
                break;
            case 'student':
                logAndRedirect(`/students/grade${userRegGrade}s/dashboard.html`, data.user.role, userRegGrade);
                break;
            default:
                logAndRedirect('/index.html', data.user.role, userRegGrade);
        }
    } catch (error) {
        console.error('Registration error:', error);
        submitButton.textContent = originalText;
        submitButton.disabled = false;
        showError('Registration failed. Please try again.', document.getElementById('error-message'));
    }
} 