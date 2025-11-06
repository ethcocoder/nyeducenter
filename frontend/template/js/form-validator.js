// Simple form validation functions for the educational center application

// Initialize validators once DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Form validator loaded');
});

// Function to validate email format
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Function to validate minimum password length
function isValidPassword(password, minLength = 8) {
    return password && password.length >= minLength;
}

// Function to validate required fields
function validateRequired(value, fieldName) {
    if (!value || value.trim() === '') {
        return {
            valid: false,
            message: `${fieldName} is required`
        };
    }
    return {
        valid: true
    };
}

// Function to show error message
function showError(message, element) {
    if (!element) return;
    
    // If element is already an error element
    if (element.classList.contains('invalid-feedback')) {
        element.textContent = message;
        element.style.display = 'block';
        return;
    }
    
    // If element has a nextElementSibling that's an error element
    if (element.nextElementSibling && element.nextElementSibling.classList.contains('invalid-feedback')) {
        const feedback = element.nextElementSibling;
        feedback.textContent = message;
        feedback.style.display = 'block';
        return;
    }
    
    // If element is an error container
    if (element.id === 'error-message') {
        element.textContent = message;
        element.style.display = 'block';
        return;
    }
    
    // Default fallback
    const errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    } else {
        console.error('Error:', message);
    }
}

// Function to clear all form errors
function clearErrors() {
    const errorElements = document.querySelectorAll('.invalid-feedback');
    const errorMessage = document.getElementById('error-message');
    
    if (errorMessage) {
        errorMessage.style.display = 'none';
        errorMessage.textContent = '';
    }
    
    errorElements.forEach(element => {
        element.textContent = '';
        element.style.display = 'none';
    });
}

// Function to validate a login form
function validateLoginForm(form) {
    clearErrors();
    
    const username = form.querySelector('#username').value;
    const password = form.querySelector('#password').value;
    
    let isValid = true;
    
    // Validate username
    if (!username) {
        showError('Please enter your username', form.querySelector('#username'));
        isValid = false;
    }
    
    // Validate password
    if (!password) {
        showError('Please enter your password', form.querySelector('#password'));
        isValid = false;
    }
    
    return isValid;
}

// Function to validate a registration form
function validateRegistrationForm(form) {
    clearErrors();
    
    const fullName = form.querySelector('#reg-fullName').value;
    const username = form.querySelector('#reg-username').value;
    const email = form.querySelector('#reg-email').value;
    const password = form.querySelector('#reg-password').value;
    const role = form.querySelector('#role').value;
    const gradeSelect = form.querySelector('#grade');
    const grade = gradeSelect ? gradeSelect.value : '';
    
    let isValid = true;
    
    // Validate required fields
    if (!fullName) {
        showError('Please enter your full name', form.querySelector('#reg-fullName'));
        isValid = false;
    }
    
    if (!username) {
        showError('Please enter a username', form.querySelector('#reg-username'));
        isValid = false;
    }
    
    if (!email) {
        showError('Please enter an email address', form.querySelector('#reg-email'));
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError('Please enter a valid email address', form.querySelector('#reg-email'));
        isValid = false;
    }
    
    if (!password) {
        showError('Please enter a password', form.querySelector('#reg-password'));
        isValid = false;
    } else if (!isValidPassword(password, 6)) {
        showError('Password must be at least 6 characters', form.querySelector('#reg-password'));
        isValid = false;
    }
    
    if (!role) {
        showError('Please select a role', form.querySelector('#role'));
        isValid = false;
    }
    
    // Validate grade for students
    if (role === 'student' && (!grade || grade === '')) {
        showError('Please select a grade', form.querySelector('#grade'));
        isValid = false;
    }
    
    return isValid;
} 