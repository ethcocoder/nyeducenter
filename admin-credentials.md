# Admin Login Credentials

## Default Admin Account
- Email: admin@edun.edu
- Password: password123 (default from seed data)

## Your Admin Account
- Email: natnaeldaniel8@gmail.com
- Password: password123 (reset to match default password)

## How to Login
1. Start the backend server:
   ```
   cd backend
   npm run dev
   ```

2. Start the frontend:
   ```
   cd frontend
   npm start
   ```

3. Visit http://localhost:3000/login and enter your credentials

## Authentication Troubleshooting
If you're still having login issues, try these steps:

1. Clear your browser's local storage:
   - Open DevTools (F12)
   - Go to Application tab > Local Storage
   - Delete any items in localStorage

2. Use the auth debug page to check your authentication status:
   - Visit http://localhost:3000/auth-debug

3. Test your credentials with the backend directly:
   ```
   cd backend
   node testLogin.js
   ```

4. Reset your password if needed:
   ```
   cd backend
   npm run reset-password
   ```

## Technical Notes
- Both the backend auth and token creation are working correctly
- The frontend now handles authentication state and redirection properly
- Token is stored in localStorage and used for API requests 