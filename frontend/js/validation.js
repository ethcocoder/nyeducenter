/**
 * Form validation utilities for NY Education Center
 */

// Generic validation functions
const validators = {
  required: (value) => value && value.trim() !== '',
  email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
  minLength: (value, length) => value && value.length >= length,
  maxLength: (value, length) => value && value.length <= length,
  number: (value) => !isNaN(parseFloat(value)) && isFinite(value),
  integer: (value) => Number.isInteger(Number(value)),
  match: (value, fieldId) => value === document.getElementById(fieldId).value,
  phone: (value) => /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/.test(value),
};

// Error messages
const errorMessages = {
  required: 'This field is required',
  email: 'Please enter a valid email address',
  minLength: (length) => `Please enter at least ${length} characters`,
  maxLength: (length) => `Please enter no more than ${length} characters`,
  number: 'Please enter a valid number',
  integer: 'Please enter a whole number',
  match: (field) => `Must match ${field}`,
  phone: 'Please enter a valid phone number',
};

/**
 * Validate a form field
 * @param {HTMLElement} field - The form field element
 * @param {Array} rules - Array of validation rules to apply
 * @returns {boolean} - Whether validation passed
 */
function validateField(field, rules) {
  // Clear existing error messages
  const errorElement = field.parentNode.querySelector('.error-message');
  if (errorElement) {
    errorElement.remove();
  }
  
  // Reset field styling
  field.classList.remove('error');
  
  // Apply validation rules
  for (const rule of rules) {
    let isValid = true;
    let message = '';
    
    if (typeof rule === 'string') {
      // Simple validation (e.g., 'required', 'email')
      isValid = validators[rule](field.value);
      message = errorMessages[rule];
    } else if (typeof rule === 'object') {
      // Complex validation with parameters (e.g., {type: 'minLength', param: 8})
      isValid = validators[rule.type](field.value, rule.param);
      message = typeof errorMessages[rule.type] === 'function' 
        ? errorMessages[rule.type](rule.param)
        : errorMessages[rule.type];
    }
    
    if (!isValid) {
      // Display error message
      field.classList.add('error');
      const errorDiv = document.createElement('div');
      errorDiv.className = 'error-message';
      errorDiv.textContent = message;
      field.parentNode.appendChild(errorDiv);
      return false;
    }
  }
  
  return true;
}

/**
 * Validate an entire form
 * @param {HTMLFormElement} form - The form element
 * @param {Object} validationRules - Object mapping field IDs to validation rules
 * @returns {boolean} - Whether all validations passed
 */
function validateForm(form, validationRules) {
  let isValid = true;
  
  for (const fieldId in validationRules) {
    const field = form.querySelector(`#${fieldId}`);
    if (field) {
      const fieldValid = validateField(field, validationRules[fieldId]);
      isValid = isValid && fieldValid;
    }
  }
  
  return isValid;
}

/**
 * Setup form validation on submit
 * @param {string} formId - ID of the form element
 * @param {Object} validationRules - Object mapping field IDs to validation rules
 * @param {Function} onSuccess - Callback for successful validation
 */
function setupFormValidation(formId, validationRules, onSuccess) {
  const form = document.getElementById(formId);
  if (!form) return;
  
  form.addEventListener('submit', function(event) {
    event.preventDefault();
    
    if (validateForm(form, validationRules)) {
      // If validation passes, call success callback
      if (typeof onSuccess === 'function') {
        onSuccess(form);
      }
    }
  });
  
  // Setup real-time validation on blur
  for (const fieldId in validationRules) {
    const field = form.querySelector(`#${fieldId}`);
    if (field) {
      field.addEventListener('blur', function() {
        validateField(field, validationRules[fieldId]);
      });
    }
  }
}

// Login form validation
document.addEventListener('DOMContentLoaded', function() {
  setupFormValidation('loginForm', {
    'username': ['required'],
    'password': ['required']
  }, function(form) {
    // Form submission logic
    const formData = new FormData(form);
    const loginData = {
      username: formData.get('username'),
      password: formData.get('password')
    };
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Logging in...';
    
    // Send login request to API
    fetch('/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(loginData)
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw new Error(data.message || 'Login failed');
        });
      }
      return response.json();
    })
    .then(data => {
      // Store JWT token
      localStorage.setItem('token', data.token);
      
      // Redirect based on user role
      if (data.user.role === 'teacher') {
        window.location.href = '/frontend/template/teachers/dashboard.html';
      } else if (data.user.role === 'student') {
        window.location.href = '/frontend/template/students/dashboard.html';
      } else {
        window.location.href = '/frontend/dashboard.html';
      }
    })
    .catch(error => {
      // Display error message
      const errorContainer = document.getElementById('login-error');
      if (errorContainer) {
        errorContainer.textContent = error.message;
        errorContainer.style.display = 'block';
      }
      
      // Reset button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    });
  });
  
  // Registration form validation
  setupFormValidation('registerForm', {
    'fullName': ['required'],
    'username': ['required', { type: 'minLength', param: 4 }],
    'email': ['required', 'email'],
    'password': ['required', { type: 'minLength', param: 8 }],
    'confirmPassword': ['required', { type: 'match', param: 'password' }]
  }, function(form) {
    // Form submission logic for registration
    const formData = new FormData(form);
    const registrationData = {
      fullName: formData.get('fullName'),
      username: formData.get('username'),
      email: formData.get('email'),
      password: formData.get('password'),
      role: formData.get('role') || 'student'
    };
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating account...';
    
    // Send registration request to API
    fetch('/api/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(registrationData)
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw new Error(data.message || 'Registration failed');
        });
      }
      return response.json();
    })
    .then(data => {
      // Store JWT token
      localStorage.setItem('token', data.token);
      
      // Redirect to success page or dashboard
      window.location.href = '/frontend/registration-success.html';
    })
    .catch(error => {
      // Display error message
      const errorContainer = document.getElementById('register-error');
      if (errorContainer) {
        errorContainer.textContent = error.message;
        errorContainer.style.display = 'block';
      }
      
      // Reset button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    });
  });
}); 