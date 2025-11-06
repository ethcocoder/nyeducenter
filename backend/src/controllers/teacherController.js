const db = require('../utils/database');
const { validateCourse, validateQuiz, validateAssignment } = require('../utils/validator');

const teacherController = {
  // Dashboard
  getDashboard: async (req, res) => {
    try {
      const teacherId = req.user.id; // Assuming user ID is attached by auth middleware

      // 1. Get courses taught by the teacher
      const courses = await db.findRecords('courses', { teacherId: teacherId });

      if (!courses || courses.length === 0) {
        return res.status(200).json({
          success: true,
          message: 'No courses assigned to this teacher yet.',
          data: { courses: [], stats: { totalStudents: 0, totalAssignments: 0, totalQuizzes: 0 } }
        });
      }

      // 2. Calculate some basic stats (example: total students across assigned courses)
      let totalStudents = 0;
      let totalAssignments = 0;
      let totalQuizzes = 0;

      // We might need to fetch enrollments, assignments, quizzes for each course
      // This could become complex/slow if not optimized. For a basic dashboard, let's stick to course count for now.
      // For more detailed stats, separate queries or aggregation might be needed.

      // Example: Fetch assignment and quiz counts for the teacher's courses
      const courseIds = courses.map(c => c.id);
      const assignments = await db.findRecords('assignments', { courseId: { $in: courseIds } }); // Needs $in support in findRecords
      const quizzes = await db.findRecords('quizzes', { courseId: { $in: courseIds } }); // Needs $in support in findRecords

      totalAssignments = assignments.length;
      totalQuizzes = quizzes.length;

      // Note: Calculating total unique students requires fetching enrollments
      // const enrollments = await db.findRecords('enrollments', { courseId: { $in: courseIds } });
      // const uniqueStudentIds = new Set(enrollments.map(e => e.studentId));
      // totalStudents = uniqueStudentIds.size;

      res.status(200).json({
        success: true,
        data: {
          courses: courses.map(c => ({ id: c.id, name: c.name, code: c.code })), // Send minimal course info
          stats: {
            totalCourses: courses.length,
            totalAssignments,
            totalQuizzes
            // totalStudents // Add this if calculated
          }
          // Could add recent activity feed here later
        }
      });
    } catch (error) {
      console.error("Error fetching teacher dashboard:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve dashboard data',
        error: error.message
      });
    }
  },

  // Course Management
  getCourses: async (req, res) => {
    try {
      const teacherId = req.user.id;
      
      // Get all courses taught by this teacher
      const courses = await db.findRecords('courses', { teacherId });
      
      if (!courses || courses.length === 0) {
        return res.status(200).json({
          success: true,
          message: 'No courses found for this teacher',
          data: []
        });
      }
      
      // Return course list with basic info
      res.status(200).json({
        success: true,
        data: courses.map(course => ({
          id: course.id,
          name: course.name,
          code: course.code,
          description: course.description,
          createdAt: course.createdAt,
          updatedAt: course.updatedAt
        }))
      });
    } catch (error) {
      console.error("Error fetching teacher courses:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve courses',
        error: error.message
      });
    }
  },

  getCourseById: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { courseId } = req.params;
      
      // Get the specific course
      const course = await db.findOneRecord('courses', { 
        id: courseId, 
        teacherId // Ensure teacher can only access their own courses
      });
      
      if (!course) {
        return res.status(404).json({
          success: false,
          message: 'Course not found or you do not have access to this course'
        });
      }
      
      // Get enrollments for this course
      const enrollments = await db.findRecords('enrollments', { courseId });
      
      // Get student details for each enrollment
      const enrolledStudents = await Promise.all(
        enrollments.map(async enrollment => {
          const student = await db.findOneRecord('users', { id: enrollment.studentId });
          // Filter out sensitive information
          if (student) {
            const { password, ...safeStudentData } = student;
            return {
              ...safeStudentData,
              enrollmentId: enrollment.id,
              enrollmentDate: enrollment.createdAt
            };
          }
          return null;
        })
      ).then(students => students.filter(student => student !== null));
      
      // Get assignments for this course
      const assignments = await db.findRecords('assignments', { courseId });
      
      // Get quizzes for this course
      const quizzes = await db.findRecords('quizzes', { courseId });
      
      // Return comprehensive course data
      res.status(200).json({
        success: true,
        data: {
          ...course,
          enrolledStudents,
          assignments: assignments.map(a => ({
            id: a.id,
            title: a.title,
            description: a.description,
            dueDate: a.dueDate,
            createdAt: a.createdAt
          })),
          quizzes: quizzes.map(q => ({
            id: q.id,
            title: q.title,
            description: q.description,
            scheduledFor: q.scheduledFor,
            expiresAt: q.expiresAt,
            createdAt: q.createdAt
          }))
        }
      });
    } catch (error) {
      console.error("Error fetching course details:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve course details',
        error: error.message
      });
    }
  },

  // Student Management (within a course context)
  getStudentsInCourse: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { courseId } = req.params;
      
      // First, verify that this course belongs to the teacher
      const course = await db.findOneRecord('courses', { id: courseId, teacherId });
      
      if (!course) {
        return res.status(404).json({
          success: false,
          message: 'Course not found or you do not have access to this course'
        });
      }
      
      // Get enrollments for this course
      const enrollments = await db.findRecords('enrollments', { courseId });
      
      if (!enrollments || enrollments.length === 0) {
        return res.status(200).json({
          success: true,
          message: 'No students enrolled in this course',
          data: []
        });
      }
      
      // Get student details for each enrollment
      const students = await Promise.all(
        enrollments.map(async enrollment => {
          const student = await db.findOneRecord('users', { id: enrollment.studentId });
          
          if (student) {
            // Remove sensitive information
            const { password, ...safeStudentData } = student;
            
            // Get assignment submissions for this student in this course
            const assignmentSubmissions = await db.findRecords('assignment_submissions', {
              studentId: student.id,
              courseId
            });
            
            // Get quiz attempts for this student in this course
            const quizAttempts = await db.findRecords('quiz_attempts', {
              studentId: student.id,
              quizId: { $in: (await db.findRecords('quizzes', { courseId })).map(q => q.id) }
            });
            
            // Calculate submission stats
            const submissionStats = {
              totalSubmissions: assignmentSubmissions.length,
              pendingGrading: assignmentSubmissions.filter(s => s.score === null).length,
              quizAttempts: quizAttempts.length
            };
            
            return {
              ...safeStudentData,
              enrollmentId: enrollment.id,
              enrollmentDate: enrollment.createdAt,
              submissionStats
            };
          }
          return null;
        })
      ).then(students => students.filter(student => student !== null));
      
      res.status(200).json({
        success: true,
        data: students
      });
    } catch (error) {
      console.error("Error fetching students in course:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve students',
        error: error.message
      });
    }
  },

  // Assignment Management
  createAssignment: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { 
        title, 
        description, 
        courseId, 
        dueDate, 
        totalPoints,
        attachments,
        instructions 
      } = req.body;
      
      // Validate assignment data
      const { isValid, errors } = validateAssignment(req.body);
      
      if (!isValid) {
        return res.status(400).json({
          success: false,
          message: 'Invalid assignment data',
          errors
        });
      }
      
      // Verify the course exists and belongs to this teacher
      const course = await db.findOneRecord('courses', { id: courseId, teacherId });
      
      if (!course) {
        return res.status(404).json({
          success: false,
          message: 'Course not found or you do not have access to this course'
        });
      }
      
      // Create the assignment
      const newAssignment = await db.createRecord('assignments', {
        title,
        description,
        courseId,
        teacherId,
        dueDate,
        totalPoints: totalPoints || 100, // Default to 100 points if not specified
        attachments: attachments || [],
        instructions: instructions || ''
      });
      
      res.status(201).json({
        success: true,
        message: 'Assignment created successfully',
        data: newAssignment
      });
    } catch (error) {
      console.error("Error creating assignment:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to create assignment',
        error: error.message
      });
    }
  },

  getAssignments: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { courseId } = req.query; // Optional filter by course ID
      
      let assignments;
      
      if (courseId) {
        // Verify the course belongs to this teacher
        const course = await db.findOneRecord('courses', { id: courseId, teacherId });
        
        if (!course) {
          return res.status(404).json({
            success: false,
            message: 'Course not found or you do not have access to this course'
          });
        }
        
        // Get assignments for the specific course
        assignments = await db.findRecords('assignments', { courseId, teacherId });
      } else {
        // Get all assignments by this teacher
        assignments = await db.findRecords('assignments', { teacherId });
      }
      
      if (!assignments || assignments.length === 0) {
        return res.status(200).json({
          success: true,
          message: courseId 
            ? 'No assignments found for this course'
            : 'No assignments found',
          data: []
        });
      }
      
      // For each assignment, get submission stats
      const assignmentsWithStats = await Promise.all(
        assignments.map(async assignment => {
          // Get submissions for this assignment
          const submissions = await db.findRecords('assignment_submissions', { assignmentId: assignment.id });
          
          // Course details
          const course = await db.findOneRecord('courses', { id: assignment.courseId });
          
          return {
            ...assignment,
            courseName: course ? course.name : 'Unknown Course',
            stats: {
              totalSubmissions: submissions.length,
              graded: submissions.filter(s => s.score !== null).length,
              pending: submissions.filter(s => s.score === null).length
            }
          };
        })
      );
      
      res.status(200).json({
        success: true,
        data: assignmentsWithStats
      });
    } catch (error) {
      console.error("Error fetching assignments:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve assignments',
        error: error.message
      });
    }
  },

  getAssignmentById: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { assignmentId } = req.params;
      
      // Get the assignment
      const assignment = await db.findOneRecord('assignments', { id: assignmentId, teacherId });
      
      if (!assignment) {
        return res.status(404).json({
          success: false,
          message: 'Assignment not found or you do not have access to this assignment'
        });
      }
      
      // Get course details
      const course = await db.findOneRecord('courses', { id: assignment.courseId });
      
      // Get all submissions for this assignment
      const submissions = await db.findRecords('assignment_submissions', { assignmentId: assignment.id });
      
      // Get student details for each submission
      const submissionsWithStudentInfo = await Promise.all(
        submissions.map(async submission => {
          const student = await db.findOneRecord('users', { id: submission.studentId });
          
          return {
            ...submission,
            studentName: student ? `${student.firstName} ${student.lastName}` : 'Unknown Student',
            studentEmail: student ? student.email : 'No Email',
            submissionDate: submission.createdAt,
            isGraded: submission.score !== null
          };
        })
      );
      
      // Return assignment with detailed submission info
      res.status(200).json({
        success: true,
        data: {
          ...assignment,
          courseName: course ? course.name : 'Unknown Course',
          submissions: submissionsWithStudentInfo
        }
      });
    } catch (error) {
      console.error("Error fetching assignment details:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve assignment details',
        error: error.message
      });
    }
  },

  updateAssignment: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { assignmentId } = req.params;
      
      // First, check if the assignment exists and belongs to this teacher
      const existingAssignment = await db.findOneRecord('assignments', { 
        id: assignmentId,
        teacherId
      });
      
      if (!existingAssignment) {
        return res.status(404).json({
          success: false,
          message: 'Assignment not found or you do not have access to this assignment'
        });
      }
      
      // Validate the update data
      const updateData = req.body;
      
      // Don't allow changing the teacherId or courseId
      delete updateData.teacherId;
      delete updateData.courseId;
      
      const { isValid, errors } = validateAssignment({
        ...existingAssignment, // Start with existing data
        ...updateData // Override with updates
      });
      
      if (!isValid) {
        return res.status(400).json({
          success: false,
          message: 'Invalid assignment data',
          errors
        });
      }
      
      // Update the assignment
      const updatedAssignment = await db.updateRecord('assignments', assignmentId, updateData);
      
      res.status(200).json({
        success: true,
        message: 'Assignment updated successfully',
        data: updatedAssignment
      });
    } catch (error) {
      console.error("Error updating assignment:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to update assignment',
        error: error.message
      });
    }
  },

  deleteAssignment: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { assignmentId } = req.params;
      
      // Check if the assignment exists and belongs to this teacher
      const assignment = await db.findOneRecord('assignments', { 
        id: assignmentId,
        teacherId
      });
      
      if (!assignment) {
        return res.status(404).json({
          success: false,
          message: 'Assignment not found or you do not have access to this assignment'
        });
      }
      
      // Check if there are any submissions for this assignment
      const submissions = await db.findRecords('assignment_submissions', { assignmentId });
      
      if (submissions && submissions.length > 0) {
        return res.status(400).json({
          success: false,
          message: 'Cannot delete an assignment that has submissions',
          data: {
            submissionCount: submissions.length
          }
        });
      }
      
      // Delete the assignment
      await db.deleteRecord('assignments', assignmentId);
      
      res.status(200).json({
        success: true,
        message: 'Assignment deleted successfully'
      });
    } catch (error) {
      console.error("Error deleting assignment:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to delete assignment',
        error: error.message
      });
    }
  },

  getSubmissionsForAssignment: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { assignmentId } = req.params;
      
      // Check if the assignment exists and belongs to this teacher
      const assignment = await db.findOneRecord('assignments', { 
        id: assignmentId,
        teacherId
      });
      
      if (!assignment) {
        return res.status(404).json({
          success: false,
          message: 'Assignment not found or you do not have access to this assignment'
        });
      }
      
      // Get all submissions for this assignment
      const submissions = await db.findRecords('assignment_submissions', { assignmentId });
      
      if (!submissions || submissions.length === 0) {
        return res.status(200).json({
          success: true,
          message: 'No submissions found for this assignment',
          data: []
        });
      }
      
      // Get student details for each submission
      const submissionsWithStudentInfo = await Promise.all(
        submissions.map(async submission => {
          const student = await db.findOneRecord('users', { id: submission.studentId });
          
          return {
            ...submission,
            studentName: student ? `${student.firstName} ${student.lastName}` : 'Unknown Student',
            studentEmail: student ? student.email : 'No Email',
            submissionDate: submission.createdAt,
            isGraded: submission.score !== null,
            isLate: new Date(submission.createdAt) > new Date(assignment.dueDate)
          };
        })
      );
      
      res.status(200).json({
        success: true,
        data: {
          assignment: {
            id: assignment.id,
            title: assignment.title,
            dueDate: assignment.dueDate,
            totalPoints: assignment.totalPoints
          },
          submissions: submissionsWithStudentInfo
        }
      });
    } catch (error) {
      console.error("Error fetching submissions:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve submissions',
        error: error.message
      });
    }
  },

  gradeSubmission: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { submissionId } = req.params;
      const { score, feedback } = req.body;
      
      if (score === undefined) {
        return res.status(400).json({
          success: false,
          message: 'Score is required'
        });
      }
      
      // Validate score is a number and within range
      if (isNaN(score) || score < 0) {
        return res.status(400).json({
          success: false,
          message: 'Score must be a non-negative number'
        });
      }
      
      // Get the submission
      const submission = await db.findOneRecord('assignment_submissions', { id: submissionId });
      
      if (!submission) {
        return res.status(404).json({
          success: false,
          message: 'Submission not found'
        });
      }
      
      // Get the assignment to verify it belongs to this teacher
      const assignment = await db.findOneRecord('assignments', { 
        id: submission.assignmentId,
        teacherId
      });
      
      if (!assignment) {
        return res.status(403).json({
          success: false,
          message: 'You do not have permission to grade this submission'
        });
      }
      
      // Ensure score doesn't exceed max points
      const finalScore = Math.min(score, assignment.totalPoints || 100);
      
      // Update the submission with grade and feedback
      const updatedSubmission = await db.updateRecord('assignment_submissions', submissionId, {
        score: finalScore,
        feedback: feedback || '',
        gradedBy: teacherId,
        gradedDate: new Date().toISOString()
      });
      
      // Get student details for the response
      const student = await db.findOneRecord('users', { id: submission.studentId });
      
      res.status(200).json({
        success: true,
        message: 'Submission graded successfully',
        data: {
          ...updatedSubmission,
          assignmentTitle: assignment.title,
          studentName: student ? `${student.firstName} ${student.lastName}` : 'Unknown Student'
        }
      });
    } catch (error) {
      console.error("Error grading submission:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to grade submission',
        error: error.message
      });
    }
  },

  // Quiz Management
  createQuiz: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { 
        title, 
        description, 
        courseId,
        scheduledFor,
        expiresAt,
        timeLimit,
        maxAttempts,
        questions 
      } = req.body;
      
      // Validate quiz data
      const { isValid, errors } = validateQuiz(req.body);
      
      if (!isValid) {
        return res.status(400).json({
          success: false,
          message: 'Invalid quiz data',
          errors
        });
      }
      
      // Verify the course exists and belongs to this teacher
      const course = await db.findOneRecord('courses', { id: courseId, teacherId });
      
      if (!course) {
        return res.status(404).json({
          success: false,
          message: 'Course not found or you do not have access to this course'
        });
      }
      
      // Validate questions (basic validation, more detailed validation would be in validateQuiz)
      if (!questions || !Array.isArray(questions) || questions.length === 0) {
        return res.status(400).json({
          success: false,
          message: 'Quiz must have at least one question'
        });
      }
      
      // Ensure each question has a unique ID
      const processedQuestions = questions.map(question => ({
        ...question,
        id: question.id || db.generateId()
      }));
      
      // Create the quiz
      const newQuiz = await db.createRecord('quizzes', {
        title,
        description,
        courseId,
        teacherId,
        courseName: course.name, // Store course name for easier reference
        scheduledFor,
        expiresAt,
        timeLimit: timeLimit || 60, // Default to 60 minutes if not specified
        maxAttempts: maxAttempts || 1, // Default to 1 attempt if not specified
        questions: processedQuestions,
        isPublished: true,
        allowReview: true // Allow review by default
      });
      
      res.status(201).json({
        success: true,
        message: 'Quiz created successfully',
        data: newQuiz
      });
    } catch (error) {
      console.error("Error creating quiz:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to create quiz',
        error: error.message
      });
    }
  },

  getQuizzes: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { courseId } = req.query; // Optional filter by course ID
      
      let quizzes;
      
      if (courseId) {
        // Verify the course belongs to this teacher
        const course = await db.findOneRecord('courses', { id: courseId, teacherId });
        
        if (!course) {
          return res.status(404).json({
            success: false,
            message: 'Course not found or you do not have access to this course'
          });
        }
        
        // Get quizzes for the specific course
        quizzes = await db.findRecords('quizzes', { courseId, teacherId });
      } else {
        // Get all quizzes by this teacher
        quizzes = await db.findRecords('quizzes', { teacherId });
      }
      
      if (!quizzes || quizzes.length === 0) {
        return res.status(200).json({
          success: true,
          message: courseId 
            ? 'No quizzes found for this course'
            : 'No quizzes found',
          data: []
        });
      }
      
      // For each quiz, get attempt stats and filter out question details (for security)
      const quizzesWithStats = await Promise.all(
        quizzes.map(async quiz => {
          // Get attempts for this quiz
          const attempts = await db.findRecords('quiz_attempts', { quizId: quiz.id });
          
          // Create a safe version without questions (to reduce payload size)
          const { questions, ...safeQuiz } = quiz;
          
          return {
            ...safeQuiz,
            questionCount: questions.length,
            stats: {
              totalAttempts: attempts.length,
              uniqueStudents: new Set(attempts.map(a => a.studentId)).size,
              averageScore: attempts.length > 0 
                ? attempts.reduce((sum, a) => sum + (a.score || 0), 0) / attempts.length 
                : 0
            }
          };
        })
      );
      
      res.status(200).json({
        success: true,
        data: quizzesWithStats
      });
    } catch (error) {
      console.error("Error fetching quizzes:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve quizzes',
        error: error.message
      });
    }
  },

  getQuizById: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { quizId } = req.params;
      
      // Get the quiz
      const quiz = await db.findOneRecord('quizzes', { id: quizId, teacherId });
      
      if (!quiz) {
        return res.status(404).json({
          success: false,
          message: 'Quiz not found or you do not have access to this quiz'
        });
      }
      
      // Get course details (optional)
      const course = await db.findOneRecord('courses', { id: quiz.courseId });
      
      // Get attempt statistics
      const attempts = await db.findRecords('quiz_attempts', { quizId });
      
      // Calculate statistics
      const completedAttempts = attempts.filter(a => a.status === 'completed');
      const stats = {
        totalAttempts: attempts.length,
        completedAttempts: completedAttempts.length,
        inProgressAttempts: attempts.filter(a => a.status === 'in-progress').length,
        uniqueStudents: new Set(attempts.map(a => a.studentId)).size,
        averageScore: completedAttempts.length > 0 
          ? completedAttempts.reduce((sum, a) => sum + (a.score || 0), 0) / completedAttempts.length 
          : 0,
        highestScore: completedAttempts.length > 0 
          ? Math.max(...completedAttempts.map(a => a.score || 0)) 
          : 0,
        lowestScore: completedAttempts.length > 0 
          ? Math.min(...completedAttempts.map(a => a.score || 0)) 
          : 0
      };
      
      res.status(200).json({
        success: true,
        data: {
          ...quiz,
          courseName: course ? course.name : 'Unknown Course',
          stats
        }
      });
    } catch (error) {
      console.error("Error fetching quiz details:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve quiz details',
        error: error.message
      });
    }
  },

  updateQuiz: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { quizId } = req.params;
      
      // First, check if the quiz exists and belongs to this teacher
      const existingQuiz = await db.findOneRecord('quizzes', { 
        id: quizId,
        teacherId
      });
      
      if (!existingQuiz) {
        return res.status(404).json({
          success: false,
          message: 'Quiz not found or you do not have access to this quiz'
        });
      }
      
      // Get update data from request body
      const updateData = { ...req.body };
      
      // Don't allow changing the teacherId or courseId
      delete updateData.teacherId;
      delete updateData.courseId;
      
      // Check if questions are being updated
      if (updateData.questions) {
        // Ensure each question has an ID
        updateData.questions = updateData.questions.map(question => ({
          ...question,
          id: question.id || db.generateId()
        }));
      }
      
      // Validate the update data
      const { isValid, errors } = validateQuiz({
        ...existingQuiz, // Start with existing data
        ...updateData // Override with updates
      });
      
      if (!isValid) {
        return res.status(400).json({
          success: false,
          message: 'Invalid quiz data',
          errors
        });
      }
      
      // Check if there are any active attempts for this quiz before allowing updates
      const activeAttempts = await db.findRecords('quiz_attempts', { 
        quizId, 
        status: 'in-progress' 
      });
      
      if (activeAttempts && activeAttempts.length > 0) {
        return res.status(400).json({
          success: false,
          message: 'Cannot update quiz while students are actively taking it',
          data: {
            activeAttempts: activeAttempts.length
          }
        });
      }
      
      // Update the quiz
      const updatedQuiz = await db.updateRecord('quizzes', quizId, updateData);
      
      res.status(200).json({
        success: true,
        message: 'Quiz updated successfully',
        data: updatedQuiz
      });
    } catch (error) {
      console.error("Error updating quiz:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to update quiz',
        error: error.message
      });
    }
  },

  deleteQuiz: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { quizId } = req.params;
      
      // Check if the quiz exists and belongs to this teacher
      const quiz = await db.findOneRecord('quizzes', { 
        id: quizId,
        teacherId
      });
      
      if (!quiz) {
        return res.status(404).json({
          success: false,
          message: 'Quiz not found or you do not have access to this quiz'
        });
      }
      
      // Check if there are any attempts for this quiz
      const attempts = await db.findRecords('quiz_attempts', { quizId });
      
      if (attempts && attempts.length > 0) {
        return res.status(400).json({
          success: false,
          message: 'Cannot delete a quiz that has student attempts',
          data: {
            attemptCount: attempts.length
          }
        });
      }
      
      // Delete the quiz
      await db.deleteRecord('quizzes', quizId);
      
      res.status(200).json({
        success: true,
        message: 'Quiz deleted successfully'
      });
    } catch (error) {
      console.error("Error deleting quiz:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to delete quiz',
        error: error.message
      });
    }
  },

  getAttemptsForQuiz: async (req, res) => {
    try {
      const teacherId = req.user.id;
      const { quizId } = req.params;
      
      // Check if the quiz exists and belongs to this teacher
      const quiz = await db.findOneRecord('quizzes', { 
        id: quizId,
        teacherId
      });
      
      if (!quiz) {
        return res.status(404).json({
          success: false,
          message: 'Quiz not found or you do not have access to this quiz'
        });
      }
      
      // Get all attempts for this quiz
      const attempts = await db.findRecords('quiz_attempts', { quizId });
      
      if (!attempts || attempts.length === 0) {
        return res.status(200).json({
          success: true,
          message: 'No attempts found for this quiz',
          data: []
        });
      }
      
      // Get student details for each attempt
      const attemptsWithStudentInfo = await Promise.all(
        attempts.map(async attempt => {
          const student = await db.findOneRecord('users', { id: attempt.studentId });
          
          return {
            ...attempt,
            studentName: student ? `${student.firstName} ${student.lastName}` : 'Unknown Student',
            studentEmail: student ? student.email : 'No Email'
          };
        })
      );
      
      res.status(200).json({
        success: true,
        data: {
          quiz: {
            id: quiz.id,
            title: quiz.title,
            courseId: quiz.courseId,
            maxAttempts: quiz.maxAttempts,
            timeLimit: quiz.timeLimit
          },
          attempts: attemptsWithStudentInfo
        }
      });
    } catch (error) {
      console.error("Error fetching quiz attempts:", error);
      res.status(500).json({
        success: false,
        message: 'Failed to retrieve quiz attempts',
        error: error.message
      });
    }
  },

  // Potentially other teacher-specific actions like managing announcements, etc.

};

module.exports = { teacherController }; 