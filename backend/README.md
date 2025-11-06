# EDUN E-Learning Backend

This is the backend API for the EDUN E-Learning System, using JSON file-based storage.

## Features

- RESTful API with Express.js
- Authentication with JWT
- JSON file-based data storage (no database required)
- Internationalization support (i18n)
- Role-based access control (admin, teacher, student, parent)

## Setup and Installation

1. Install dependencies:
   ```
   npm install
   ```

2. Create a `.env` file in the root directory with the following environment variables:
   ```
   PORT=5000
   JWT_SECRET=your_secret_key_here
   ```

3. Start the server:
   ```
   npm start
   ```

   For development with auto-restart:
   ```
   npm run dev
   ```

## Data Storage

All data is stored in JSON files in the `data` directory. Each collection has its own JSON file:

- users.json
- courses.json
- content.json
- assignments.json
- grades.json
- messages.json
- quizzes.json
- calendar.json
- announcements.json

## Seeding Data

The server automatically seeds initial data when it starts. If you want to manually seed data:

```
npm run seed
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login and get token
- `GET /api/auth/user` - Get current user data

### Users
- `GET /api/users` - Get all users (admin only)
- `GET /api/users/me` - Get current user profile
- `PUT /api/users/me` - Update current user profile

### Courses
- `GET /api/courses` - Get all courses
- `GET /api/courses/:id` - Get a specific course
- `POST /api/courses` - Create a new course (teacher/admin only)
- `PUT /api/courses/:id` - Update a course (teacher/admin only)
- `DELETE /api/courses/:id` - Delete a course (admin only)

### Other endpoints
Additional endpoints are available for assignments, quizzes, grades, and other resources.

## Default Test Accounts

The seed data includes the following test accounts (all with password: `password123`):

1. Admin: admin@edun.edu
2. Teacher: teacher@edun.edu
3. Student: student@edun.edu
4. Parent: parent@edun.edu 