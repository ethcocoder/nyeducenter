const pool = require('../config/db.config');

// Create a new assignment (teacher only)
exports.createAssignment = async (req, res) => {
  try {
    const {
      course_id, module_id, title, description, instructions,
      assessment_type_id, due_date, total_points, is_published
    } = req.body;
    const creator_id = req.user.id; // teacher

    const [result] = await db.query(
      `INSERT INTO assignments
        (course_id, module_id, title, description, instructions, assessment_type_id, due_date, total_points, creator_id, is_published)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [course_id, module_id, title, description, instructions, assessment_type_id, due_date, total_points, creator_id, is_published]
    );

    res.status(201).json({ message: 'Assignment created', assignmentId: result.insertId });
  } catch (err) {
    console.error('Error creating assignment:', err);
    res.status(500).json({ error: 'Failed to create assignment' });
  }
};

// List assignments for a teacher
exports.getAssignmentsByTeacher = async (req, res) => {
  try {
    const teacherId = req.user.id;
    const [assignments] = await db.query(
      `SELECT a.*, c.name as course_name
       FROM assignments a
       JOIN courses c ON a.course_id = c.id
       WHERE a.creator_id = ?
       ORDER BY a.due_date DESC`,
      [teacherId]
    );
    res.json(assignments);
  } catch (err) {
    console.error('Error fetching assignments:', err);
    res.status(500).json({ error: 'Failed to fetch assignments' });
  }
}; 