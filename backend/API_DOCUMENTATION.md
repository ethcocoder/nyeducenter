# API Documentation

This document outlines all available API endpoints for the Education Platform Backend. Use this as a reference for integrating with the backend services.

## Authentication

All API endpoints (except `/auth/login` and `/auth/register`) require authentication using a JWT token. 

### Authentication Flow

1. Obtain a JWT token by calling the login or register endpoint
2. Include the token in the `Authorization` header of all subsequent requests:
   ```
   Authorization: Bearer <your_token>
   ```

### CSRF Protection

For all state-changing operations (POST, PUT, DELETE), include the CSRF token in the `X-CSRF-Token` header. The token can be obtained from:

1. The `csrf-token` meta tag in the HTML
2. The `XSRF-TOKEN` cookie set by the server

## Base URL

All API endpoints are relative to the base URL: `/api`

## Error Responses

All error responses follow this format:

```json
{
  "error": true,
  "message": "Description of the error",
  "status": 400, 
  "details": {} // Optional additional error details
}
```

Common HTTP status codes:
- `400` - Bad Request (invalid input)
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found (resource doesn't exist)
- `422` - Unprocessable Entity (validation error)
- `500` - Internal Server Error

## API Endpoints

### Authentication

#### Login

- **URL**: `/auth/login`
- **Method**: `POST`
- **Auth Required**: No
- **Request Body**:
  ```json
  {
    "username": "teacher123",
    "password": "securepassword"
  }
  ```
- **Response**:
  ```json
  {
    "user": {
      "id": "user_123",
      "username": "teacher123",
      "role": "teacher",
      "grade": "10t",
      "name": "Full Name"
    },
    "token": "jwt_token_here"
  }
  ```

#### Register

- **URL**: `/auth/register`
- **Method**: `POST`
- **Auth Required**: No
- **Request Body**:
  ```json
  {
    "username": "newteacher123",
    "password": "securepassword",
    "role": "teacher",
    "grade": "9t",
    "name": "Full Name"
  }
  ```
- **Response**:
  ```json
  {
    "user": {
      "id": "user_124",
      "username": "newteacher123",
      "role": "teacher",
      "grade": "9t",
      "name": "Full Name"
    },
    "token": "jwt_token_here"
  }
  ```

### User Management

#### Get Current User

- **URL**: `/users/me`
- **Method**: `GET`
- **Auth Required**: Yes
- **Response**:
  ```json
  {
    "id": "user_123",
    "username": "teacher123",
    "role": "teacher",
    "grade": "10t",
    "name": "Full Name",
    "email": "teacher@example.com",
    "created_at": "2023-05-15T12:30:45Z"
  }
  ```

#### Update Profile

- **URL**: `/users/me`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "name": "Updated Name",
    "email": "newemail@example.com"
  }
  ```
- **Response**:
  ```json
  {
    "id": "user_123",
    "username": "teacher123",
    "name": "Updated Name",
    "email": "newemail@example.com",
    "updated_at": "2023-06-20T10:15:30Z"
  }
  ```

#### Change Password

- **URL**: `/users/me/password`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "current_password": "oldpassword",
    "new_password": "newpassword",
    "confirm_password": "newpassword"
  }
  ```
- **Response**:
  ```json
  {
    "message": "Password updated successfully",
    "updated_at": "2023-06-20T10:20:45Z"
  }
  ```

### Quizzes

#### Create Quiz

- **URL**: `/quizzes`
- **Method**: `POST`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "title": "Mathematics Mid-Term",
    "subject": "mathematics",
    "total_marks": 100,
    "time_limit": 60,
    "due_date": "2023-07-15T23:59:59Z",
    "instructions": "Answer all questions.",
    "status": "published",
    "questions": [
      {
        "text": "What is 2+2?",
        "type": "multiple-choice",
        "marks": 5,
        "options": [
          {"text": "3", "is_correct": false},
          {"text": "4", "is_correct": true},
          {"text": "5", "is_correct": false},
          {"text": "6", "is_correct": false}
        ]
      },
      {
        "text": "Earth is flat.",
        "type": "true-false",
        "marks": 5,
        "correct_answer": false
      }
    ]
  }
  ```
- **Response**:
  ```json
  {
    "id": "quiz_123",
    "title": "Mathematics Mid-Term",
    "subject": "mathematics",
    "total_marks": 100,
    "created_at": "2023-06-20T11:30:15Z",
    "created_by": "user_123",
    "status": "published",
    "question_count": 2
  }
  ```

#### Get All Quizzes

- **URL**: `/quizzes`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `subject` (optional): Filter by subject
  - `status` (optional): Filter by status (published, draft)
  - `page` (optional): Page number for pagination
  - `limit` (optional): Items per page
- **Response**:
  ```json
  {
    "total": 25,
    "page": 1,
    "limit": 10,
    "quizzes": [
      {
        "id": "quiz_123",
        "title": "Mathematics Mid-Term",
        "subject": "mathematics",
        "total_marks": 100,
        "created_at": "2023-06-20T11:30:15Z",
        "status": "published",
        "question_count": 2
      },
      // More quizzes...
    ]
  }
  ```

#### Get Quiz by ID

- **URL**: `/quizzes/:id`
- **Method**: `GET`
- **Auth Required**: Yes
- **Response**:
  ```json
  {
    "id": "quiz_123",
    "title": "Mathematics Mid-Term",
    "subject": "mathematics",
    "total_marks": 100,
    "time_limit": 60,
    "due_date": "2023-07-15T23:59:59Z",
    "instructions": "Answer all questions.",
    "status": "published",
    "created_at": "2023-06-20T11:30:15Z",
    "created_by": "user_123",
    "questions": [
      {
        "id": "question_1",
        "text": "What is 2+2?",
        "type": "multiple-choice",
        "marks": 5,
        "options": [
          {"id": "option_1", "text": "3", "is_correct": false},
          {"id": "option_2", "text": "4", "is_correct": true},
          {"id": "option_3", "text": "5", "is_correct": false},
          {"id": "option_4", "text": "6", "is_correct": false}
        ]
      },
      {
        "id": "question_2",
        "text": "Earth is flat.",
        "type": "true-false",
        "marks": 5,
        "correct_answer": false
      }
    ]
  }
  ```

#### Update Quiz

- **URL**: `/quizzes/:id`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Request Body**: Same as Create Quiz
- **Response**: Same as Get Quiz by ID

#### Delete Quiz

- **URL**: `/quizzes/:id`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **Response**:
  ```json
  {
    "message": "Quiz deleted successfully"
  }
  ```

#### Send Quiz to Students

- **URL**: `/quizzes/:id/send`
- **Method**: `POST`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "class_ids": ["class_9a", "class_9b"],
    "student_ids": ["student_123", "student_124"],
    "due_date": "2023-07-15T23:59:59Z",
    "message": "Please complete this quiz before the deadline."
  }
  ```
- **Response**:
  ```json
  {
    "message": "Quiz sent successfully to 45 students",
    "sent_at": "2023-06-21T09:15:30Z"
  }
  ```

### Assignments

#### Create Assignment

- **URL**: `/assignments`
- **Method**: `POST`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "title": "Mathematics Homework",
    "subject": "mathematics",
    "description": "Complete all problems in Chapter 5.",
    "total_marks": 50,
    "due_date": "2023-07-10T23:59:59Z",
    "status": "published",
    "attachment_urls": [
      "https://example.com/files/math_hw.pdf"
    ]
  }
  ```
- **Response**:
  ```json
  {
    "id": "assignment_123",
    "title": "Mathematics Homework",
    "subject": "mathematics",
    "total_marks": 50,
    "created_at": "2023-06-20T13:45:20Z",
    "created_by": "user_123",
    "status": "published"
  }
  ```

#### Get All Assignments

- **URL**: `/assignments`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `subject` (optional): Filter by subject
  - `status` (optional): Filter by status (published, draft)
  - `page` (optional): Page number for pagination
  - `limit` (optional): Items per page
- **Response**:
  ```json
  {
    "total": 15,
    "page": 1,
    "limit": 10,
    "assignments": [
      {
        "id": "assignment_123",
        "title": "Mathematics Homework",
        "subject": "mathematics",
        "total_marks": 50,
        "created_at": "2023-06-20T13:45:20Z",
        "status": "published",
        "due_date": "2023-07-10T23:59:59Z"
      },
      // More assignments...
    ]
  }
  ```

#### Get Assignment by ID

- **URL**: `/assignments/:id`
- **Method**: `GET`
- **Auth Required**: Yes
- **Response**:
  ```json
  {
    "id": "assignment_123",
    "title": "Mathematics Homework",
    "subject": "mathematics",
    "description": "Complete all problems in Chapter 5.",
    "total_marks": 50,
    "due_date": "2023-07-10T23:59:59Z",
    "status": "published",
    "created_at": "2023-06-20T13:45:20Z",
    "created_by": "user_123",
    "attachment_urls": [
      "https://example.com/files/math_hw.pdf"
    ]
  }
  ```

#### Update Assignment

- **URL**: `/assignments/:id`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Request Body**: Same as Create Assignment
- **Response**: Same as Get Assignment by ID

#### Delete Assignment

- **URL**: `/assignments/:id`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **Response**:
  ```json
  {
    "message": "Assignment deleted successfully"
  }
  ```

#### Send Assignment to Students

- **URL**: `/assignments/:id/send`
- **Method**: `POST`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "class_ids": ["class_9a", "class_9b"],
    "student_ids": ["student_123", "student_124"],
    "due_date": "2023-07-10T23:59:59Z",
    "message": "Please complete this assignment before the deadline."
  }
  ```
- **Response**:
  ```json
  {
    "message": "Assignment sent successfully to 45 students",
    "sent_at": "2023-06-21T10:30:15Z"
  }
  ```

### Courses

#### Create Course

- **URL**: `/courses`
- **Method**: `POST`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "title": "Introduction to Algebra",
    "subject": "mathematics",
    "description": "A comprehensive course on algebraic concepts.",
    "code": "MATH101",
    "credit": 3,
    "class_ids": ["class_9a", "class_9b"],
    "image_url": "https://example.com/images/algebra.jpg",
    "materials": [
      {
        "title": "Chapter 1: Variables",
        "description": "Introduction to variables and expressions.",
        "type": "document",
        "url": "https://example.com/files/variables.pdf"
      },
      {
        "title": "Introduction Video",
        "description": "Welcome to the course.",
        "type": "video",
        "url": "https://youtube.com/watch?v=abc123"
      }
    ],
    "schedule": [
      {
        "day": "monday",
        "start_time": "08:00",
        "end_time": "09:30"
      },
      {
        "day": "wednesday",
        "start_time": "08:00",
        "end_time": "09:30"
      }
    ],
    "status": "published"
  }
  ```
- **Response**:
  ```json
  {
    "id": "course_123",
    "title": "Introduction to Algebra",
    "subject": "mathematics",
    "code": "MATH101",
    "created_at": "2023-06-20T14:30:10Z",
    "created_by": "user_123",
    "status": "published"
  }
  ```

#### Get All Courses

- **URL**: `/courses`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `subject` (optional): Filter by subject
  - `status` (optional): Filter by status (published, draft)
  - `page` (optional): Page number for pagination
  - `limit` (optional): Items per page
- **Response**:
  ```json
  {
    "total": 8,
    "page": 1,
    "limit": 10,
    "courses": [
      {
        "id": "course_123",
        "title": "Introduction to Algebra",
        "subject": "mathematics",
        "code": "MATH101",
        "credit": 3,
        "created_at": "2023-06-20T14:30:10Z",
        "status": "published",
        "image_url": "https://example.com/images/algebra.jpg"
      },
      // More courses...
    ]
  }
  ```

#### Get Course by ID

- **URL**: `/courses/:id`
- **Method**: `GET`
- **Auth Required**: Yes
- **Response**:
  ```json
  {
    "id": "course_123",
    "title": "Introduction to Algebra",
    "subject": "mathematics",
    "description": "A comprehensive course on algebraic concepts.",
    "code": "MATH101",
    "credit": 3,
    "class_ids": ["class_9a", "class_9b"],
    "image_url": "https://example.com/images/algebra.jpg",
    "materials": [
      {
        "id": "material_1",
        "title": "Chapter 1: Variables",
        "description": "Introduction to variables and expressions.",
        "type": "document",
        "url": "https://example.com/files/variables.pdf"
      },
      {
        "id": "material_2",
        "title": "Introduction Video",
        "description": "Welcome to the course.",
        "type": "video",
        "url": "https://youtube.com/watch?v=abc123"
      }
    ],
    "schedule": [
      {
        "day": "monday",
        "start_time": "08:00",
        "end_time": "09:30"
      },
      {
        "day": "wednesday",
        "start_time": "08:00",
        "end_time": "09:30"
      }
    ],
    "status": "published",
    "created_at": "2023-06-20T14:30:10Z",
    "created_by": "user_123"
  }
  ```

#### Update Course

- **URL**: `/courses/:id`
- **Method**: `PUT`
- **Auth Required**: Yes
- **Request Body**: Same as Create Course
- **Response**: Same as Get Course by ID

#### Delete Course

- **URL**: `/courses/:id`
- **Method**: `DELETE`
- **Auth Required**: Yes
- **Response**:
  ```json
  {
    "message": "Course deleted successfully"
  }
  ```

### File Upload

#### Upload File

- **URL**: `/files/upload`
- **Method**: `POST`
- **Auth Required**: Yes
- **Content-Type**: `multipart/form-data`
- **Form Fields**:
  - `file`: The file to upload
  - `type` (optional): The type of file (document, image, video)
  - `related_to` (optional): Related entity (quiz, assignment, course)
  - `related_id` (optional): ID of the related entity
- **Response**:
  ```json
  {
    "file_id": "file_123",
    "filename": "assignment.pdf",
    "original_name": "math_hw.pdf",
    "url": "https://example.com/files/assignment.pdf",
    "mime_type": "application/pdf",
    "size": 1024000,
    "uploaded_at": "2023-06-21T11:45:30Z"
  }
  ```

### Classes and Students

#### Get Classes

- **URL**: `/classes`
- **Method**: `GET`
- **Auth Required**: Yes
- **Response**:
  ```json
  {
    "classes": [
      {
        "id": "class_9a",
        "name": "9-A",
        "grade": "9",
        "section": "A",
        "student_count": 35
      },
      {
        "id": "class_9b",
        "name": "9-B",
        "grade": "9",
        "section": "B",
        "student_count": 32
      }
    ]
  }
  ```

#### Get Students by Class

- **URL**: `/classes/:id/students`
- **Method**: `GET`
- **Auth Required**: Yes
- **Response**:
  ```json
  {
    "class": {
      "id": "class_9a",
      "name": "9-A",
      "grade": "9",
      "section": "A"
    },
    "students": [
      {
        "id": "student_123",
        "name": "Student Name",
        "roll_number": "9A001"
      },
      // More students...
    ]
  }
  ```

## Rate Limiting

To prevent abuse, the API implements rate limiting:

- 100 requests per minute for authenticated users
- 20 requests per minute for unauthenticated users

When a rate limit is exceeded, you'll receive a `429 Too Many Requests` response with the following headers:
- `X-RateLimit-Limit`: The request limit
- `X-RateLimit-Remaining`: The number of remaining requests
- `X-RateLimit-Reset`: The time when the rate limit will reset (Unix timestamp)

## Webhook Notifications

For real-time updates, you can register webhooks:

- **URL**: `/webhooks/register`
- **Method**: `POST`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "target_url": "https://yourapp.com/api/notifications",
    "events": ["quiz.submitted", "assignment.created"],
    "secret": "your_secret_key"
  }
  ```
- **Response**:
  ```json
  {
    "id": "webhook_123",
    "target_url": "https://yourapp.com/api/notifications",
    "events": ["quiz.submitted", "assignment.created"],
    "created_at": "2023-06-22T09:00:00Z"
  }
  ```

Webhook requests will include an `X-Webhook-Signature` header that you should validate using your secret key. 