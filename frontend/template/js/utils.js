/**
 * Utility functions for the application
 * Includes: CSRF protection, API request handling, form validation, and error handling
 */

// Global configuration
const config = {
    apiBaseUrl: '/api', // Replace with actual API base URL in production
    csrfHeaderName: 'X-CSRF-Token',
    defaultErrorMessage: 'ስህተት ተከስቷል፣ እባክዎ ዳግም ይሞክሩ።'
};

// CSRF Token Management
const csrfToken = {
    /**
     * Gets the CSRF token from a meta tag or cookie
     * @returns {string} The CSRF token
     */
    get: function() {
        // First try to get from meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        // Fallback to cookie
        return this.getFromCookie();
    },
    
    /**
     * Gets the CSRF token from a cookie
     * @returns {string} The CSRF token from cookie
     */
    getFromCookie: function() {
        const name = 'XSRF-TOKEN=';
        const decodedCookie = decodeURIComponent(document.cookie);
        const cookieArray = decodedCookie.split(';');
        
        for (let i = 0; i < cookieArray.length; i++) {
            let cookie = cookieArray[i].trim();
            if (cookie.indexOf(name) === 0) {
                return cookie.substring(name.length, cookie.length);
            }
        }
        
        return '';
    },
    
    /**
     * Sets the CSRF token in a meta tag
     * @param {string} token - The CSRF token to set
     */
    setMetaTag: function(token) {
        let metaTag = document.querySelector('meta[name="csrf-token"]');
        
        if (!metaTag) {
            metaTag = document.createElement('meta');
            metaTag.name = 'csrf-token';
            document.head.appendChild(metaTag);
        }
        
        metaTag.setAttribute('content', token);
    }
};

// API Request Handler
const api = {
    /**
     * Makes an API request with proper error handling
     * @param {string} endpoint - The API endpoint
     * @param {Object} options - Fetch options
     * @returns {Promise} Promise that resolves with the response data
     */
    request: async function(endpoint, options = {}) {
        const url = endpoint.startsWith('http') ? endpoint : `${config.apiBaseUrl}${endpoint}`;
        
        // Default options
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                [config.csrfHeaderName]: csrfToken.get()
            },
            credentials: 'include', // Include cookies
        };
        
        // Merge options
        const fetchOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...(options.headers || {})
            }
        };
        
        try {
            const response = await fetch(url, fetchOptions);
            
            // Handle 401 (Unauthorized) - redirect to login
            if (response.status === 401) {
                window.location.href = '/login.html';
                return null;
            }
            
            // Handle 403 (Forbidden) - CSRF token mismatch or permission issue
            if (response.status === 403) {
                console.error('Forbidden: CSRF token mismatch or permission issue');
                throw new Error('ይህን ድርጊት ለማከናወን ፈቃድ የለዎትም።');
            }
            
            // Parse response
            const contentType = response.headers.get('content-type');
            let data;
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = await response.text();
            }
            
            // Check if the response was successful
            if (!response.ok) {
                const error = (data && data.message) || response.statusText;
                throw new Error(error);
            }
            
            return data;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    },
    
    /**
     * Makes a GET request to the API
     * @param {string} endpoint - The API endpoint
     * @param {Object} options - Additional fetch options
     * @returns {Promise} Promise that resolves with the response data
     */
    get: function(endpoint, options = {}) {
        return this.request(endpoint, {
            method: 'GET',
            ...options
        });
    },
    
    /**
     * Makes a POST request to the API
     * @param {string} endpoint - The API endpoint
     * @param {Object} data - The data to send
     * @param {Object} options - Additional fetch options
     * @returns {Promise} Promise that resolves with the response data
     */
    post: function(endpoint, data, options = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data),
            ...options
        });
    },
    
    /**
     * Makes a PUT request to the API
     * @param {string} endpoint - The API endpoint
     * @param {Object} data - The data to send
     * @param {Object} options - Additional fetch options
     * @returns {Promise} Promise that resolves with the response data
     */
    put: function(endpoint, data, options = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data),
            ...options
        });
    },
    
    /**
     * Makes a DELETE request to the API
     * @param {string} endpoint - The API endpoint
     * @param {Object} options - Additional fetch options
     * @returns {Promise} Promise that resolves with the response data
     */
    delete: function(endpoint, options = {}) {
        return this.request(endpoint, {
            method: 'DELETE',
            ...options
        });
    },
    
    /**
     * Uploads a file or form data to the API
     * @param {string} endpoint - The API endpoint
     * @param {FormData} formData - The form data to send
     * @param {Object} options - Additional fetch options
     * @returns {Promise} Promise that resolves with the response data
     */
    upload: function(endpoint, formData, options = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: formData,
            headers: {
                [config.csrfHeaderName]: csrfToken.get()
                // Content-Type is automatically set by the browser for FormData
            },
            ...options
        });
    }
};

// Form Validation
const validation = {
    /**
     * Shows an error message for a form field
     * @param {HTMLElement} field - The form field
     * @param {string} message - The error message
     */
    showError: function(field, message) {
        // Remove existing error message
        this.clearError(field);
        
        // Add error class to the field
        field.classList.add('is-invalid');
        
        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        
        // Insert after the field
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    },
    
    /**
     * Clears the error message for a form field
     * @param {HTMLElement} field - The form field
     */
    clearError: function(field) {
        field.classList.remove('is-invalid');
        
        // Remove existing error message
        const errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('error-message')) {
            errorElement.remove();
        }
    },
    
    /**
     * Validates that a field is not empty
     * @param {HTMLElement} field - The form field
     * @param {string} message - The error message
     * @returns {boolean} Whether the field is valid
     */
    required: function(field, message = 'ይህ መስክ አስፈላጊ ነው።') {
        if (!field.value.trim()) {
            this.showError(field, message);
            return false;
        }
        
        this.clearError(field);
        return true;
    },
    
    /**
     * Validates that a field has a minimum length
     * @param {HTMLElement} field - The form field
     * @param {number} minLength - The minimum length
     * @param {string} message - The error message
     * @returns {boolean} Whether the field is valid
     */
    minLength: function(field, minLength, message = `ቢያንስ ${minLength} ቁምፊዎች ያስፈልጋሉ።`) {
        if (field.value.trim().length < minLength) {
            this.showError(field, message);
            return false;
        }
        
        this.clearError(field);
        return true;
    },
    
    /**
     * Validates that a field matches a specific pattern (regex)
     * @param {HTMLElement} field - The form field
     * @param {RegExp} pattern - The regex pattern
     * @param {string} message - The error message
     * @returns {boolean} Whether the field is valid
     */
    pattern: function(field, pattern, message = 'እባክዎ ትክክለኛ ቅርጸት ያስገቡ።') {
        if (!pattern.test(field.value.trim())) {
            this.showError(field, message);
            return false;
        }
        
        this.clearError(field);
        return true;
    },
    
    /**
     * Validates an email field
     * @param {HTMLElement} field - The form field
     * @param {string} message - The error message
     * @returns {boolean} Whether the field is valid
     */
    email: function(field, message = 'እባክዎ ትክክለኛ ኢሜይል አድራሻ ያስገቡ።') {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return this.pattern(field, emailPattern, message);
    }
};

// Export utilities to the global scope
window.csrfToken = csrfToken;
window.api = api;
window.validation = validation; 