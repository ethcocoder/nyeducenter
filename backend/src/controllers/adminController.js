const { 
    findRecords, 
    findOneRecord, 
    createRecord, 
    updateRecord, 
    deleteRecord,
    hashPassword
} = require('../utils/database');

const adminController = {
    // Dashboard statistics
    async getDashboardStats(req, res) {
        try {
            // Get counts for different entities
            const usersCount = (await findRecords('users')).length;
            const coursesCount = (await findRecords('courses')).length;
            const teachersCount = (await findRecords('users', { role: 'teacher' })).length;
            const studentsCount = (await findRecords('users', { role: 'student' })).length;
            
            // Get recent activities
            const recentActivities = await findRecords('activities');
            recentActivities.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
            const latestActivities = recentActivities.slice(0, 10);
            
            res.json({
                counts: {
                    users: usersCount,
                    courses: coursesCount,
                    teachers: teachersCount,
                    students: studentsCount
                },
                recentActivities: latestActivities
            });
        } catch (error) {
            console.error('Error fetching dashboard stats:', error);
            res.status(500).json({ error: 'Failed to fetch dashboard statistics' });
        }
    },

    // User Management
    async getAllUsers(req, res) {
        try {
            const { role, sort, page = 1, limit = 20 } = req.query;
            let query = {};
            
            // Filter by role if provided
            if (role) {
                query.role = role;
            }
            
            const users = await findRecords('users', query);
            
            // Sort users if sort parameter provided
            if (sort) {
                const [field, order] = sort.split(':');
                users.sort((a, b) => {
                    if (order === 'desc') {
                        return a[field] > b[field] ? -1 : 1;
                    }
                    return a[field] > b[field] ? 1 : -1;
                });
            }
            
            // Pagination
            const startIndex = (page - 1) * limit;
            const endIndex = page * limit;
            const paginatedUsers = users.slice(startIndex, endIndex);
            
            // Don't return password hashes
            const safeUsers = paginatedUsers.map(user => {
                const { password, ...safeUser } = user;
                return safeUser;
            });
            
            res.json({
                total: users.length,
                page: parseInt(page),
                limit: parseInt(limit),
                users: safeUsers
            });
        } catch (error) {
            console.error('Error fetching users:', error);
            res.status(500).json({ error: 'Failed to fetch users' });
        }
    },

    async getUserById(req, res) {
        try {
            const user = await findOneRecord('users', { id: req.params.id });
            
            if (!user) {
                return res.status(404).json({ error: 'User not found' });
            }
            
            // Don't return password hash
            const { password, ...safeUser } = user;
            
            res.json(safeUser);
        } catch (error) {
            console.error('Error fetching user:', error);
            res.status(500).json({ error: 'Failed to fetch user' });
        }
    },

    async createUser(req, res) {
        try {
            const { username, password, role, grade, fullName, email } = req.body;
            
            // Validate required fields
            if (!username || !password || !role) {
                return res.status(400).json({ error: 'Username, password, and role are required' });
            }
            
            // Check if username is already taken
            const existingUser = await findOneRecord('users', { username });
            if (existingUser) {
                return res.status(400).json({ error: 'Username already exists' });
            }
            
            // Hash password
            const hashedPassword = await hashPassword(password);
            
            // Create new user
            const newUser = await createRecord('users', {
                username,
                password: hashedPassword,
                role,
                grade: role === 'teacher' || role === 'student' ? grade : null,
                fullName,
                email,
                createdAt: new Date().toISOString(),
                updatedAt: new Date().toISOString()
            });
            
            // Don't return password hash
            const { password: _, ...safeUser } = newUser;
            
            res.status(201).json({
                message: 'User created successfully',
                user: safeUser
            });
        } catch (error) {
            console.error('Error creating user:', error);
            res.status(500).json({ error: 'Failed to create user' });
        }
    },

    async updateUser(req, res) {
        try {
            const userId = req.params.id;
            const { username, password, role, grade, fullName, email } = req.body;
            
            // Check if user exists
            const existingUser = await findOneRecord('users', { id: userId });
            if (!existingUser) {
                return res.status(404).json({ error: 'User not found' });
            }
            
            // If changing username, check if the new username is available
            if (username && username !== existingUser.username) {
                const usernameExists = await findOneRecord('users', { username });
                if (usernameExists) {
                    return res.status(400).json({ error: 'Username already exists' });
                }
            }
            
            // Prepare update data
            const updateData = {
                ...(username && { username }),
                ...(role && { role }),
                ...(fullName && { fullName }),
                ...(email && { email }),
                ...(role === 'teacher' || role === 'student' ? { grade } : { grade: null }),
                updatedAt: new Date().toISOString()
            };
            
            // If password is provided, hash it
            if (password) {
                updateData.password = await hashPassword(password);
            }
            
            // Update user
            const updatedUser = await updateRecord('users', { id: userId }, updateData);
            
            // Don't return password hash
            const { password: _, ...safeUser } = updatedUser;
            
            res.json({
                message: 'User updated successfully',
                user: safeUser
            });
        } catch (error) {
            console.error('Error updating user:', error);
            res.status(500).json({ error: 'Failed to update user' });
        }
    },

    async deleteUser(req, res) {
        try {
            const userId = req.params.id;
            
            // Check if user exists
            const user = await findOneRecord('users', { id: userId });
            if (!user) {
                return res.status(404).json({ error: 'User not found' });
            }
            
            // Delete user
            await deleteRecord('users', { id: userId });
            
            res.json({ message: 'User deleted successfully' });
        } catch (error) {
            console.error('Error deleting user:', error);
            res.status(500).json({ error: 'Failed to delete user' });
        }
    },

    // Course Management
    async getAllCourses(req, res) {
        try {
            const { grade, subject, page = 1, limit = 20 } = req.query;
            let query = {};
            
            // Filter by grade or subject if provided
            if (grade) query.grade = grade;
            if (subject) query.subject = subject;
            
            const courses = await findRecords('courses', query);
            
            // Pagination
            const startIndex = (page - 1) * limit;
            const endIndex = page * limit;
            const paginatedCourses = courses.slice(startIndex, endIndex);
            
            res.json({
                total: courses.length,
                page: parseInt(page),
                limit: parseInt(limit),
                courses: paginatedCourses
            });
        } catch (error) {
            console.error('Error fetching courses:', error);
            res.status(500).json({ error: 'Failed to fetch courses' });
        }
    },

    async getCourseById(req, res) {
        try {
            const course = await findOneRecord('courses', { id: req.params.id });
            
            if (!course) {
                return res.status(404).json({ error: 'Course not found' });
            }
            
            res.json(course);
        } catch (error) {
            console.error('Error fetching course:', error);
            res.status(500).json({ error: 'Failed to fetch course' });
        }
    },

    async createCourse(req, res) {
        try {
            const { title, description, grade, subject, teacherId } = req.body;
            
            // Validate required fields
            if (!title || !grade || !subject) {
                return res.status(400).json({ error: 'Title, grade, and subject are required' });
            }
            
            // Validate teacher if teacherId is provided
            if (teacherId) {
                const teacher = await findOneRecord('users', { id: teacherId, role: 'teacher' });
                if (!teacher) {
                    return res.status(400).json({ error: 'Invalid teacher ID' });
                }
            }
            
            // Create new course
            const newCourse = await createRecord('courses', {
                title,
                description,
                grade,
                subject,
                teacherId,
                createdAt: new Date().toISOString(),
                updatedAt: new Date().toISOString()
            });
            
            res.status(201).json({
                message: 'Course created successfully',
                course: newCourse
            });
        } catch (error) {
            console.error('Error creating course:', error);
            res.status(500).json({ error: 'Failed to create course' });
        }
    },

    async updateCourse(req, res) {
        try {
            const courseId = req.params.id;
            const { title, description, grade, subject, teacherId } = req.body;
            
            // Check if course exists
            const existingCourse = await findOneRecord('courses', { id: courseId });
            if (!existingCourse) {
                return res.status(404).json({ error: 'Course not found' });
            }
            
            // Validate teacher if teacherId is provided
            if (teacherId) {
                const teacher = await findOneRecord('users', { id: teacherId, role: 'teacher' });
                if (!teacher) {
                    return res.status(400).json({ error: 'Invalid teacher ID' });
                }
            }
            
            // Prepare update data
            const updateData = {
                ...(title && { title }),
                ...(description !== undefined && { description }),
                ...(grade && { grade }),
                ...(subject && { subject }),
                ...(teacherId && { teacherId }),
                updatedAt: new Date().toISOString()
            };
            
            // Update course
            const updatedCourse = await updateRecord('courses', { id: courseId }, updateData);
            
            res.json({
                message: 'Course updated successfully',
                course: updatedCourse
            });
        } catch (error) {
            console.error('Error updating course:', error);
            res.status(500).json({ error: 'Failed to update course' });
        }
    },

    async deleteCourse(req, res) {
        try {
            const courseId = req.params.id;
            
            // Check if course exists
            const course = await findOneRecord('courses', { id: courseId });
            if (!course) {
                return res.status(404).json({ error: 'Course not found' });
            }
            
            // Delete course
            await deleteRecord('courses', { id: courseId });
            
            res.json({ message: 'Course deleted successfully' });
        } catch (error) {
            console.error('Error deleting course:', error);
            res.status(500).json({ error: 'Failed to delete course' });
        }
    },

    // System Settings
    async getSettings(req, res) {
        try {
            const settings = await findOneRecord('settings', { id: 'system_settings' }) || {};
            res.json(settings);
        } catch (error) {
            console.error('Error fetching settings:', error);
            res.status(500).json({ error: 'Failed to fetch settings' });
        }
    },

    async updateSettings(req, res) {
        try {
            const { siteName, contactEmail, maintenanceMode, allowRegistration, academicYear } = req.body;
            
            // Prepare update data
            const updateData = {
                ...(siteName && { siteName }),
                ...(contactEmail && { contactEmail }),
                ...(maintenanceMode !== undefined && { maintenanceMode }),
                ...(allowRegistration !== undefined && { allowRegistration }),
                ...(academicYear && { academicYear }),
                updatedAt: new Date().toISOString()
            };
            
            // Update or create settings
            const settings = await findOneRecord('settings', { id: 'system_settings' });
            let updatedSettings;
            
            if (settings) {
                updatedSettings = await updateRecord('settings', { id: 'system_settings' }, updateData);
            } else {
                updatedSettings = await createRecord('settings', {
                    id: 'system_settings',
                    ...updateData,
                    createdAt: new Date().toISOString()
                });
            }
            
            res.json({
                message: 'Settings updated successfully',
                settings: updatedSettings
            });
        } catch (error) {
            console.error('Error updating settings:', error);
            res.status(500).json({ error: 'Failed to update settings' });
        }
    },

    // Reports
    async getUsersReport(req, res) {
        try {
            const users = await findRecords('users');
            
            // Don't return password hashes
            const safeUsers = users.map(user => {
                const { password, ...safeUser } = user;
                return safeUser;
            });
            
            // Group users by role
            const usersByRole = safeUsers.reduce((acc, user) => {
                acc[user.role] = acc[user.role] || [];
                acc[user.role].push(user);
                return acc;
            }, {});
            
            // Calculate statistics
            const stats = {
                total: users.length,
                byRole: {
                    admin: (usersByRole.admin || []).length,
                    teacher: (usersByRole.teacher || []).length,
                    student: (usersByRole.student || []).length
                }
            };
            
            // If requested with detail=true, include the user data
            const includeDetails = req.query.detail === 'true';
            
            res.json({
                stats,
                ...(includeDetails && { users: safeUsers })
            });
        } catch (error) {
            console.error('Error generating users report:', error);
            res.status(500).json({ error: 'Failed to generate users report' });
        }
    },

    async getCoursesReport(req, res) {
        try {
            const courses = await findRecords('courses');
            
            // Group courses by grade
            const coursesByGrade = courses.reduce((acc, course) => {
                acc[course.grade] = acc[course.grade] || [];
                acc[course.grade].push(course);
                return acc;
            }, {});
            
            // Group courses by subject
            const coursesBySubject = courses.reduce((acc, course) => {
                acc[course.subject] = acc[course.subject] || [];
                acc[course.subject].push(course);
                return acc;
            }, {});
            
            // Calculate statistics
            const stats = {
                total: courses.length,
                byGrade: Object.keys(coursesByGrade).reduce((acc, grade) => {
                    acc[grade] = coursesByGrade[grade].length;
                    return acc;
                }, {}),
                bySubject: Object.keys(coursesBySubject).reduce((acc, subject) => {
                    acc[subject] = coursesBySubject[subject].length;
                    return acc;
                }, {})
            };
            
            // If requested with detail=true, include the course data
            const includeDetails = req.query.detail === 'true';
            
            res.json({
                stats,
                ...(includeDetails && { courses })
            });
        } catch (error) {
            console.error('Error generating courses report:', error);
            res.status(500).json({ error: 'Failed to generate courses report' });
        }
    },

    async getActivitiesReport(req, res) {
        try {
            const { startDate, endDate } = req.query;
            let activities = await findRecords('activities');
            
            // Filter by date range if provided
            if (startDate || endDate) {
                activities = activities.filter(activity => {
                    const activityDate = new Date(activity.timestamp);
                    if (startDate && new Date(startDate) > activityDate) return false;
                    if (endDate && new Date(endDate) < activityDate) return false;
                    return true;
                });
            }
            
            // Group activities by type
            const activitiesByType = activities.reduce((acc, activity) => {
                acc[activity.type] = acc[activity.type] || [];
                acc[activity.type].push(activity);
                return acc;
            }, {});
            
            // Group activities by user role
            const activitiesByRole = activities.reduce((acc, activity) => {
                acc[activity.userRole] = acc[activity.userRole] || [];
                acc[activity.userRole].push(activity);
                return acc;
            }, {});
            
            // Calculate statistics
            const stats = {
                total: activities.length,
                byType: Object.keys(activitiesByType).reduce((acc, type) => {
                    acc[type] = activitiesByType[type].length;
                    return acc;
                }, {}),
                byRole: Object.keys(activitiesByRole).reduce((acc, role) => {
                    acc[role] = activitiesByRole[role].length;
                    return acc;
                }, {})
            };
            
            // If requested with detail=true, include the activity data
            const includeDetails = req.query.detail === 'true';
            
            res.json({
                stats,
                ...(includeDetails && { activities })
            });
        } catch (error) {
            console.error('Error generating activities report:', error);
            res.status(500).json({ error: 'Failed to generate activities report' });
        }
    }
};

module.exports = adminController; 