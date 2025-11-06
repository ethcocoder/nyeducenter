/**
 * API Client
 * A utility for making API requests with error handling and authentication
 */

class ApiClient {
  constructor(baseUrl = '/api') {
    this.baseUrl = baseUrl;
    this.authToken = localStorage.getItem('auth_token');
    this.user = JSON.parse(localStorage.getItem('user') || 'null');
  }

  /**
   * Set the authentication token
   * @param {string} token - JWT token
   */
  setAuthToken(token) {
    this.authToken = token;
    if (token) {
      localStorage.setItem('auth_token', token);
    } else {
      localStorage.removeItem('auth_token');
    }
  }

  /**
   * Set the user data
   * @param {Object} user - User data
   */
  setUser(user) {
    this.user = user;
    if (user) {
      localStorage.setItem('user', JSON.stringify(user));
    } else {
      localStorage.removeItem('user');
    }
  }

  /**
   * Get the authentication status
   * @returns {boolean} Is authenticated
   */
  isAuthenticated() {
    return !!this.authToken;
  }

  /**
   * Get the currently logged in user
   * @returns {Object|null} User data or null if not logged in
   */
  getUser() {
    return this.user;
  }

  /**
   * Get the user role
   * @returns {string|null} User role or null if not logged in
   */
  getUserRole() {
    return this.user ? this.user.role : null;
  }

  /**
   * Logout the user
   */
  logout() {
    this.setAuthToken(null);
    this.setUser(null);
    
    // Redirect to login page if not already there
    if (!window.location.pathname.includes('/login.html')) {
      window.location.href = '/login.html';
    }
  }

  /**
   * Create request headers
   * @param {Object} additionalHeaders - Additional headers to include
   * @returns {Object} Headers object
   */
  createHeaders(additionalHeaders = {}) {
    const headers = {
      'Content-Type': 'application/json',
      ...additionalHeaders
    };

    if (this.authToken) {
      headers['Authorization'] = `Bearer ${this.authToken}`;
    }

    return headers;
  }

  /**
   * Handle response
   * @param {Response} response - Fetch response
   * @returns {Promise} Promise with response data
   */
  async handleResponse(response) {
    const contentType = response.headers.get('content-type');
    let data;

    if (contentType && contentType.includes('application/json')) {
      data = await response.json();
    } else {
      data = await response.text();
    }

    if (!response.ok) {
      // Handle expired token
      if (response.status === 401) {
        // Check if the error is due to an expired token
        if (data.message && (
          data.message.includes('expired') || 
          data.message.includes('invalid token') ||
          data.message.includes('jwt')
        )) {
          this.logout();
          throw new Error('Your session has expired. Please login again.');
        }
      }

      const error = new Error(data.message || response.statusText);
      error.status = response.status;
      error.data = data;
      throw error;
    }

    return data;
  }

  /**
   * Make a request
   * @param {string} method - HTTP method
   * @param {string} endpoint - API endpoint
   * @param {Object} data - Request data
   * @param {Object} options - Additional options
   * @returns {Promise} Promise with response data
   */
  async request(method, endpoint, data = null, options = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    const headers = this.createHeaders(options.headers);
    const config = {
      method,
      headers,
      ...options
    };

    if (data) {
      if (data instanceof FormData) {
        // Remove Content-Type header so that browser can set it with the boundary
        delete config.headers['Content-Type'];
        config.body = data;
      } else {
        config.body = JSON.stringify(data);
      }
    }

    try {
      const response = await fetch(url, config);
      return await this.handleResponse(response);
    } catch (error) {
      // Show toast notification if available
      if (window.ToastNotification) {
        window.ToastNotification.error({
          title: 'Error',
          message: error.message || 'An error occurred'
        });
      }
      throw error;
    }
  }

  /**
   * Make a GET request
   * @param {string} endpoint - API endpoint
   * @param {Object} options - Additional options
   * @returns {Promise} Promise with response data
   */
  get(endpoint, options = {}) {
    return this.request('GET', endpoint, null, options);
  }

  /**
   * Make a POST request
   * @param {string} endpoint - API endpoint
   * @param {Object} data - Request data
   * @param {Object} options - Additional options
   * @returns {Promise} Promise with response data
   */
  post(endpoint, data, options = {}) {
    return this.request('POST', endpoint, data, options);
  }

  /**
   * Make a PUT request
   * @param {string} endpoint - API endpoint
   * @param {Object} data - Request data
   * @param {Object} options - Additional options
   * @returns {Promise} Promise with response data
   */
  put(endpoint, data, options = {}) {
    return this.request('PUT', endpoint, data, options);
  }

  /**
   * Make a PATCH request
   * @param {string} endpoint - API endpoint
   * @param {Object} data - Request data
   * @param {Object} options - Additional options
   * @returns {Promise} Promise with response data
   */
  patch(endpoint, data, options = {}) {
    return this.request('PATCH', endpoint, data, options);
  }

  /**
   * Make a DELETE request
   * @param {string} endpoint - API endpoint
   * @param {Object} options - Additional options
   * @returns {Promise} Promise with response data
   */
  delete(endpoint, options = {}) {
    return this.request('DELETE', endpoint, null, options);
  }

  /**
   * Upload a file
   * @param {string} endpoint - API endpoint
   * @param {File|Blob} file - File to upload
   * @param {string} fieldName - Field name for the file
   * @param {Object} additionalData - Additional form data
   * @param {Object} options - Additional options
   * @returns {Promise} Promise with response data
   */
  uploadFile(endpoint, file, fieldName = 'file', additionalData = {}, options = {}) {
    const formData = new FormData();
    formData.append(fieldName, file);

    // Add additional data to form data
    Object.entries(additionalData).forEach(([key, value]) => {
      formData.append(key, value);
    });

    return this.request('POST', endpoint, formData, options);
  }

  /**
   * Log in user
   * @param {string} username - Username
   * @param {string} password - Password
   * @returns {Promise} Promise with response data
   */
  async login(username, password) {
    try {
      const response = await this.post('/auth/login', { username, password });
      this.setAuthToken(response.token);
      this.setUser(response.user);
      return response;
    } catch (error) {
      throw error;
    }
  }

  /**
   * Register user
   * @param {Object} userData - User data
   * @returns {Promise} Promise with response data
   */
  async register(userData) {
    try {
      const response = await this.post('/auth/register', userData);
      this.setAuthToken(response.token);
      this.setUser(response.user);
      return response;
    } catch (error) {
      throw error;
    }
  }
}

// Create a singleton instance
const api = new ApiClient();

// Export the singleton instance
if (typeof module !== 'undefined' && module.exports) {
  module.exports = api;
} 