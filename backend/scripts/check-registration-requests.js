const { pool } = require('../config/db.config');

async function checkRegistrationRequests() {
    try {
        // Check if tables exist
        console.log('\nChecking database tables...');
        const [tables] = await pool.query('SHOW TABLES');
        console.log('Available tables:', tables.map(t => Object.values(t)[0]));

        // Check registration_requests table structure
        console.log('\nChecking registration_requests table structure...');
        const [columns] = await pool.query('DESCRIBE registration_requests');
        console.log('Table columns:', columns.map(c => c.Field));

        // Check registration_requests table
        console.log('\nChecking registration requests...');
        const [requests] = await pool.query(`
            SELECT 
                rr.*,
                u.email,
                u.first_name,
                u.last_name
            FROM registration_requests rr
            LEFT JOIN users u ON rr.user_id = u.id
            ORDER BY rr.created_at DESC
        `);

        console.log('\nRegistration Requests:');
        console.log('=====================');
        if (requests.length === 0) {
            console.log('No registration requests found.');
        } else {
            requests.forEach((req, index) => {
                console.log(`\nRequest #${index + 1}:`);
                console.log(`ID: ${req.id}`);
                console.log(`User ID: ${req.user_id}`);
                console.log(`User: ${req.first_name || 'N/A'} ${req.last_name || 'N/A'} (${req.email || 'N/A'})`);
                console.log(`Status: ${req.status}`);
                console.log(`Created: ${new Date(req.created_at).toLocaleString()}`);
                console.log(`Updated: ${new Date(req.updated_at).toLocaleString()}`);
                if (req.registration_code) {
                    console.log(`Registration Code: ${req.registration_code}`);
                }
            });
        }

        // Check users table for pending registrations
        console.log('\nChecking pending users...');
        const [pendingUsers] = await pool.query(`
            SELECT 
                u.*,
                rr.status as request_status
            FROM users u
            LEFT JOIN registration_requests rr ON u.id = rr.user_id
            WHERE u.is_active = 0
            ORDER BY u.created_at DESC
        `);

        console.log('\nPending Users:');
        console.log('=============');
        if (pendingUsers.length === 0) {
            console.log('No pending users found.');
        } else {
            pendingUsers.forEach((user, index) => {
                console.log(`\nUser #${index + 1}:`);
                console.log(`ID: ${user.id}`);
                console.log(`Name: ${user.first_name} ${user.last_name}`);
                console.log(`Email: ${user.email}`);
                console.log(`Role ID: ${user.role_id}`);
                console.log(`Active: ${user.is_active}`);
                console.log(`Created: ${new Date(user.created_at).toLocaleString()}`);
                console.log(`Request Status: ${user.request_status || 'No request'}`);
            });
        }

    } catch (error) {
        console.error('\nError checking registration requests:');
        console.error('Error message:', error.message);
        console.error('Error code:', error.code);
        console.error('Error stack:', error.stack);
    } finally {
        process.exit(0);
    }
}

checkRegistrationRequests(); 