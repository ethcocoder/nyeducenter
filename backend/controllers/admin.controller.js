const { pool } = require('../config/db.config');

// Get all users
exports.getAllUsers = async (req, res) => {
  try {
    const [users] = await pool.query(`
      SELECT 
        u.id,
        u.first_name as firstName,
        u.last_name as lastName,
        u.email,
        r.name as role,
        u.is_active as isActive,
        u.created_at as createdAt
      FROM users u
      JOIN roles r ON u.role_id = r.id
      ORDER BY u.created_at DESC
    `);

    res.json(users);
  } catch (err) {
    console.error('Error fetching users:', err);
    res.status(500).json({ error: 'Failed to fetch users' });
  }
};

// Update user
exports.updateUser = async (req, res) => {
  const { id } = req.params;
  const { firstName, lastName, email, role } = req.body;

  try {
    // Get role ID
    const [roles] = await pool.query('SELECT id FROM roles WHERE name = ?', [role]);
    if (roles.length === 0) {
      return res.status(400).json({ error: 'Invalid role' });
    }
    const roleId = roles[0].id;

    // Update user
    await pool.query(
      `UPDATE users 
       SET first_name = ?, last_name = ?, email = ?, role_id = ?
       WHERE id = ?`,
      [firstName, lastName, email, roleId, id]
    );

    res.json({ message: 'User updated successfully' });
  } catch (err) {
    console.error('Error updating user:', err);
    if (err.code === 'ER_DUP_ENTRY') {
      return res.status(400).json({ error: 'Email already exists' });
    }
    res.status(500).json({ error: 'Failed to update user' });
  }
};

// Delete user
exports.deleteUser = async (req, res) => {
  const { id } = req.params;

  try {
    // Check if user exists
    const [users] = await pool.query('SELECT * FROM users WHERE id = ?', [id]);
    if (users.length === 0) {
      return res.status(404).json({ error: 'User not found' });
    }

    // Delete user
    await pool.query('DELETE FROM users WHERE id = ?', [id]);

    res.json({ message: 'User deleted successfully' });
  } catch (err) {
    console.error('Error deleting user:', err);
    res.status(500).json({ error: 'Failed to delete user' });
  }
};

// Dashboard stats
exports.getDashboardStats = async (req, res) => {
  try {
    // Total students
    const [[{ totalStudents }]] = await pool.query('SELECT COUNT(*) as totalStudents FROM users WHERE role_id=3');
    // Total teachers
    const [[{ totalTeachers }]] = await pool.query('SELECT COUNT(*) as totalTeachers FROM users WHERE role_id=2');
    // Total courses
    const [[{ totalCourses }]] = await pool.query('SELECT COUNT(*) as totalCourses FROM courses');
    // Total active users
    const [[{ activeUsers }]] = await pool.query('SELECT COUNT(*) as activeUsers FROM users WHERE is_active=1');
    // Completion rate (example: percent of students who completed at least 1 course)
    const [[{ completed }]] = await pool.query('SELECT COUNT(DISTINCT user_id) as completed FROM course_completions');
    const completionRate = totalStudents > 0 ? Math.round((completed / totalStudents) * 100) : 0;

    res.json({
      totalStudents,
      totalTeachers,
      totalCourses,
      activeUsers,
      completionRate
    });
  } catch (err) {
    console.error('Error fetching dashboard stats:', err);
    res.status(500).json({ error: 'Failed to fetch dashboard stats' });
  }
}; 