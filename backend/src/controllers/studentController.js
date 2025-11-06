const db = require('../utils/db');
const { validateAssignmentSubmission, validateQuizSubmission } = require('../utils/validator');

const studentController = {
  // Dashboard data
  getDashboard: async (req, res) => {
    try {
      const userId = req.user.id;
      
      // Get upcoming assignments
      const assignments = await db.findRecords('assignments', {
        deadline: { $gt: new Date().toISOString() }
      });
      
      // Get upcoming quizzes
      const quizzes = await db.findRecords('quizzes', {
        scheduledFor: { $gt: new Date().toISOString() }
      });
      
      // Get recent grades
      const grades = await db.findRecords('grades', { studentId: userId });
      
      // Get enrolled courses
      const enrollments = await db.findRecords('enrollments', { studentId: userId });
      const courseIds = enrollments.map(enrollment => enrollment.courseId);
      const courses = await Promise.all(
        courseIds.map(id => db.findOneRecord('courses', { id }))
      );
      
      res.status(200).json({
        success: true,
        data: {
          upcomingAssignments: assignments.slice(0, 5),
          upcomingQuizzes: quizzes.slice(0, 5),
          recentGrades: grades.slice(0, 5),
          courses
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve dashboard data',
        error: error.message
      });
    }
  },
  
  // Profile management
  getProfile: async (req, res) => {
    try {
      const userId = req.user.id;
      const user = await db.findOneRecord('users', { id: userId });
      
      if (!user) {
        return res.status(404).json({
          success: false,
          message: 'User not found'
        });
      }
      
      // Remove sensitive information
      const { password, ...userData } = user;
      
      res.status(200).json({
        success: true,
        data: userData
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve profile',
        error: error.message
      });
    }
  },
  
  updateProfile: async (req, res) => {
    try {
      const userId = req.user.id;
      const { name, email, phone, address } = req.body;
      
      // Only allow specific fields to be updated
      const updateData = {};
      if (name) updateData.name = name;
      if (email) updateData.email = email;
      if (phone) updateData.phone = phone;
      if (address) updateData.address = address;
      
      const updated = await db.updateRecord('users', { id: userId }, updateData);
      
      if (!updated) {
        return res.status(404).json({
          success: false,
          message: 'User not found'
        });
      }
      
      res.status(200).json({
        success: true,
        message: 'Profile updated successfully',
        data: updated
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to update profile',
        error: error.message
      });
    }
  },
  
  // Course management
  getCourses: async (req, res) => {
    try {
      const userId = req.user.id;
      
      // Get enrolled courses
      const enrollments = await db.findRecords('enrollments', { studentId: userId });
      const courseIds = enrollments.map(enrollment => enrollment.courseId);
      const courses = await Promise.all(
        courseIds.map(id => db.findOneRecord('courses', { id }))
      );
      
      res.status(200).json({
        success: true,
        data: courses
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve courses',
        error: error.message
      });
    }
  },
  
  getCourseById: async (req, res) => {
    try {
      const userId = req.user.id;
      const { courseId } = req.params;
      
      // Check if student is enrolled in this course
      const enrollment = await db.findOneRecord('enrollments', { 
        studentId: userId,
        courseId 
      });
      
      if (!enrollment) {
        return res.status(403).json({
          success: false,
          message: 'You are not enrolled in this course'
        });
      }
      
      // Get course details
      const course = await db.findOneRecord('courses', { id: courseId });
      
      if (!course) {
        return res.status(404).json({
          success: false,
          message: 'Course not found'
        });
      }
      
      // Get course materials
      const materials = await db.findRecords('materials', { courseId });
      
      // Get course assignments
      const assignments = await db.findRecords('assignments', { courseId });
      
      // Get course quizzes
      const quizzes = await db.findRecords('quizzes', { courseId });
      
      res.status(200).json({
        success: true,
        data: {
          ...course,
          materials,
          assignments,
          quizzes
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve course details',
        error: error.message
      });
    }
  },
  
  // Assignment management
  getAssignments: async (req, res) => {
    try {
      const userId = req.user.id;
      
      // Get enrolled courses
      const enrollments = await db.findRecords('enrollments', { studentId: userId });
      const courseIds = enrollments.map(enrollment => enrollment.courseId);
      
      // Get assignments for enrolled courses
      const assignments = await db.findRecords('assignments', { 
        courseId: { $in: courseIds } 
      });
      
      // Get submission status for each assignment
      const assignmentsWithStatus = await Promise.all(
        assignments.map(async (assignment) => {
          const submission = await db.findOneRecord('submissions', {
            assignmentId: assignment.id,
            studentId: userId
          });
          
          return {
            ...assignment,
            isSubmitted: !!submission,
            submission: submission || null
          };
        })
      );
      
      res.status(200).json({
        success: true,
        data: assignmentsWithStatus
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve assignments',
        error: error.message
      });
    }
  },
  
  getAssignmentById: async (req, res) => {
    try {
      const userId = req.user.id;
      const { assignmentId } = req.params;
      
      // Get assignment details
      const assignment = await db.findOneRecord('assignments', { id: assignmentId });
      
      if (!assignment) {
        return res.status(404).json({
          success: false,
          message: 'Assignment not found'
        });
      }
      
      // Check if student is enrolled in the course for this assignment
      const enrollment = await db.findOneRecord('enrollments', {
        studentId: userId,
        courseId: assignment.courseId
      });
      
      if (!enrollment) {
        return res.status(403).json({
          success: false,
          message: 'You do not have access to this assignment'
        });
      }
      
      // Get submission if exists
      const submission = await db.findOneRecord('submissions', {
        assignmentId,
        studentId: userId
      });
      
      res.status(200).json({
        success: true,
        data: {
          ...assignment,
          isSubmitted: !!submission,
          submission: submission || null
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve assignment details',
        error: error.message
      });
    }
  },
  
  submitAssignment: async (req, res) => {
    try {
      const userId = req.user.id;
      const { assignmentId } = req.params;
      const { content, attachments } = req.body;
      
      // Validate submission
      const { isValid, errors } = validateAssignmentSubmission(req.body);
      if (!isValid) {
        return res.status(400).json({
          success: false,
          errors
        });
      }
      
      // Check if assignment exists
      const assignment = await db.findOneRecord('assignments', { id: assignmentId });
      
      if (!assignment) {
        return res.status(404).json({
          success: false,
          message: 'Assignment not found'
        });
      }
      
      // Check if student is enrolled in the course
      const enrollment = await db.findOneRecord('enrollments', {
        studentId: userId,
        courseId: assignment.courseId
      });
      
      if (!enrollment) {
        return res.status(403).json({
          success: false,
          message: 'You do not have access to this assignment'
        });
      }
      
      // Check deadline
      if (new Date(assignment.deadline) < new Date()) {
        return res.status(400).json({
          success: false,
          message: 'Assignment deadline has passed'
        });
      }
      
      // Check if already submitted
      const existingSubmission = await db.findOneRecord('submissions', {
        assignmentId,
        studentId: userId
      });
      
      if (existingSubmission) {
        // Update existing submission
        const updated = await db.updateRecord('submissions', {
          id: existingSubmission.id
        }, {
          content,
          attachments,
          submittedAt: new Date().toISOString(),
          status: 'submitted'
        });
        
        return res.status(200).json({
          success: true,
          message: 'Assignment submission updated',
          data: updated
        });
      }
      
      // Create new submission
      const submission = await db.createRecord('submissions', {
        assignmentId,
        studentId: userId,
        content,
        attachments,
        submittedAt: new Date().toISOString(),
        status: 'submitted',
        grade: null,
        feedback: null
      });
      
      res.status(201).json({
        success: true,
        message: 'Assignment submitted successfully',
        data: submission
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to submit assignment',
        error: error.message
      });
    }
  },
  
  getSubmissions: async (req, res) => {
    try {
      const userId = req.user.id;
      
      // Get all submissions by the student
      const submissions = await db.findRecords('submissions', { studentId: userId });
      
      // Get assignment details for each submission
      const submissionsWithDetails = await Promise.all(
        submissions.map(async (submission) => {
          const assignment = await db.findOneRecord('assignments', { 
            id: submission.assignmentId 
          });
          
          return {
            ...submission,
            assignment
          };
        })
      );
      
      res.status(200).json({
        success: true,
        data: submissionsWithDetails
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve submissions',
        error: error.message
      });
    }
  },
  
  // Quiz management
  getQuizzes: async (req, res) => {
    try {
      const userId = req.user.id;
      
      // Get enrolled courses
      const enrollments = await db.findRecords('enrollments', { studentId: userId });
      const courseIds = enrollments.map(enrollment => enrollment.courseId);
      
      // Get quizzes for enrolled courses
      const quizzes = await db.findRecords('quizzes', { 
        courseId: { $in: courseIds } 
      });
      
      // Get attempt status for each quiz
      const quizzesWithStatus = await Promise.all(
        quizzes.map(async (quiz) => {
          const attempts = await db.findRecords('quiz_attempts', {
            quizId: quiz.id,
            studentId: userId
          });
          
          return {
            ...quiz,
            attempts: attempts.length,
            lastAttempt: attempts.length > 0 ? attempts[attempts.length - 1] : null
          };
        })
      );
      
      res.status(200).json({
        success: true,
        data: quizzesWithStatus
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve quizzes',
        error: error.message
      });
    }
  },
  
  getQuizById: async (req, res) => {
    try {
      const userId = req.user.id;
      const { quizId } = req.params;
      
      // Get quiz details
      const quiz = await db.findOneRecord('quizzes', { id: quizId });
      
      if (!quiz) {
        return res.status(404).json({
          success: false,
          message: 'Quiz not found'
        });
      }
      
      // Check if student is enrolled in the course for this quiz
      const enrollment = await db.findOneRecord('enrollments', {
        studentId: userId,
        courseId: quiz.courseId
      });
      
      if (!enrollment) {
        return res.status(403).json({
          success: false,
          message: 'You do not have access to this quiz'
        });
      }
      
      // Get previous attempts
      const attempts = await db.findRecords('quiz_attempts', {
        quizId,
        studentId: userId
      });
      
      // Check if quiz is available
      const now = new Date();
      const scheduledFor = new Date(quiz.scheduledFor);
      const expiresAt = new Date(quiz.expiresAt);
      
      const isAvailable = now >= scheduledFor && now <= expiresAt;
      const canAttempt = isAvailable && 
        (quiz.maxAttempts === 0 || attempts.length < quiz.maxAttempts);
      
      // Don't include questions if quiz hasn't started or if attempts are maxed out
      const sanitizedQuiz = {
        ...quiz,
        questions: canAttempt && isAvailable ? quiz.questions : undefined,
        attempts: attempts.length,
        previousAttempts: attempts,
        isAvailable,
        canAttempt
      };
      
      res.status(200).json({
        success: true,
        data: sanitizedQuiz
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve quiz details',
        error: error.message
      });
    }
  },
  
  startQuiz: async (req, res) => {
    try {
      const userId = req.user.id;
      const { quizId } = req.params;
      
      // Get quiz details
      const quiz = await db.findOneRecord('quizzes', { id: quizId });
      
      if (!quiz) {
        return res.status(404).json({
          success: false,
          message: 'Quiz not found'
        });
      }
      
      // Check if student is enrolled in the course
      const enrollment = await db.findOneRecord('enrollments', {
        studentId: userId,
        courseId: quiz.courseId
      });
      
      if (!enrollment) {
        return res.status(403).json({
          success: false,
          message: 'You do not have access to this quiz'
        });
      }
      
      // Check if quiz is available
      const now = new Date();
      const scheduledFor = new Date(quiz.scheduledFor);
      const expiresAt = new Date(quiz.expiresAt);
      
      if (now < scheduledFor || now > expiresAt) {
        return res.status(400).json({
          success: false,
          message: 'Quiz is not available at this time'
        });
      }
      
      // Check attempts
      const attempts = await db.findRecords('quiz_attempts', {
        quizId,
        studentId: userId
      });
      
      if (quiz.maxAttempts > 0 && attempts.length >= quiz.maxAttempts) {
        return res.status(400).json({
          success: false,
          message: 'Maximum attempts reached for this quiz'
        });
      }
      
      // Create a new attempt
      const endTime = new Date();
      endTime.setMinutes(endTime.getMinutes() + quiz.timeLimit);
      
      const attempt = await db.createRecord('quiz_attempts', {
        quizId,
        studentId: userId,
        startedAt: now.toISOString(),
        endsAt: endTime.toISOString(),
        status: 'in-progress',
        answers: [],
        score: null,
        attemptNumber: attempts.length + 1
      });
      
      // Send quiz without answers
      const questionsWithoutAnswers = quiz.questions.map(q => {
        const { correctOptions, ...questionData } = q;
        return questionData;
      });
      
      res.status(200).json({
        success: true,
        message: 'Quiz started successfully',
        data: {
          ...attempt,
          questions: questionsWithoutAnswers,
          timeLimit: quiz.timeLimit
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to start quiz',
        error: error.message
      });
    }
  },
  
  submitQuiz: async (req, res) => {
    try {
      const userId = req.user.id;
      const { quizId } = req.params;
      const { attemptId, answers } = req.body;
      
      // Validate submission
      const { isValid, errors } = validateQuizSubmission(req.body);
      if (!isValid) {
        return res.status(400).json({
          success: false,
          errors
        });
      }
      
      // Get quiz details
      const quiz = await db.findOneRecord('quizzes', { id: quizId });
      
      if (!quiz) {
        return res.status(404).json({
          success: false,
          message: 'Quiz not found'
        });
      }
      
      // Get attempt
      const attempt = await db.findOneRecord('quiz_attempts', { id: attemptId });
      
      if (!attempt) {
        return res.status(404).json({
          success: false,
          message: 'Quiz attempt not found'
        });
      }
      
      // Verify this is the student's attempt
      if (attempt.studentId !== userId) {
        return res.status(403).json({
          success: false,
          message: 'You cannot submit answers for this attempt'
        });
      }
      
      // Check if attempt is still valid
      const now = new Date();
      const endsAt = new Date(attempt.endsAt);
      
      if (attempt.status !== 'in-progress') {
        return res.status(400).json({
          success: false,
          message: 'This attempt has already been submitted'
        });
      }
      
      if (now > endsAt) {
        return res.status(400).json({
          success: false,
          message: 'Time limit exceeded for this attempt'
        });
      }
      
      // Grade the quiz
      let score = 0;
      const gradedAnswers = answers.map(answer => {
        const question = quiz.questions.find(q => q.id === answer.questionId);
        
        if (!question) return { ...answer, isCorrect: false };
        
        // Check if answer is correct
        let isCorrect = false;
        
        if (question.type === 'multiple-choice') {
          isCorrect = question.correctOptions.includes(answer.selectedOption);
        } else if (question.type === 'multiple-answer') {
          // All selected options must be correct, and all correct options must be selected
          const selectedSet = new Set(answer.selectedOptions);
          const correctSet = new Set(question.correctOptions);
          
          isCorrect = selectedSet.size === correctSet.size && 
            [...selectedSet].every(option => correctSet.has(option));
        }
        
        if (isCorrect) score += question.points || 1;
        
        return {
          ...answer,
          isCorrect
        };
      });
      
      // Calculate percentage score
      const totalPoints = quiz.questions.reduce((total, q) => total + (q.points || 1), 0);
      const percentageScore = Math.round((score / totalPoints) * 100);
      
      // Update attempt
      const updated = await db.updateRecord('quiz_attempts', { id: attemptId }, {
        answers: gradedAnswers,
        score: percentageScore,
        submittedAt: now.toISOString(),
        status: 'completed'
      });
      
      res.status(200).json({
        success: true,
        message: 'Quiz submitted successfully',
        data: {
          ...updated,
          totalPoints,
          earnedPoints: score
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to submit quiz',
        error: error.message
      });
    }
  },
  
  getQuizResults: async (req, res) => {
    try {
      const userId = req.user.id;
      
      // Get all completed quiz attempts
      const attempts = await db.findRecords('quiz_attempts', {
        studentId: userId,
        status: 'completed'
      });
      
      // Get quiz details for each attempt
      const attemptsWithDetails = await Promise.all(
        attempts.map(async (attempt) => {
          const quiz = await db.findOneRecord('quizzes', { id: attempt.quizId });
          
          return {
            ...attempt,
            quizTitle: quiz ? quiz.title : 'Unknown Quiz',
            courseName: quiz ? quiz.courseName : 'Unknown Course'
          };
        })
      );
      
      res.status(200).json({
        success: true,
        data: attemptsWithDetails
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve quiz results',
        error: error.message
      });
    }
  },
  
  getQuizResultById: async (req, res) => {
    try {
      const userId = req.user.id;
      const { resultId } = req.params;
      
      // Get attempt details
      const attempt = await db.findOneRecord('quiz_attempts', { id: resultId });
      
      if (!attempt) {
        return res.status(404).json({
          success: false,
          message: 'Quiz result not found'
        });
      }
      
      // Verify this is the student's attempt
      if (attempt.studentId !== userId) {
        return res.status(403).json({
          success: false,
          message: 'You do not have access to this quiz result'
        });
      }
      
      // Get quiz details
      const quiz = await db.findOneRecord('quizzes', { id: attempt.quizId });
      
      if (!quiz) {
        return res.status(404).json({
          success: false,
          message: 'Quiz not found'
        });
      }
      
      // Get detailed results with questions and answers
      const detailedResults = {
        ...attempt,
        quiz: {
          id: quiz.id,
          title: quiz.title,
          courseName: quiz.courseName,
          courseId: quiz.courseId
        },
        questions: quiz.questions.map(question => {
          const answer = attempt.answers.find(a => a.questionId === question.id);
          
          return {
            question: question.text,
            options: question.options,
            type: question.type,
            points: question.points || 1,
            correctOptions: question.correctOptions,
            studentAnswer: answer ? {
              selectedOption: answer.selectedOption,
              selectedOptions: answer.selectedOptions,
              isCorrect: answer.isCorrect
            } : null
          };
        })
      };
      
      res.status(200).json({
        success: true,
        data: detailedResults
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve quiz result details',
        error: error.message
      });
    }
  },
  
  // Grades
  getGrades: async (req, res) => {
    try {
      const userId = req.user.id;
      
      // Get all grades for the student
      const grades = await db.findRecords('grades', { studentId: userId });
      
      // Get course details for each grade
      const gradesWithDetails = await Promise.all(
        grades.map(async (grade) => {
          const course = await db.findOneRecord('courses', { id: grade.courseId });
          
          return {
            ...grade,
            courseName: course ? course.name : 'Unknown Course'
          };
        })
      );
      
      res.status(200).json({
        success: true,
        data: gradesWithDetails
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve grades',
        error: error.message
      });
    }
  },
  
  getCourseGrades: async (req, res) => {
    try {
      const userId = req.user.id;
      const { courseId } = req.params;
      
      // Check if student is enrolled in this course
      const enrollment = await db.findOneRecord('enrollments', {
        studentId: userId,
        courseId
      });
      
      if (!enrollment) {
        return res.status(403).json({
          success: false,
          message: 'You are not enrolled in this course'
        });
      }
      
      // Get all grades for the student in this course
      const grades = await db.findRecords('grades', {
        studentId: userId,
        courseId
      });
      
      // Get course details
      const course = await db.findOneRecord('courses', { id: courseId });
      
      if (!course) {
        return res.status(404).json({
          success: false,
          message: 'Course not found'
        });
      }
      
      // Calculate overall grade
      const totalWeight = grades.reduce((sum, grade) => sum + grade.weight, 0);
      const weightedSum = grades.reduce(
        (sum, grade) => sum + (grade.score * grade.weight), 0
      );
      
      const overallGrade = totalWeight > 0 ? weightedSum / totalWeight : null;
      
      res.status(200).json({
        success: true,
        data: {
          course: {
            id: course.id,
            name: course.name
          },
          grades,
          overallGrade
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve course grades',
        error: error.message
      });
    }
  }
};

module.exports = { studentController };