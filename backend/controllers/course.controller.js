const { pool } = require('../config/db.config');

// Create a new course (teacher only)
exports.createCourse = async (req, res) => {
  try {
    const {
      subject_id, grade_level_id, term_id, name, code, description, start_date, end_date, max_students
    } = req.body;
    const teacher_id = req.user.id;

    const [result] = await pool.query(
      `INSERT INTO courses
        (subject_id, grade_level_id, teacher_id, term_id, name, code, description, start_date, end_date, max_students)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [subject_id, grade_level_id, teacher_id, term_id, name, code, description, start_date, end_date, max_students]
    );

    res.status(201).json({ message: 'Course created', courseId: result.insertId });
  } catch (err) {
    console.error('Error creating course:', err);
    res.status(500).json({ error: 'Failed to create course' });
  }
};

// List courses for a teacher
exports.getCoursesByTeacher = async (req, res) => {
  try {
    const teacherId = req.user.id;
    const [courses] = await pool.query(
      `SELECT * FROM courses WHERE teacher_id = ? ORDER BY created_at DESC`,
      [teacherId]
    );
    res.json(courses);
  } catch (err) {
    console.error('Error fetching courses:', err);
    res.status(500).json({ error: 'Failed to fetch courses' });
  }
}; 