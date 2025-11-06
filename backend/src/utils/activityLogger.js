const { createRecord } = require('./database');

/**
 * Activity types
 */
const ActivityTypes = {
    // Auth activities
    USER_LOGIN: 'user_login',
    USER_LOGOUT: 'user_logout',
    FAILED_LOGIN: 'failed_login',
    PASSWORD_RESET: 'password_reset',
    
    // User management activities
    USER_CREATED: 'user_created',
    USER_UPDATED: 'user_updated',
    USER_DELETED: 'user_deleted',
    
    // Course management activities
    COURSE_CREATED: 'course_created',
    COURSE_UPDATED: 'course_updated',
    COURSE_DELETED: 'course_deleted',
    
    // Content activities
    CONTENT_CREATED: 'content_created',
    CONTENT_UPDATED: 'content_updated',
    CONTENT_DELETED: 'content_deleted',
    
    // System activities
    SETTINGS_UPDATED: 'settings_updated',
    BACKUP_CREATED: 'backup_created',
    SYSTEM_MAINTENANCE: 'system_maintenance'
};

/**
 * Log an activity in the system
 * 
 * @param {Object} params - Activity parameters
 * @param {string} params.type - Activity type
 * @param {string} params.userId - User ID who performed the activity
 * @param {string} params.userRole - Role of the user who performed the activity
 * @param {Object} params.metadata - Additional metadata about the activity
 * @param {string} params.ip - IP address of the user
 * @returns {Promise<Object>} The created activity record
 */
const logActivity = async ({ type, userId, userRole, metadata = {}, ip = null }) => {
    if (!type || !userId) {
        console.warn('Activity log skipped: type or userId missing');
        return null; // Don't throw error, just return null
    }

    const activity = {
        type,
        userId,
        userRole,
        metadata,
        ip,
        timestamp: new Date().toISOString()
    };

    try {
        // Check if tables exist first
        try {
            return await createRecord('activities', activity);
        } catch (error) {
            // If table doesn't exist, just log to console instead
            console.log(`Activity log (${type}): User ${userId} (${userRole})`);
            return null;
        }
    } catch (error) {
        console.error('Failed to log activity:', error);
        // Don't throw the error to prevent breaking the main flow
        return null;
    }
};

/**
 * Express middleware to log activities
 * 
 * @param {string} type - Activity type
 * @param {Function} metadataExtractor - Function to extract metadata from request
 * @returns {Function} Express middleware
 */
const activityLoggerMiddleware = (type, metadataExtractor = null) => {
    return async (req, res, next) => {
        try {
            // Save the original end method
            const originalEnd = res.end;
            
            // Override the end method
            res.end = async function(...args) {
                // Get metadata if extractor is provided
                const metadata = metadataExtractor ? await metadataExtractor(req, res) : {};
                
                // Log the activity
                if (req.user) {
                    await logActivity({
                        type,
                        userId: req.user.id,
                        userRole: req.user.role,
                        metadata: {
                            ...metadata,
                            statusCode: res.statusCode,
                            method: req.method,
                            path: req.path
                        },
                        ip: req.ip || req.headers['x-forwarded-for'] || null
                    });
                }
                
                // Call the original end method
                originalEnd.apply(res, args);
            };
            
            next();
        } catch (error) {
            // If logging fails, continue with the request
            console.error('Activity logging middleware error:', error);
            next();
        }
    };
};

module.exports = {
    ActivityTypes,
    logActivity,
    activityLoggerMiddleware
}; 