// Update the login function to accept either username or email
// Add this near the top of the login function:

// Check if input is an email
const isEmail = username.includes('@');
const users = await getUsersByRole(role);
let user;

if (isEmail) {
  // If input looks like an email, search by email
  user = users.find(u => u.email.toLowerCase() === username.toLowerCase());
} else {
  // Otherwise search by username
  user = users.find(u => u.username.toLowerCase() === username.toLowerCase());
}

// If no user is found
if (!user) {
  return res.status(401).json({ error: 'Invalid username/email or password' });
} 