/**
 * Seed script to populate the database with initial data
 * Run with: node seed.js
 */

const { seedAll } = require('./utils/seedData');

// Run the seed function
(async () => {
  try {
    console.log('Starting manual data seed...');
    await seedAll();
    console.log('Manual seed completed successfully!');
  } catch (error) {
    console.error('Error during manual seed:', error);
  }
})(); 