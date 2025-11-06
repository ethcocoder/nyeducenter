const nodemailer = require('nodemailer');

// Create a transporter using SMTP
const transporter = nodemailer.createTransport({
  service: 'gmail',
  auth: {
    user: 'natnaelermiyas1@gmail.com',
    pass: 'xawh mztc xref cgwo'
  }
});

// Email templates
const emailTemplates = {
  registrationApproved: (name, code) => ({
    subject: 'Registration Request Approved - EduN',
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
        <div style="text-align: center; margin-bottom: 20px;">
          <h1 style="color: #4CAF50; margin: 0;">EduN System</h1>
          <p style="color: #666;">Your Learning Management Platform</p>
        </div>
        
        <h2 style="color: #2196F3; border-bottom: 2px solid #2196F3; padding-bottom: 10px;">Registration Request Approved</h2>
        
        <p>Dear ${name},</p>
        
        <p>Great news! Your registration request has been approved. You can now complete your registration using the code below:</p>
        
        <div style="background-color: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;">
          <h3 style="color: #2196F3; margin: 0; font-size: 24px;">Registration Code</h3>
          <p style="font-size: 32px; font-weight: bold; color: #4CAF50; margin: 10px 0;">${code}</p>
        </div>
        
        <p><strong>Next Steps:</strong></p>
        <ol>
          <li>Go to the EduN registration page</li>
          <li>Enter your registration code</li>
          <li>Complete your profile setup</li>
          <li>Start using the platform!</li>
        </ol>
        
        <p>If you have any questions or need assistance, please contact our support team.</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
          <p style="margin: 0;">Best regards,</p>
          <p style="margin: 5px 0 0 0; font-weight: bold;">The EduN Team</p>
        </div>
      </div>
    `
  }),

  registrationRejected: (name) => ({
    subject: 'Registration Request Status - EduN',
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
        <div style="text-align: center; margin-bottom: 20px;">
          <h1 style="color: #f44336; margin: 0;">EduN System</h1>
          <p style="color: #666;">Your Learning Management Platform</p>
        </div>
        
        <h2 style="color: #f44336; border-bottom: 2px solid #f44336; padding-bottom: 10px;">Registration Request Status</h2>
        
        <p>Dear ${name},</p>
        
        <p>We regret to inform you that your registration request has not been approved at this time.</p>
        
        <div style="background-color: #fff3f3; padding: 20px; border-radius: 5px; margin: 20px 0;">
          <p><strong>Possible reasons for rejection:</strong></p>
          <ul>
            <li>Incomplete or incorrect information provided</li>
            <li>Email domain not authorized</li>
            <li>System capacity limitations</li>
          </ul>
        </div>
        
        <p>If you believe this is an error or would like to provide additional information, please contact our support team.</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
          <p style="margin: 0;">Best regards,</p>
          <p style="margin: 5px 0 0 0; font-weight: bold;">The EduN Team</p>
        </div>
      </div>
    `
  })
};

// Send email function
const sendEmail = async (to, template, data) => {
  try {
    const { subject, html } = emailTemplates[template](data.name, data.code);
    
    const mailOptions = {
      from: 'EduN System <natnaelermiyas1@gmail.com>',
      to,
      subject,
      html
    };

    const info = await transporter.sendMail(mailOptions);
    console.log('Email sent:', info.messageId);
    return true;
  } catch (error) {
    console.error('Error sending email:', error);
    throw error;
  }
};

module.exports = {
  sendEmail
};