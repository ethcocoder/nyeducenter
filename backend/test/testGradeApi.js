/**
 * Grade API Test Script
 * 
 * This script tests the Grade API endpoints.
 * Run with: node test/testGradeApi.js
 */

const axios = require('axios');

// API Configuration
const API_URL = 'http://localhost:3000/api';
let authToken = '';

// Test credentials
const TEST_USER = {
  username: 'admin',
  password: 'admin123'
};

// Sample grade data
const SAMPLE_GRADE = {
  studentId: 'student123',
  courseId: 'course456',
  value: 95,
  weight: 1,
  type: 'Assignment',
  comment: 'Excellent work'
};

// Helper function for API requests
async function apiRequest(method, endpoint, data = null, token = null) {
  try {
    const config = {
      headers: {}
    };
    
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    
    let response;
    
    if (method === 'GET') {
      response = await axios.get(`${API_URL}${endpoint}`, config);
    } else if (method === 'POST') {
      response = await axios.post(`${API_URL}${endpoint}`, data, config);
    } else if (method === 'PUT') {
      response = await axios.put(`${API_URL}${endpoint}`, data, config);
    } else if (method === 'DELETE') {
      response = await axios.delete(`${API_URL}${endpoint}`, config);
    }
    
    console.log(`${method} ${endpoint}: ${response.status} ${response.statusText}`);
    return response.data;
  } catch (error) {
    console.error(`Error ${method} ${endpoint}:`, error.response?.data || error.message);
    return null;
  }
}

// Test login to get authentication token
async function login() {
  console.log('\n=== Testing Login ===');
  const response = await apiRequest('POST', '/auth/login', TEST_USER);
  
  if (response && response.token) {
    authToken = response.token;
    console.log('Login successful, token obtained');
    return true;
  } else {
    console.log('Login failed');
    console.log('Try using the debug login endpoint instead:');
    const debugResponse = await apiRequest('POST', '/debug/login', TEST_USER);
    
    if (debugResponse && debugResponse.token) {
      authToken = debugResponse.token;
      console.log('Debug login successful, token obtained');
      return true;
    }
    
    return false;
  }
}

// Test grade API endpoints
async function testGradeAPI() {
  console.log('\n=== Testing Grade API ===');
  
  // Test login first to get token
  const loginSuccess = await login();
  
  if (!loginSuccess) {
    console.error('Failed to authenticate. Cannot proceed with tests.');
    return;
  }
  
  // Create a test grade for grade 9 students
  console.log('\n--- Create Grade ---');
  const newGrade = await apiRequest(
    'POST', 
    '/grades/student/9', 
    SAMPLE_GRADE, 
    authToken
  );
  
  if (!newGrade) {
    console.error('Failed to create grade. Cannot proceed with tests.');
    return;
  }
  
  const gradeId = newGrade.data.id;
  console.log(`Grade created with ID: ${gradeId}`);
  
  // Get all grades
  console.log('\n--- Get All Grades ---');
  await apiRequest('GET', '/grades/student/9', null, authToken);
  
  // Get grade by ID
  console.log('\n--- Get Grade by ID ---');
  await apiRequest('GET', `/grades/student/9/${gradeId}`, null, authToken);
  
  // Update grade
  console.log('\n--- Update Grade ---');
  await apiRequest(
    'PUT', 
    `/grades/student/9/${gradeId}`, 
    { value: 98, comment: 'Updated: Outstanding work' }, 
    authToken
  );
  
  // Get updated grade
  console.log('\n--- Get Updated Grade ---');
  await apiRequest('GET', `/grades/student/9/${gradeId}`, null, authToken);
  
  // Delete grade
  console.log('\n--- Delete Grade ---');
  await apiRequest('DELETE', `/grades/student/9/${gradeId}`, null, authToken);
  
  // Verify deletion
  console.log('\n--- Verify Deletion ---');
  await apiRequest('GET', `/grades/student/9/${gradeId}`, null, authToken);
}

// Run the tests
testGradeAPI().catch(error => {
  console.error('Test Error:', error);
}); 