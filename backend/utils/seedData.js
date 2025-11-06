const bcrypt = require('bcryptjs');
const JsonDB = require('./jsonDb');

// Initialize collections
const userDb = new JsonDB('users');
const courseDb = new JsonDB('courses');
const assignmentDb = new JsonDB('assignments');
const quizDb = new JsonDB('quizzes');
const announcementDb = new JsonDB('announcements');

// Seed Users
async function seedUsers() {
  try {
    // Only seed if users collection is empty
    const users = userDb.findAll();
    if (users.length > 0) {
      console.log('Users collection already has data, skipping seed...');
      return;
    }

    // Create password hash
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash('password123', salt);

    // Admin user
    userDb.create({
      firstName: 'Admin',
      lastName: 'User',
      email: 'admin@edun.edu',
      password: hashedPassword,
      role: 'admin',
      profilePicture: 'https://i.pravatar.cc/150?img=1',
      preferredLanguage: 'en'
    });

    // Teacher user
    userDb.create({
      firstName: 'Teacher',
      lastName: 'User',
      email: 'teacher@edun.edu',
      password: hashedPassword,
      role: 'teacher',
      profilePicture: 'https://i.pravatar.cc/150?img=2',
      preferredLanguage: 'en'
    });

    // Student user
    userDb.create({
      firstName: 'Student',
      lastName: 'User',
      email: 'student@edun.edu',
      password: hashedPassword,
      role: 'student',
      grade: 10,
      profilePicture: 'https://i.pravatar.cc/150?img=3',
      preferredLanguage: 'en'
    });

    // Parent user
    userDb.create({
      firstName: 'Parent',
      lastName: 'User',
      email: 'parent@edun.edu',
      password: hashedPassword,
      role: 'parent',
      profilePicture: 'https://i.pravatar.cc/150?img=4',
      preferredLanguage: 'en'
    });

    console.log('Users seeded successfully!');
  } catch (error) {
    console.error('Error seeding users:', error);
  }
}

// Seed Courses
function seedCourses() {
  try {
    // Only seed if courses collection is empty
    const courses = courseDb.findAll();
    if (courses.length > 0) {
      console.log('Courses collection already has data, skipping seed...');
      return;
    }

    const teacherUser = userDb.findOne(user => user.role === 'teacher');
    const teacherId = teacherUser ? teacherUser.id : null;

    // Create courses
    courseDb.create({
      title: 'Introduction to Mathematics',
      description: 'Basic math concepts for high school students.',
      grade: 9,
      teacher: teacherId,
      imageUrl: 'https://source.unsplash.com/random/800x600/?math',
      enrolledStudents: []
    });

    courseDb.create({
      title: 'Physics Fundamentals',
      description: 'An introduction to physics concepts.',
      grade: 10,
      teacher: teacherId,
      imageUrl: 'https://source.unsplash.com/random/800x600/?physics',
      enrolledStudents: []
    });

    courseDb.create({
      title: 'Biology Basics',
      description: 'Understanding living organisms and systems.',
      grade: 10,
      teacher: teacherId,
      imageUrl: 'https://source.unsplash.com/random/800x600/?biology',
      enrolledStudents: []
    });

    console.log('Courses seeded successfully!');
  } catch (error) {
    console.error('Error seeding courses:', error);
  }
}

// Seed Assignments
function seedAssignments() {
  try {
    // Only seed if assignments collection is empty
    const assignments = assignmentDb.findAll();
    if (assignments.length > 0) {
      console.log('Assignments collection already has data, skipping seed...');
      return;
    }

    const courses = courseDb.findAll();
    const courseIds = courses.map(course => course.id);

    // Create assignments
    if (courseIds.length > 0) {
      assignmentDb.create({
        title: 'Algebra Homework 1',
        description: 'Complete problems 1-10 from Chapter 2.',
        courseId: courseIds[0],
        dueDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(), // 7 days from now
        points: 100
      });

      assignmentDb.create({
        title: 'Physics Lab Report',
        description: 'Write a report on the pendulum experiment.',
        courseId: courseIds[1],
        dueDate: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString(), // 14 days from now
        points: 150
      });
    }

    console.log('Assignments seeded successfully!');
  } catch (error) {
    console.error('Error seeding assignments:', error);
  }
}

// Seed Quizzes
function seedQuizzes() {
  try {
    // Only seed if quizzes collection is empty
    const quizzes = quizDb.findAll();
    if (quizzes.length > 0) {
      console.log('Quizzes collection already has data, skipping seed...');
      return;
    }

    const courses = courseDb.findAll();
    const courseIds = courses.map(course => course.id);

    // Create quizzes
    if (courseIds.length > 0) {
      quizDb.create({
        title: 'Math Quiz 1',
        description: 'Test your algebra knowledge',
        courseId: courseIds[0],
        timeLimit: 30, // 30 minutes
        dueDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
        questions: [
          {
            question: 'What is the value of x in the equation 2x + 5 = 15?',
            options: ['5', '7.5', '10', '5.5'],
            correctAnswer: '5'
          },
          {
            question: 'Simplify: 3(x + 2) - 2(x - 1)',
            options: ['x + 8', 'x + 4', '5x + 8', 'x + 7'],
            correctAnswer: 'x + 8'
          }
        ]
      });
    }

    console.log('Quizzes seeded successfully!');
  } catch (error) {
    console.error('Error seeding quizzes:', error);
  }
}

// Seed Announcements
function seedAnnouncements() {
  try {
    // Only seed if announcements collection is empty
    const announcements = announcementDb.findAll();
    if (announcements.length > 0) {
      console.log('Announcements collection already has data, skipping seed...');
      return;
    }

    const teacherUser = userDb.findOne(user => user.role === 'teacher');
    const teacherId = teacherUser ? teacherUser.id : null;

    // Create announcements
    if (teacherId) {
      announcementDb.create({
        title: 'Welcome to the New Semester!',
        content: 'Welcome to the new academic year. We are excited to begin our learning journey together.',
        author: teacherId,
        priority: 'high'
      });

      announcementDb.create({
        title: 'School Holiday Announcement',
        content: 'Please note that the school will be closed next Monday for the national holiday.',
        author: teacherId,
        priority: 'medium'
      });
    }

    console.log('Announcements seeded successfully!');
  } catch (error) {
    console.error('Error seeding announcements:', error);
  }
}

// Main seed function
async function seedAll() {
  console.log('Starting data seeding...');
  
  await seedUsers();
  seedCourses();
  seedAssignments();
  seedQuizzes();
  seedAnnouncements();
  
  console.log('All data seeded successfully!');
}

// If this script is run directly
if (require.main === module) {
  seedAll();
}

module.exports = { seedAll }; 