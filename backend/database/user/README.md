# User Database Structure

This directory contains JSON data storage for users categorized by roles.

## Directory Structure

```
backend/database/user/
├── admin/
│   └── grades/
│       └── grades.json
├── teacher/
│   ├── grade9/
│   │   └── grades/
│   │       └── grades.json
│   ├── grade10/
│   │   └── grades/
│   │       └── grades.json
│   ├── grade11/
│   │   └── grades/
│   │       └── grades.json
│   └── grade12/
│       └── grades/
│           └── grades.json
└── student/
    ├── grade9/
    │   └── grades/
    │       └── grades.json
    ├── grade10/
    │   └── grades/
    │       └── grades.json
    ├── grade11/
    │   └── grades/
    │       └── grades.json
    └── grade12/
        └── grades/
            └── grades.json
```

## Grade Schema

Each `grades.json` file contains an array of grade objects with the following structure:

```json
[
  {
    "id": "string",
    "studentId": "string",
    "courseId": "string",
    "value": number,
    "weight": number,
    "type": "string",
    "comment": "string",
    "createdAt": "ISO-date-string",
    "updatedAt": "ISO-date-string"
  }
]
```

## API Access

You can access grade data through the following API endpoints:

- GET `/api/grades/:role/:grade` - Get all grades for a specific role and grade
- GET `/api/grades/:role/:grade/:id` - Get a specific grade by ID
- POST `/api/grades/:role/:grade` - Create a new grade
- PUT `/api/grades/:role/:grade/:id` - Update an existing grade
- DELETE `/api/grades/:role/:grade/:id` - Delete a grade (admin only)

Refer to the API documentation for more details on using these endpoints. 