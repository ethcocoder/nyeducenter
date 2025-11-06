const express = require('express');
const path = require('path');
const fs = require('fs');
const app = express();
const PORT = process.env.PORT || 8080;

// Log startup information
console.log('Starting frontend server...');
console.log(`Current directory: ${process.cwd()}`);
console.log(`Template directory: ${path.join(__dirname, 'template')}`);

// Check if important directories exist
const templateDir = path.join(__dirname, 'template');
const teacherDir = path.join(templateDir, 'teachers');
const studentDir = path.join(templateDir, 'students');
const adminDir = path.join(templateDir, 'admin');

console.log(`Template directory exists: ${fs.existsSync(templateDir)}`);
console.log(`Teacher directory exists: ${fs.existsSync(teacherDir)}`);
console.log(`Student directory exists: ${fs.existsSync(studentDir)}`);
console.log(`Admin directory exists: ${fs.existsSync(adminDir)}`);

// List teacher grade directories
if (fs.existsSync(teacherDir)) {
  console.log('Teacher grade directories:');
  fs.readdirSync(teacherDir).forEach(dir => {
    console.log(` - ${dir} (exists: ${fs.existsSync(path.join(teacherDir, dir))})`);
    // Check for dashboard file
    const dashboardPath = path.join(teacherDir, dir, 'dashboard.html');
    console.log(`   Dashboard exists: ${fs.existsSync(dashboardPath)}`);
  });
}

// List student grade directories
if (fs.existsSync(studentDir)) {
  console.log('Student grade directories:');
  fs.readdirSync(studentDir).forEach(dir => {
    console.log(` - ${dir} (exists: ${fs.existsSync(path.join(studentDir, dir))})`);
    // Check for dashboard file
    const dashboardPath = path.join(studentDir, dir, 'dashboard.html');
    console.log(`   Dashboard exists: ${fs.existsSync(dashboardPath)}`);
  });
}

// Ensure student directories exist for all grades
function ensureDirectoryExists(directory) {
  if (!fs.existsSync(directory)) {
    console.log(`Creating directory: ${directory}`);
    fs.mkdirSync(directory, { recursive: true });
    return true;
  }
  return false;
}

// Make sure student grade directories exist
const studentGrades = ['grade9s', 'grade10s', 'grade11s', 'grade12s'];
studentGrades.forEach(grade => {
  const gradeDir = path.join(studentDir, grade);
  if (ensureDirectoryExists(gradeDir)) {
    // If we had to create the directory, create a simple dashboard file
    const dashboardPath = path.join(gradeDir, 'dashboard.html');
    const dashboardContent = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Student Dashboard - ${grade}</title>
        <meta charset="UTF-8">
        <style>
          body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
          h1 { color: #333; }
        </style>
      </head>
      <body>
        <h1>Student Dashboard - ${grade.replace('s', '')}</h1>
        <p>Welcome to your student dashboard. This is a placeholder page.</p>
      </body>
      </html>
    `;
    fs.writeFileSync(dashboardPath, dashboardContent);
    console.log(`Created dashboard file: ${dashboardPath}`);
  }
});

// Add simple request logging middleware
app.use((req, res, next) => {
  console.log(`${new Date().toISOString()} - ${req.method} ${req.url}`);
  next();
});

// Serve static files from the template directory
app.use(express.static(path.join(__dirname, 'template')));

// Create multiple handler for the teacher routes
app.get([
  '/teachers/grade9t/dashboard.html',
  '/teachers/grade9/dashboard.html'
], (req, res) => {
  const dashboardPath = path.join(teacherDir, 'grade9t', 'dashboard.html');
  console.log(`Request for teacher grade 9 dashboard. Path: ${dashboardPath}`);
  console.log(`File exists: ${fs.existsSync(dashboardPath)}`);
  
  if (fs.existsSync(dashboardPath)) {
    res.sendFile(dashboardPath);
  } else {
    console.error(`Teacher dashboard file not found: ${dashboardPath}`);
    res.status(404).send(`File not found: ${dashboardPath}`);
  }
});

// Create specific handlers for students grade routes
app.get([
  '/students/grade9s/dashboard.html',
  '/students/grade9/dashboard.html'
], (req, res) => {
  const dashboardPath = path.join(studentDir, 'grade9s', 'dashboard.html');
  console.log(`Request for student grade 9 dashboard. Path: ${dashboardPath}`);
  console.log(`File exists: ${fs.existsSync(dashboardPath)}`);
  
  if (fs.existsSync(dashboardPath)) {
    res.sendFile(dashboardPath);
  } else {
    console.error(`Student dashboard file not found: ${dashboardPath}`);
    res.status(404).send(`File not found: ${dashboardPath}`);
  }
});

app.get([
  '/students/grade10s/dashboard.html',
  '/students/grade10/dashboard.html'
], (req, res) => {
  const dashboardPath = path.join(studentDir, 'grade10s', 'dashboard.html');
  console.log(`Request for student grade 10 dashboard. Path: ${dashboardPath}`);
  console.log(`File exists: ${fs.existsSync(dashboardPath)}`);
  
  if (fs.existsSync(dashboardPath)) {
    res.sendFile(dashboardPath);
  } else {
    console.error(`Student dashboard file not found: ${dashboardPath}`);
    res.status(404).send(`File not found: ${dashboardPath}`);
  }
});

app.get([
  '/students/grade11s/dashboard.html',
  '/students/grade11/dashboard.html'
], (req, res) => {
  const dashboardPath = path.join(studentDir, 'grade11s', 'dashboard.html');
  console.log(`Request for student grade 11 dashboard. Path: ${dashboardPath}`);
  console.log(`File exists: ${fs.existsSync(dashboardPath)}`);
  
  if (fs.existsSync(dashboardPath)) {
    res.sendFile(dashboardPath);
  } else {
    console.error(`Student dashboard file not found: ${dashboardPath}`);
    res.status(404).send(`File not found: ${dashboardPath}`);
  }
});

app.get([
  '/students/grade12s/dashboard.html',
  '/students/grade12/dashboard.html'
], (req, res) => {
  const dashboardPath = path.join(studentDir, 'grade12s', 'dashboard.html');
  console.log(`Request for student grade 12 dashboard. Path: ${dashboardPath}`);
  console.log(`File exists: ${fs.existsSync(dashboardPath)}`);
  
  if (fs.existsSync(dashboardPath)) {
    res.sendFile(dashboardPath);
  } else {
    console.error(`Student dashboard file not found: ${dashboardPath}`);
    res.status(404).send(`File not found: ${dashboardPath}`);
  }
});

// Generic handler for any other student grade routes (keep as fallback)
app.get('/students/grade:grade*/dashboard.html', (req, res) => {
  const grade = req.params.grade;
  console.log(`Generic student dashboard requested for grade: ${grade}`);
  
  // Make sure the path includes the 's' suffix if missing
  const gradeSuffix = grade.endsWith('s') ? grade : `${grade}s`;
  
  try {
    const filePath = path.join(__dirname, 'template', 'students', `grade${gradeSuffix}`, 'dashboard.html');
    console.log(`Attempting to serve: ${filePath}`);
    res.sendFile(filePath);
  } catch (error) {
    console.error(`Error serving student dashboard: ${error}`);
    res.status(404).sendFile(path.join(__dirname, 'template', '404.html'));
  }
});

// Add a special route for login to log when it's accessed
app.get('/login.html', (req, res) => {
  console.log('Login page accessed');
  res.sendFile(path.join(__dirname, 'template', 'login.html'));
});

// For any route not found in static files, log the request in detail and serve index.html
app.get('*', (req, res) => {
  console.log(`Fallback route triggered for: ${req.url}`);
  console.log(`Requested path: ${req.path}`);
  console.log(`Full URL: ${req.protocol}://${req.get('host')}${req.originalUrl}`);
  
  // Check if the file exists in the static directory
  const requestedFile = path.join(templateDir, req.path);
  console.log(`Looking for file: ${requestedFile}`);
  console.log(`File exists: ${fs.existsSync(requestedFile)}`);
  
  res.sendFile(path.join(__dirname, 'template', 'index.html'));
});

app.listen(PORT, () => {
  console.log(`Frontend server running at http://localhost:${PORT}`);
  console.log(`Serving static files from: ${path.join(__dirname, 'template')}`);
}); 