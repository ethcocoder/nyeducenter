/**
 * Form Validator for NY Edu Center
 * Handles form validation and error display
 */

class FormValidator {
    constructor(form, options = {}) {
        this.form = form;
        this.options = {
            errorClass: 'is-invalid',
            errorMessageClass: 'invalid-feedback',
            showErrorsImmediately: true,
            ...options
        };
        
        this.fields = {};
        this.valid = true;
        
        // Initialize validation on form submit
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!this.validateAll()) {
                    e.preventDefault();
                }
            });
        }
    }
    
    /**
     * Add a field to validate
     * @param {string|HTMLElement} field - Field ID or element
     * @param {Array} rules - Array of validation rules
     */
    addField(field, rules = []) {
        const fieldElement = typeof field === 'string' ? document.getElementById(field) : field;
        
        if (!fieldElement) {
            console.error(`Field not found: ${field}`);
            return;
        }
        
        this.fields[fieldElement.id] = {
            element: fieldElement,
            rules
        };
        
        // Add blur event for immediate validation if enabled
        if (this.options.showErrorsImmediately) {
            fieldElement.addEventListener('blur', () => {
                this.validateField(fieldElement);
            });
            
            // For select elements, validate on change
            if (fieldElement.tagName === 'SELECT') {
                fieldElement.addEventListener('change', () => {
                    this.validateField(fieldElement);
                });
            }
        }
    }
    
    /**
     * Validate a specific field
     * @param {string|HTMLElement} field - Field ID or element
     * @returns {boolean} Whether the field is valid
     */
    validateField(field) {
        const fieldElement = typeof field === 'string' ? document.getElementById(field) : field;
        
        if (!fieldElement || !this.fields[fieldElement.id]) {
            console.error(`Field not registered for validation: ${field}`);
            return false;
        }
        
        const fieldConfig = this.fields[fieldElement.id];
        let isValid = true;
        
        // Clear any existing errors
        this.clearError(fieldElement);
        
        // Run through all validation rules
        for (const rule of fieldConfig.rules) {
            const { type, message, ...params } = rule;
            
            if (!this.runValidation(type, fieldElement, params, message)) {
                isValid = false;
                break; // Stop at first error
            }
        }
        
        return isValid;
    }
    
    /**
     * Run a validation rule on a field
     * @param {string} type - Validation rule type
     * @param {HTMLElement} field - Field element
     * @param {object} params - Rule parameters
     * @param {string} message - Custom error message
     * @returns {boolean} Whether the field passed validation
     */
    runValidation(type, field, params, message) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = message || '';
        
        switch (type) {
            case 'required':
                isValid = value.length > 0;
                errorMessage = errorMessage || 'This field is required';
                break;
                
            case 'email':
                isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                errorMessage = errorMessage || 'Please enter a valid email address';
                break;
                
            case 'minLength':
                isValid = value.length >= params.min;
                errorMessage = errorMessage || `Minimum length is ${params.min} characters`;
                break;
                
            case 'maxLength':
                isValid = value.length <= params.max;
                errorMessage = errorMessage || `Maximum length is ${params.max} characters`;
                break;
                
            case 'pattern':
                isValid = params.regex.test(value);
                errorMessage = errorMessage || 'Please enter a valid value';
                break;
                
            case 'match':
                const matchField = document.getElementById(params.field);
                isValid = matchField && value === matchField.value.trim();
                errorMessage = errorMessage || `Must match ${params.field}`;
                break;
                
            case 'custom':
                isValid = params.validator(value, field);
                errorMessage = errorMessage || 'Invalid value';
                break;
                
            default:
                console.error(`Unknown validation type: ${type}`);
                break;
        }
        
        if (!isValid) {
            this.showError(field, errorMessage);
        }
        
        return isValid;
    }
    
    /**
     * Validate all fields in the form
     * @returns {boolean} Whether all fields are valid
     */
    validateAll() {
        let isValid = true;
        
        for (const fieldId in this.fields) {
            const fieldValid = this.validateField(fieldId);
            isValid = isValid && fieldValid;
        }
        
        return isValid;
    }
    
    /**
     * Show an error message for a field
     * @param {HTMLElement} field - Field element
     * @param {string} message - Error message
     */
    showError(field, message) {
        // Add error class to the field
        field.classList.add(this.options.errorClass);
        
        // Find or create error message element
        let errorElement = field.parentElement.querySelector(`.${this.options.errorMessageClass}`);
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = this.options.errorMessageClass;
            field.parentElement.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
    
    /**
     * Clear error for a field
     * @param {HTMLElement} field - Field element
     */
    clearError(field) {
        field.classList.remove(this.options.errorClass);
        
        const errorElement = field.parentElement.querySelector(`.${this.options.errorMessageClass}`);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
    }
    
    /**
     * Clear all errors
     */
    clearAllErrors() {
        for (const fieldId in this.fields) {
            this.clearError(this.fields[fieldId].element);
        }
    }
}

// Helper functions
function showError(message, field = null) {
    if (field) {
        const errorElement = field.parentElement.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        field.classList.add('is-invalid');
    } else {
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        }
    }
}

function clearErrors() {
    const errorElements = document.querySelectorAll('.invalid-feedback');
    errorElements.forEach(element => {
        element.textContent = '';
        element.style.display = 'none';
    });
    
    const invalidFields = document.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    const errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        errorMessage.style.display = 'none';
    }
}

// Make functions and class globally available
window.FormValidator = FormValidator;
window.showError = showError;
window.clearErrors = clearErrors; 