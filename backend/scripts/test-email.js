require('dotenv').config();
const { sendEmail } = require('../services/emailService');

async function testEmail() {
  try {
    console.log('Testing email configuration...');
    
    await sendEmail(
      'natnaelermiyas1@gmail.com', // Send to your email for testing
      'registrationApproved',
      {
        name: 'Test User',
        code: 'TEST123'
      }
    );
    
    console.log('Test email sent successfully!');
    process.exit(0);
  } catch (error) {
    console.error('Error sending test email:', error);
    process.exit(1);
  }
}

testEmail(); 