const { pool } = require('../config/db.config');

const admins = [
  {
    email: 'admin@example.com',
    password: '$2a$10$H.B9Aok5z2r3OFFfGZVCwu4wDJZq6W3xFHbZnAYnfz5UXZrF3xQfS', // 'admin123'
    firstName: 'Admin',
    lastName: 'User',
  },
  {
    email: 'natikiyu7@gmail.com',
    password: '$2b$10$at0F4ug5oNTUbsN5XUwM6eToMEA1eAF2DiI2lqp6j0iDG0VaPCPR.', // 'changedpassis1221'
    firstName: 'Natnael',
    lastName: 'Kiyu',
  }
];

async function seedAdmins() {
  try {
    // Check if any admins exist
    const [existingAdmins] = await pool.query('SELECT COUNT(*) as count FROM admin');
    if (existingAdmins[0].count > 0) {
      console.log('Admin users already exist. Skipping seeding.');
      process.exit(0);
      return;
    }

    // Start a transaction
    await pool.query('START TRANSACTION');

    for (const admin of admins) {
      // Check if user exists
      const [userRows] = await pool.query('SELECT id FROM users WHERE email = ?', [admin.email]);
      let userId;

      if (userRows.length === 0) {
        // Insert new user
        const [result] = await pool.query(
          'INSERT INTO users (role_id, email, password, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?, 1)',
          [1, admin.email, admin.password, admin.firstName, admin.lastName]
        );
        userId = result.insertId;
        console.log(`Inserted user: ${admin.email}`);
      } else {
        userId = userRows[0].id;
        // Update existing user
        await pool.query(
          'UPDATE users SET role_id = ?, password = ?, first_name = ?, last_name = ?, is_active = 1 WHERE id = ?',
          [1, admin.password, admin.firstName, admin.lastName, userId]
        );
        console.log(`Updated user: ${admin.email}`);
      }

      // Check and update admin entry
      const [adminRows] = await pool.query('SELECT id FROM admin WHERE user_id = ?', [userId]);
      if (adminRows.length === 0) {
        await pool.query('INSERT INTO admin (user_id, admin_level) VALUES (?, ?)', [userId, 'super']);
        console.log(`Inserted admin: ${admin.email}`);
      } else {
        await pool.query('UPDATE admin SET admin_level = ? WHERE user_id = ?', ['super', userId]);
        console.log(`Updated admin: ${admin.email}`);
      }

      // Check and update auth entry
      const [authRows] = await pool.query('SELECT id FROM auth WHERE user_id = ?', [userId]);
      if (authRows.length === 0) {
        await pool.query('INSERT INTO auth (user_id, email, password) VALUES (?, ?, ?)', 
          [userId, admin.email, admin.password]);
        console.log(`Inserted auth for: ${admin.email}`);
      } else {
        await pool.query('UPDATE auth SET email = ?, password = ? WHERE user_id = ?', 
          [admin.email, admin.password, userId]);
        console.log(`Updated auth for: ${admin.email}`);
      }
    }

    // Commit the transaction
    await pool.query('COMMIT');
    console.log('Admin users seeded successfully.');
    process.exit(0);
  } catch (err) {
    // Rollback in case of error
    await pool.query('ROLLBACK');
    console.error('Error seeding admin users:', err);
    process.exit(1);
  }
}

seedAdmins(); 