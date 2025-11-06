/**
 * Database schemas for the application
 * These schemas define the structure for each table in the database
 */

// User schema
const userSchema = {
    id: { type: 'string', required: true },
    username: { type: 'string', required: true },
    password: { type: 'string', required: true },
    role: { type: 'string', enum: ['admin', 'teacher', 'student'], required: true },
    grade: { type: 'string', required: false },
    fullName: { type: 'string', required: false },
    email: { type: 'string', required: false },
    profileImage: { type: 'string', required: false },
    phone: { type: 'string', required: false },
    address: { type: 'string', required: false },
    createdAt: { type: 'string', format: 'date-time', required: true },
    updatedAt: { type: 'string', format: 'date-time', required: true }
};

// Course schema
const courseSchema = {
    id: { type: 'string', required: true },
    title: { type: 'string', required: true },
    description: { type: 'string', required: false },
    grade: { type: 'string', required: true },
    subject: { type: 'string', required: true },
    teacherId: { type: 'string', required: false },
    startDate: { type: 'string', format: 'date-time', required: false },
    endDate: { type: 'string', format: 'date-time', required: false },
    createdAt: { type: 'string', format: 'date-time', required: true },
    updatedAt: { type: 'string', format: 'date-time', required: true }
};

// Activity schema
const activitySchema = {
    id: { type: 'string', required: true },
    type: { type: 'string', required: true },
    userId: { type: 'string', required: true },
    userRole: { type: 'string', required: true },
    metadata: { type: 'object', required: false },
    ip: { type: 'string', required: false },
    timestamp: { type: 'string', format: 'date-time', required: true }
};

// Settings schema
const settingsSchema = {
    id: { type: 'string', required: true },
    siteName: { type: 'string', required: false },
    contactEmail: { type: 'string', required: false },
    maintenanceMode: { type: 'boolean', required: false },
    allowRegistration: { type: 'boolean', required: false },
    academicYear: { type: 'string', required: false },
    logoUrl: { type: 'string', required: false },
    faviconUrl: { type: 'string', required: false },
    createdAt: { type: 'string', format: 'date-time', required: true },
    updatedAt: { type: 'string', format: 'date-time', required: true }
};

// Quiz schema
const quizSchema = {
    id: { type: 'string', required: true },
    title: { type: 'string', required: true },
    description: { type: 'string', required: false },
    courseId: { type: 'string', required: false },
    grade: { type: 'string', required: true },
    subject: { type: 'string', required: true },
    teacherId: { type: 'string', required: true },
    durationMinutes: { type: 'number', required: false },
    totalPoints: { type: 'number', required: false },
    passingPoints: { type: 'number', required: false },
    questions: { 
        type: 'array', 
        items: {
            type: 'object',
            properties: {
                id: { type: 'string', required: true },
                text: { type: 'string', required: true },
                type: { type: 'string', enum: ['multiple-choice', 'true-false', 'short-answer'], required: true },
                options: { type: 'array', items: { type: 'string' }, required: false },
                correctAnswer: { type: 'string', required: true },
                points: { type: 'number', required: false }
            }
        },
        required: false 
    },
    createdAt: { type: 'string', format: 'date-time', required: true },
    updatedAt: { type: 'string', format: 'date-time', required: true }
};

// Assignment schema
const assignmentSchema = {
    id: { type: 'string', required: true },
    title: { type: 'string', required: true },
    description: { type: 'string', required: false },
    courseId: { type: 'string', required: false },
    grade: { type: 'string', required: true },
    subject: { type: 'string', required: true },
    teacherId: { type: 'string', required: true },
    dueDate: { type: 'string', format: 'date-time', required: false },
    totalPoints: { type: 'number', required: false },
    attachments: { type: 'array', items: { type: 'string' }, required: false },
    instructions: { type: 'string', required: false },
    createdAt: { type: 'string', format: 'date-time', required: true },
    updatedAt: { type: 'string', format: 'date-time', required: true }
};

// Student quiz submission schema
const quizSubmissionSchema = {
    id: { type: 'string', required: true },
    quizId: { type: 'string', required: true },
    studentId: { type: 'string', required: true },
    submissionDate: { type: 'string', format: 'date-time', required: true },
    score: { type: 'number', required: true },
    maxScore: { type: 'number', required: true },
    answers: { 
        type: 'array', 
        items: {
            type: 'object',
            properties: {
                questionId: { type: 'string', required: true },
                answer: { type: 'string', required: false },
                isCorrect: { type: 'boolean', required: true },
                points: { type: 'number', required: true }
            }
        },
        required: false 
    },
    timeSpentMinutes: { type: 'number', required: false },
    feedback: { type: 'string', required: false },
    createdAt: { type: 'string', format: 'date-time', required: true },
    updatedAt: { type: 'string', format: 'date-time', required: true }
};

// Student assignment submission schema
const assignmentSubmissionSchema = {
    id: { type: 'string', required: true },
    assignmentId: { type: 'string', required: true },
    studentId: { type: 'string', required: true },
    submissionDate: { type: 'string', format: 'date-time', required: true },
    content: { type: 'string', required: false },
    attachments: { type: 'array', items: { type: 'string' }, required: false },
    score: { type: 'number', required: false },
    maxScore: { type: 'number', required: false },
    feedback: { type: 'string', required: false },
    gradedBy: { type: 'string', required: false },
    gradedDate: { type: 'string', format: 'date-time', required: false },
    createdAt: { type: 'string', format: 'date-time', required: true },
    updatedAt: { type: 'string', format: 'date-time', required: true }
};

// Export schemas
module.exports = {
    userSchema,
    courseSchema,
    activitySchema,
    settingsSchema,
    quizSchema,
    assignmentSchema,
    quizSubmissionSchema,
    assignmentSubmissionSchema
}; 