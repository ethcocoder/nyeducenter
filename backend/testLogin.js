const axios = require('axios');

async function testLogin(email, password) {
  try {
    console.log(`Testing login with email: ${email}`);
    
    // Test login with provided credentials
    const loginRes = await axios.post('http://localhost:5000/api/auth/login', {
      email,
      password
    }, {
      headers: {
        'Content-Type': 'application/json'
      }
    });
    
    console.log('Login successful!');
    console.log('Token:', loginRes.data.token);
    
    // Test fetching user data with the token
    const userRes = await axios.get('http://localhost:5000/api/auth/user', {
      headers: {
        'x-auth-token': loginRes.data.token
      }
    });
    
    console.log('User data retrieved successfully:');
    console.log(JSON.stringify(userRes.data, null, 2));
    
    return true;
  } catch (error) {
    console.error('Error during test:');
    if (error.response) {
      console.error(`Status: ${error.response.status}`);
      console.error('Response data:', error.response.data);
    } else {
      console.error(error.message);
    }
    return false;
  }
}

async function runTests() {
  console.log('=== TESTING LOGIN FUNCTIONALITY ===');
  
  // Test with default credentials from seed data
  console.log('\n1. Testing default admin account:');
  await testLogin('admin@edun.edu', 'password123');
  
  // Test with your email but default password from createAdmin script
  console.log('\n2. Testing your account with admin123 password:');
  await testLogin('natnaeldaniel8@gmail.com', 'admin123');
  
  // Test with your email and the newly reset password
  console.log('\n3. Testing your account with reset password:');
  await testLogin('natnaeldaniel8@gmail.com', 'password123');
  
  console.log('\nIf any tests succeeded, your backend authentication system is working properly.');
  console.log('If you\'re still having issues with the frontend, it may be related to how the application is handling the token or redirecting after login.');
}

runTests(); 