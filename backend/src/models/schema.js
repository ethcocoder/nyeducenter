const schema = {
    users: {
        fields: {
            id: { type: 'string', required: true },
            username: { type: 'string', required: true, unique: true },
            password: { type: 'string', required: true },
            role: { type: 'string', required: true, enum: ['admin', 'teacher', 'student'] },
            grade: { type: 'string', enum: ['9', '10', '11', '12'] },
            fullName: { type: 'string', required: true },
            email: { type: 'string', required: true, unique: true },
            phone: { type: 'string' },
            createdAt: { type: 'string', required: true },
            updatedAt: { type: 'string', required: true }
        }
    },
    courses: {
        fields: {
            id: { type: 'string', required: true },
            title: { type: 'string', required: true },
            description: { type: 'string' },
            grade: { type: 'string', required: true, enum: ['9', '10', '11', '12'] },
            teacherId: { type: 'string', required: true },
            createdAt: { type: 'string', required: true },
            updatedAt: { type: 'string', required: true }
        }
    },
    assignments: {
        fields: {
            id: { type: 'string', required: true },
            title: { type: 'string', required: true },
            description: { type: 'string' },
            courseId: { type: 'string', required: true },
            grade: { type: 'string', required: true },
            dueDate: { type: 'string', required: true },
            teacherId: { type: 'string', required: true },
            createdAt: { type: 'string', required: true },
            updatedAt: { type: 'string', required: true }
        }
    },
    studentAssignments: {
        fields: {
            id: { type: 'string', required: true },
            assignmentId: { type: 'string', required: true },
            studentId: { type: 'string', required: true },
            status: { type: 'string', required: true, enum: ['pending', 'submitted', 'graded'] },
            submission: { type: 'string' },
            grade: { type: 'number' },
            feedback: { type: 'string' },
            submittedAt: { type: 'string' },
            gradedAt: { type: 'string' }
        }
    },
    quizzes: {
        fields: {
            id: { type: 'string', required: true },
            title: { type: 'string', required: true },
            description: { type: 'string' },
            courseId: { type: 'string', required: true },
            grade: { type: 'string', required: true },
            teacherId: { type: 'string', required: true },
            questions: { type: 'array', required: true },
            timeLimit: { type: 'number' },
            createdAt: { type: 'string', required: true },
            updatedAt: { type: 'string', required: true }
        }
    },
    studentQuizzes: {
        fields: {
            id: { type: 'string', required: true },
            quizId: { type: 'string', required: true },
            studentId: { type: 'string', required: true },
            answers: { type: 'array' },
            score: { type: 'number' },
            status: { type: 'string', required: true, enum: ['pending', 'in_progress', 'completed'] },
            startedAt: { type: 'string' },
            completedAt: { type: 'string' }
        }
    },
    announcements: {
        fields: {
            id: { type: 'string', required: true },
            title: { type: 'string', required: true },
            content: { type: 'string', required: true },
            authorId: { type: 'string', required: true },
            grade: { type: 'string', enum: ['all', '9', '10', '11', '12'] },
            createdAt: { type: 'string', required: true },
            updatedAt: { type: 'string', required: true }
        }
    },
    forumPosts: {
        fields: {
            id: { type: 'string', required: true },
            title: { type: 'string', required: true },
            content: { type: 'string', required: true },
            authorId: { type: 'string', required: true },
            grade: { type: 'string', enum: ['all', '9', '10', '11', '12'] },
            createdAt: { type: 'string', required: true },
            updatedAt: { type: 'string', required: true }
        }
    },
    forumComments: {
        fields: {
            id: { type: 'string', required: true },
            postId: { type: 'string', required: true },
            content: { type: 'string', required: true },
            authorId: { type: 'string', required: true },
            createdAt: { type: 'string', required: true },
            updatedAt: { type: 'string', required: true }
        }
    },
    messages: {
        fields: {
            id: { type: 'string', required: true },
            senderId: { type: 'string', required: true },
            receiverId: { type: 'string', required: true },
            content: { type: 'string', required: true },
            read: { type: 'boolean', default: false },
            createdAt: { type: 'string', required: true }
        }
    }
};

module.exports = schema; 