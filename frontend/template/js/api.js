// API helper functions for the educational center application

// Base API URL
const API_BASE_URL = '';

// Get auth token from localStorage
function getAuthToken() {
    return localStorage.getItem('token');
}

// API request helper with auth headers
async function apiRequest(endpoint, options = {}) {
    const token = getAuthToken();
    
    // Default headers with auth token if available
    const headers = {
        'Content-Type': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
        ...(options.headers || {})
    };
    
    // Build request config
    const config = {
        ...options,
        headers
    };
    
    // Log the request for debugging
    console.log(`API Request: ${endpoint}`, config);
    
    try {
        // Make the request
        const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
        
        // Try to parse JSON response
        let data;
        try {
            data = await response.json();
        } catch (e) {
            console.warn('Response is not JSON:', e);
            data = {};
        }
        
        // Log response for debugging
        console.log(`API Response: ${endpoint}`, { status: response.status, data });
        
        // If response is not OK, throw an error
        if (!response.ok) {
            throw new Error(data.error || `API Error: ${response.status}`);
        }
        
        return data;
    } catch (error) {
        console.error(`API Error: ${endpoint}`, error);
        throw error;
    }
}

// API endpoints
const api = {
    // Auth API
    auth: {
        login: async (credentials) => {
            try {
                // Try main endpoint
                return await apiRequest('/api/auth/login', {
                    method: 'POST',
                    body: JSON.stringify(credentials)
                });
            } catch (error) {
                // Fallback to debug endpoint
                console.log('Main login endpoint failed, trying debug endpoint...');
                return await apiRequest('/debug/login', {
                    method: 'POST',
                    body: JSON.stringify(credentials)
                });
            }
        },
        
        register: async (userData) => {
            try {
                // Try main endpoint
                return await apiRequest('/api/auth/register', {
                    method: 'POST',
                    body: JSON.stringify(userData)
                });
            } catch (error) {
                // Fallback to debug endpoint
                console.log('Main registration endpoint failed, trying debug endpoint...');
                return await apiRequest('/debug/register', {
                    method: 'POST',
                    body: JSON.stringify(userData)
                });
            }
        }
    },
    
    // Grades API
    grades: {
        getGrades: async (role, grade) => {
            return await apiRequest(`/api/grades/${role}/${grade}`, {
                method: 'GET'
            });
        },
        
        addGrade: async (role, grade, gradeData) => {
            return await apiRequest(`/api/grades/${role}/${grade}`, {
                method: 'POST',
                body: JSON.stringify(gradeData)
            });
        }
    }
};

// Initialize with logging
console.log('API helper initialized'); 