/**
 * Login and Registration Handler for NY Edu Center
 * Handles authentication and user registration
 */
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('registration-form');
    const loginTab = document.getElementById('login-tab');
    const registerTab = document.getElementById('register-tab');
    const loginContent = document.getElementById('login-content');
    const registerContent = document.getElementById('register-content');
    const errorMessage = document.getElementById('error-message');
    
    // Check for redirect parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('expired') === 'true') {
        showError('Your session has expired. Please log in again.');
    }
    
    if (urlParams.get('logout') === 'true') {
        showError('You have been successfully logged out.', 'success');
    }
    
    // Show error message
    function showError(message, type = 'error') {
        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
            
            if (type === 'success') {
                errorMessage.style.backgroundColor = 'rgba(52, 168, 83, 0.1)';
                errorMessage.style.color = '#34a853';
            } else {
                errorMessage.style.backgroundColor = 'rgba(234, 67, 53, 0.1)';
                errorMessage.style.color = '#ea4335';
            }
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }
    }
    
    // Toggle between login and registration forms
    if (loginTab && registerTab) {
        loginTab.addEventListener('click', function() {
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            loginContent.style.display = 'block';
            registerContent.style.display = 'none';
        });
        
        registerTab.addEventListener('click', function() {
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            registerContent.style.display = 'block';
            loginContent.style.display = 'none';
        });
    }
    
    // Login form submission
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                if (!username) showError('Please enter your username.');
                if (!password) showError('Please enter your password.');
                return;
            }
            
            try {
                console.log('Attempting login for user:', username);
                
                // Show loading state
                const submitButton = loginForm.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                submitButton.textContent = 'Logging in...';
                submitButton.disabled = true;
                
                // Try the main login endpoint first
                let response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });
                
                // If main endpoint fails, try debug endpoint
                if (!response.ok) {
                    console.log('Main login endpoint failed, trying debug endpoint...');
                    response = await fetch('/debug/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ username, password })
                    });
                }
                
                const data = await response.json();
                
                // Reset button
                submitButton.textContent = originalText;
                submitButton.disabled = false;
                
                if (!response.ok) {
                    console.error('Login failed:', data.error);
                    showError(data.error || 'Failed to log in. Please try again.');
                    return;
                }
                
                console.log('Login successful:', data);
                console.log('User role:', data.user.role);
                console.log('User grade:', data.user.grade);
                
                // Store token and user info
                localStorage.setItem('token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Initialize grades
                console.log('Initializing grades...');
                initializeUserGrades({ ...data.user, token: data.token });
                
                // Redirect based on role
                console.log('Redirecting user...');
                redirectUserBasedOnRole(data.user.role, data.user.grade);
                
            } catch (error) {
                console.error('Login error:', error);
                showError('An unexpected error occurred. Please try again.');
            }
        });
    }
    
    // Registration form submission
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const fullName = document.getElementById('reg-fullName').value;
            const username = document.getElementById('reg-username').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-password').value;
            const role = document.getElementById('role').value;
            const gradeSelect = document.getElementById('grade');
            const grade = gradeSelect ? gradeSelect.value : '';
            
            console.log('Registration attempt:', { fullName, username, email, role, grade });
            
            if (!fullName || !username || !email || !password || !role) {
                showError('Please fill in all required fields.');
                return;
            }
            
            try {
                // Show loading state
                const submitButton = registerForm.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                submitButton.textContent = 'Registering...';
                submitButton.disabled = true;
                
                // Format grade properly - extract just the number from "Grade X" format
                let formattedGrade = grade;
                if (formattedGrade && formattedGrade.includes('Grade ')) {
                    formattedGrade = formattedGrade.replace('Grade ', '');
                }
                
                // Create user data object
                const userData = { 
                    fullName, 
                    username, 
                    email, 
                    password, 
                    role,
                    grade: (role === 'student' || role === 'teacher') ? formattedGrade || '9' : null
                };
                
                console.log('Registering user with formatted data:', { ...userData, password: '[HIDDEN]' });
                
                // Try the main registration endpoint first
                let response = await fetch('/api/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(userData)
                });
                
                // If main endpoint fails, try debug endpoint
                if (!response.ok) {
                    console.log('Main registration endpoint failed, trying debug endpoint...');
                    response = await fetch('/debug/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(userData)
                    });
                }
                
                const data = await response.json();
                
                // Reset button
                submitButton.textContent = originalText;
                submitButton.disabled = false;
                
                if (!response.ok) {
                    showError(data.error || 'Failed to register. Please try again.');
                    return;
                }
                
                console.log('Registration successful:', data);
                
                // Store token and user info
                localStorage.setItem('token', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                
                // Initialize grades
                initializeUserGrades({ ...data.user, token: data.token });
                
                // Redirect based on role
                redirectUserBasedOnRole(data.user.role, data.user.grade);
                
            } catch (error) {
                console.error('Registration error:', error);
                showError('An unexpected error occurred. Please try again.');
            }
        });
    }
    
    // Redirect user based on role
    function redirectUserBasedOnRole(role, grade) {
        console.log('Redirecting user with role:', role, 'and grade:', grade);
        
        // Extract and normalize grade (remove 'Grade ' prefix if present)
        let userGrade = grade || '9';
        if (typeof userGrade === 'string' && userGrade.includes('Grade ')) {
            userGrade = userGrade.replace('Grade ', '');
        }
        console.log("Normalized grade for redirect:", userGrade);
        
        // Use absolute paths with leading slash
        switch(role) {
            case 'admin':
                window.location.href = '/admin/dashboard.html';
                break;
            case 'teacher':
                window.location.href = `/teachers/grade${userGrade}t/dashboard.html`;
                break;
            case 'student':
                window.location.href = `/students/grade${userGrade}s/dashboard.html`;
                break;
            default:
                window.location.href = '/index.html';
        }
    }
    
    // Password visibility toggle
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        const toggleButton = field.parentElement.querySelector('.toggle-password');
        if (toggleButton) {
            toggleButton.addEventListener('click', function() {
                const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
                field.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    });
});

// Add this function after login/registration success
function initializeUserGrades(user) {
  if (!user || !user.token) return;
  
  // Only initialize grades for teachers and students
  if (user.role !== 'teacher' && user.role !== 'student') return;
  
  // Make sure grade exists
  const grade = user.grade || '9';
  
  console.log(`Initializing grades for ${user.role} in grade ${grade}`);
  
  // Check if grades exist for this user
  fetch(`/api/grades/${user.role}/${grade}`, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${user.token}`,
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    console.log('Grades initialization check:', data);
    
    // If no grades exist, create initial empty grade entry
    if (!data.data || data.data.length === 0) {
      console.log('No grades found, creating initial entry');
      
      // Create a placeholder grade record to ensure file is properly set up
      const initialGrade = {
        studentId: user.id,
        courseId: 'system',
        value: 0,
        weight: 0,
        type: 'system',
        comment: 'Grade file initialization'
      };
      
      fetch(`/api/grades/${user.role}/${grade}`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${user.token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(initialGrade)
      })
      .then(response => response.json())
      .then(result => {
        console.log('Grade initialization successful:', result);
      })
      .catch(error => {
        console.error('Grade initialization error:', error);
      });
    }
  })
  .catch(error => {
    console.error('Error checking grades:', error);
  });
} 