/**
 * API Utilities for NY Education Center
 * Handles authenticated API requests with token management
 */

class ApiService {
  constructor(baseUrl = '/api') {
    this.baseUrl = baseUrl;
    this.token = localStorage.getItem('token');
  }

  /**
   * Get headers for API requests
   * @param {boolean} includeContentType - Whether to include Content-Type header
   * @returns {Object} - Headers object
   */
  getHeaders(includeContentType = true) {
    const headers = {};
    
    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }
    
    if (includeContentType) {
      headers['Content-Type'] = 'application/json';
    }
    
    return headers;
  }

  /**
   * Handle API response
   * @param {Response} response - Fetch API response
   * @returns {Promise} - Resolved with response data or rejected with error
   */
  async handleResponse(response) {
    const data = await response.json();
    
    if (!response.ok) {
      // Handle token expiration
      if (response.status === 401) {
        localStorage.removeItem('token');
        this.token = null;
        
        // Redirect to login page if not already there
        if (!window.location.pathname.includes('login.html')) {
          window.location.href = '/frontend/login.html?expired=true';
        }
      }
      
      throw new Error(data.message || 'API request failed');
    }
    
    return data;
  }

  /**
   * Make a GET request
   * @param {string} endpoint - API endpoint
   * @param {Object} params - Query parameters
   * @returns {Promise} - Resolved with response data
   */
  async get(endpoint, params = {}) {
    // Build query string
    const queryString = Object.keys(params)
      .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
      .join('&');
    
    const url = `${this.baseUrl}${endpoint}${queryString ? `?${queryString}` : ''}`;
    
    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: this.getHeaders(false)
      });
      
      return this.handleResponse(response);
    } catch (error) {
      console.error('API GET error:', error);
      throw error;
    }
  }

  /**
   * Make a POST request
   * @param {string} endpoint - API endpoint
   * @param {Object} data - Request body data
   * @returns {Promise} - Resolved with response data
   */
  async post(endpoint, data = {}) {
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        method: 'POST',
        headers: this.getHeaders(),
        body: JSON.stringify(data)
      });
      
      return this.handleResponse(response);
    } catch (error) {
      console.error('API POST error:', error);
      throw error;
    }
  }

  /**
   * Make a PUT request
   * @param {string} endpoint - API endpoint
   * @param {Object} data - Request body data
   * @returns {Promise} - Resolved with response data
   */
  async put(endpoint, data = {}) {
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        method: 'PUT',
        headers: this.getHeaders(),
        body: JSON.stringify(data)
      });
      
      return this.handleResponse(response);
    } catch (error) {
      console.error('API PUT error:', error);
      throw error;
    }
  }

  /**
   * Make a DELETE request
   * @param {string} endpoint - API endpoint
   * @returns {Promise} - Resolved with response data
   */
  async delete(endpoint) {
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        method: 'DELETE',
        headers: this.getHeaders()
      });
      
      return this.handleResponse(response);
    } catch (error) {
      console.error('API DELETE error:', error);
      throw error;
    }
  }

  /**
   * Upload a file
   * @param {string} endpoint - API endpoint
   * @param {FormData} formData - Form data with file
   * @returns {Promise} - Resolved with response data
   */
  async uploadFile(endpoint, formData) {
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        method: 'POST',
        headers: {
          'Authorization': this.token ? `Bearer ${this.token}` : ''
          // Note: Content-Type is automatically set with FormData
        },
        body: formData
      });
      
      return this.handleResponse(response);
    } catch (error) {
      console.error('API file upload error:', error);
      throw error;
    }
  }
}

// Create API service instances
const api = new ApiService();

// API modules for specific resources
const authApi = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  getCurrentUser: () => api.get('/auth/me')
};

const courseApi = {
  getAllCourses: (params) => api.get('/courses', params),
  getCourseById: (id) => api.get(`/courses/${id}`),
  createCourse: (courseData) => api.post('/courses', courseData),
  updateCourse: (id, courseData) => api.put(`/courses/${id}`, courseData),
  deleteCourse: (id) => api.delete(`/courses/${id}`),
  uploadCourseMaterial: (courseId, formData) => 
    api.uploadFile(`/courses/${courseId}/materials`, formData)
};

const quizApi = {
  getAllQuizzes: (params) => api.get('/quizzes', params),
  getQuizById: (id) => api.get(`/quizzes/${id}`),
  createQuiz: (quizData) => api.post('/quizzes', quizData),
  updateQuiz: (id, quizData) => api.put(`/quizzes/${id}`, quizData),
  deleteQuiz: (id) => api.delete(`/quizzes/${id}`),
  submitQuiz: (quizId, answers) => api.post(`/quizzes/${quizId}/submit`, answers),
  getQuizResults: (quizId) => api.get(`/quizzes/${quizId}/results`)
};

const assignmentApi = {
  getAllAssignments: (params) => api.get('/assignments', params),
  getAssignmentById: (id) => api.get(`/assignments/${id}`),
  createAssignment: (assignmentData) => api.post('/assignments', assignmentData),
  updateAssignment: (id, assignmentData) => api.put(`/assignments/${id}`, assignmentData),
  deleteAssignment: (id) => api.delete(`/assignments/${id}`),
  submitAssignment: (assignmentId, formData) => 
    api.uploadFile(`/assignments/${assignmentId}/submit`, formData),
  getSubmissions: (assignmentId) => api.get(`/assignments/${assignmentId}/submissions`)
};

const studentApi = {
  getDashboard: () => api.get('/students/dashboard'),
  getProfile: () => api.get('/students/profile'),
  updateProfile: (profileData) => api.put('/students/profile', profileData),
  getCourses: () => api.get('/students/courses'),
  getAssignments: () => api.get('/students/assignments'),
  getQuizzes: () => api.get('/students/quizzes')
};

const teacherApi = {
  getDashboard: () => api.get('/teachers/dashboard'),
  getProfile: () => api.get('/teachers/profile'),
  updateProfile: (profileData) => api.put('/teachers/profile', profileData),
  getStudents: (params) => api.get('/teachers/students', params),
  getStudentById: (id) => api.get(`/teachers/students/${id}`),
  getAssignmentSubmissions: (assignmentId) => 
    api.get(`/teachers/assignments/${assignmentId}/submissions`),
  gradeSubmission: (submissionId, grade) => 
    api.put(`/teachers/submissions/${submissionId}/grade`, grade)
};

// Auth state management
function isAuthenticated() {
  return !!localStorage.getItem('token');
}

function getUserRole() {
  const token = localStorage.getItem('token');
  if (!token) return null;
  
  // Extract payload from JWT token
  try {
    const payload = JSON.parse(atob(token.split('.')[1]));
    return payload.role;
  } catch (error) {
    console.error('Error parsing token:', error);
    return null;
  }
}

// Check auth on page load and redirect if needed
document.addEventListener('DOMContentLoaded', function() {
  // Skip auth check on login and register pages
  const currentPath = window.location.pathname;
  if (currentPath.includes('login.html') || currentPath.includes('register.html')) {
    return;
  }
  
  // Check if authenticated
  if (!isAuthenticated()) {
    window.location.href = '/frontend/login.html?redirect=' + encodeURIComponent(currentPath);
    return;
  }
  
  // Get user role and check access
  const userRole = getUserRole();
  
  // Redirect if accessing wrong section
  if (userRole === 'teacher' && currentPath.includes('/students/')) {
    window.location.href = '/frontend/template/teachers/dashboard.html';
  } else if (userRole === 'student' && currentPath.includes('/teachers/')) {
    window.location.href = '/frontend/template/students/dashboard.html';
  }
}); 