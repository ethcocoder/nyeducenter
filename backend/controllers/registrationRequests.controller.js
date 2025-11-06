const pool = require('../config/db.config');
const { sendEmail } = require('../services/emailService');

// Create new registration request
exports.create = async (req, res) => {
  const { firstName, lastName, email, role } = req.body;
  try {
    console.log('Creating registration request:', { firstName, lastName, email, role });

    // First create a user with is_active = 0
    const [userResult] = await db.query(
      'INSERT INTO users (first_name, last_name, email, role_id, is_active) VALUES (?, ?, ?, ?, 0)',
      [firstName, lastName, email, role === 'teacher' ? 2 : 3] // 2 for teacher, 3 for student
    );

    console.log('Created user:', userResult);

    // Then create the registration request
    const [requestResult] = await db.query(
      'INSERT INTO registration_requests (user_id, status) VALUES (?, ?)',
      [userResult.insertId, 'pending']
    );

    console.log('Created registration request:', requestResult);

    res.status(201).json({ 
      message: 'Registration request submitted successfully',
      requestId: requestResult.insertId
    });
  } catch (err) {
    console.error('Error creating registration request:', err);
    if (err.code === 'ER_DUP_ENTRY') {
      return res.status(400).json({ error: 'Email already exists' });
    }
    res.status(500).json({ error: 'Failed to submit registration request' });
  }
};

// Get all registration requests
exports.getAll = async (req, res) => {
  try {
    console.log('Fetching all registration requests...');

    // Get the requests
    const [requests] = await db.query(`
      SELECT 
        rr.id,
        rr.user_id,
        rr.status,
        rr.registration_code,
        rr.created_at,
        rr.updated_at,
        u.first_name,
        u.last_name,
        u.email,
        u.role_id
      FROM registration_requests rr
      LEFT JOIN users u ON rr.user_id = u.id
      WHERE rr.status = 'pending'
      ORDER BY rr.created_at DESC
    `);

    console.log('Found requests:', requests.length);

    // Format the response
    const formattedRequests = requests.map(req => ({
      id: req.id,
      userId: req.user_id,
      firstName: req.first_name,
      lastName: req.last_name,
      email: req.email,
      role: req.role_id === 2 ? 'teacher' : 'student',
      status: req.status,
      createdAt: req.created_at,
      updatedAt: req.updated_at
    }));

    res.json(formattedRequests);
  } catch (err) {
    console.error('Error fetching registration requests:', err);
    console.error('Error details:', {
      message: err.message,
      code: err.code,
      sqlMessage: err.sqlMessage
    });
    res.status(500).json({ 
      error: 'Failed to fetch registration requests',
      details: err.message
    });
  }
};

// Approve registration request
exports.approve = async (req, res) => {
  const { id } = req.params;
  try {
    // Get request details with user info
    const [requests] = await db.query(`
      SELECT 
        rr.*,
        u.first_name,
        u.last_name,
        u.email
      FROM registration_requests rr
      LEFT JOIN users u ON rr.user_id = u.id
      WHERE rr.id = ?
    `, [id]);

    if (requests.length === 0) {
      return res.status(404).json({ error: 'Registration request not found' });
    }

    const request = requests[0];
    
    // Generate registration code
    const registrationCode = Math.random().toString(36).substring(2, 8).toUpperCase();
    
    // Update request status and add code
    await db.query(
      'UPDATE registration_requests SET status = ?, registration_code = ? WHERE id = ?',
      ['approved', registrationCode, id]
    );

    // Activate the user
    await db.query(
      'UPDATE users SET is_active = 1 WHERE id = ?',
      [request.user_id]
    );

    // Send approval email
    await sendEmail(request.email, 'registrationApproved', {
      firstName: request.first_name,
      lastName: request.last_name,
      code: registrationCode
    });

    res.json({ 
      message: 'Registration request approved',
      code: registrationCode
    });
  } catch (err) {
    console.error('Error approving registration request:', err);
    res.status(500).json({ error: 'Failed to approve registration request' });
  }
};

// Reject registration request
exports.reject = async (req, res) => {
  const { id } = req.params;
  try {
    // Get request details with user info
    const [requests] = await db.query(`
      SELECT 
        rr.*,
        u.first_name,
        u.last_name,
        u.email
      FROM registration_requests rr
      LEFT JOIN users u ON rr.user_id = u.id
      WHERE rr.id = ?
    `, [id]);

    if (requests.length === 0) {
      return res.status(404).json({ error: 'Registration request not found' });
    }

    const request = requests[0];
    
    // Update request status
    await db.query(
      'UPDATE registration_requests SET status = ? WHERE id = ?',
      ['rejected', id]
    );

    // Delete the inactive user
    await db.query(
      'DELETE FROM users WHERE id = ? AND is_active = 0',
      [request.user_id]
    );

    // Send rejection email
    await sendEmail(request.email, 'registrationRejected', {
      firstName: request.first_name,
      lastName: request.last_name
    });

    res.json({ message: 'Registration request rejected' });
  } catch (err) {
    console.error('Error rejecting registration request:', err);
    res.status(500).json({ error: 'Failed to reject registration request' });
  }
}; 