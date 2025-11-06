// ... existing code ...
useEffect(() => {
  const token = localStorage.getItem('token');
  if (token) {
    // Add proper token validation and redirect
    navigate('/dashboard'); // Redirect authenticated users
  }
}, []);
// ... existing code ...