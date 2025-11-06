const pool = require('../config/db.config');
exports.getDashboard = async (req, res) => {
  try {
    const userId = req.user.id;

    // GPA, class rank, credits earned
    const [[gpaRow]] = await db.query('SELECT gpa, class_rank, credits_earned FROM students WHERE user_id = ?', [userId]);

    // Academic progress (per subject)
    const [progressRows] = await db.query(`
      SELECT subject, progress, label
      FROM academic_progress
      WHERE user_id = ?
    `, [userId]);

    // Recent assignments
    const [assignments] = await db.query(`
      SELECT a.id, a.title, a.subject, a.due_date, a.status
      FROM assignments a
      WHERE a.user_id = ?
      ORDER BY a.due_date DESC
      LIMIT 5
    `, [userId]);

    // Upcoming deadlines
    const [deadlines] = await db.query(`
      SELECT title, subject, due_date
      FROM assignments
      WHERE user_id = ? AND due_date >= NOW()
      ORDER BY due_date ASC
      LIMIT 3
    `, [userId]);

    // Achievements & awards
    const [awards] = await db.query(`
      SELECT name
      FROM achievements
      WHERE user_id = ?
      ORDER BY achieved_at DESC
      LIMIT 5
    `, [userId]);

    res.json({
      gpa: gpaRow?.gpa || 0,
      classRank: gpaRow?.class_rank || null,
      creditsEarned: gpaRow?.credits_earned || 0,
      academicProgress: progressRows,
      assignments,
      deadlines,
      awards: awards.map(a => a.name)
    });
  } catch (err) {
    console.error('Error fetching student dashboard:', err);
    res.status(500).json({ error: 'Failed to fetch student dashboard' });
  }
}; 