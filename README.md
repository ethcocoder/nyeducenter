# NY Education Center Application

A comprehensive education management platform for teachers and students, providing course management, quiz creation, assignment submission, and student progress tracking.

## Features

- **User Authentication**: Secure login and registration system with role-based access
- **Course Management**: Create, edit, and manage courses with materials and schedules
- **Quiz System**: Create quizzes with various question types, automatic grading
- **Assignment Management**: Create, distribute, and collect assignments
- **Student Dashboard**: View courses, upcoming assignments, and grades
- **Teacher Dashboard**: Manage courses, monitor student progress, and grade assignments

## Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: Node.js, Express.js
- **Database**: MongoDB with Mongoose ODM
- **Authentication**: JWT (JSON Web Tokens)
- **File Storage**: Local file system with configurable cloud storage options

## Project Structure

```
ny-edu-center/
│
├── backend/                  # Backend Node.js application
│   ├── src/
│   │   ├── controllers/      # Request handlers
│   │   ├── middlewares/      # Express middlewares
│   │   ├── models/           # Database models
│   │   ├── routes/           # API routes
│   │   ├── services/         # Business logic
│   │   ├── utils/            # Utility functions
│   │   ├── app.js            # Express application setup
│   │   └── server.js         # Server entry point
│   ├── .env.example          # Environment variables example
│   ├── package.json          # Dependencies and scripts
│   └── API_DOCUMENTATION.md  # API documentation
│
└── frontend/                 # Frontend application
    ├── template/             # HTML templates
    │   ├── css/              # CSS styles
    │   ├── js/               # JavaScript files
    │   ├── img/              # Image assets
    │   ├── teachers/         # Teacher-specific pages
    │   └── students/         # Student-specific pages
    └── index.html            # Entry point
```

## Prerequisites

- Node.js (v14 or higher)
- MongoDB (v4.4 or higher)
- npm or yarn

## Local Development Setup

### Backend Setup

1. Navigate to the backend directory:
   ```bash
   cd backend
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Create a `.env` file based on the `.env.example`:
   ```bash
   cp .env.example .env
   ```

4. Modify the `.env` file with your configurations (database URL, JWT secret, etc.)

5. Start the development server:
   ```bash
   npm run dev
   ```

### Frontend Setup

1. No build process is required for the frontend as it uses vanilla HTML, CSS, and JavaScript
2. You can serve the frontend using any static file server:
   ```bash
   npx serve frontend
   ```

## Deployment

### Backend Deployment

#### Option 1: Traditional VPS/Dedicated Server

1. SSH into your server:
   ```bash
   ssh user@your-server-ip
   ```

2. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/ny-edu-center.git
   cd ny-edu-center/backend
   ```

3. Install dependencies:
   ```bash
   npm install --production
   ```

4. Set up environment variables:
   ```bash
   cp .env.example .env
   nano .env  # Edit with your production values
   ```

5. Use PM2 to manage the Node.js process:
   ```bash
   npm install -g pm2
   pm2 start src/server.js --name "ny-edu-api"
   pm2 save
   pm2 startup
   ```

#### Option 2: Containerized Deployment (Docker)

1. Build the Docker image:
   ```bash
   docker build -t ny-edu-backend ./backend
   ```

2. Run the container:
   ```bash
   docker run -d -p 3000:3000 --env-file ./backend/.env --name ny-edu-api ny-edu-backend
   ```

### Frontend Deployment

#### Option 1: Static Hosting (Nginx)

1. Install Nginx:
   ```bash
   sudo apt update
   sudo apt install nginx
   ```

2. Configure Nginx:
   ```bash
   sudo nano /etc/nginx/sites-available/ny-edu
   ```

3. Add the following configuration:
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;

       root /path/to/ny-edu-center/frontend;
       index index.html;

       location / {
           try_files $uri $uri/ /index.html;
       }

       location /api {
           proxy_pass http://localhost:3000;
           proxy_http_version 1.1;
           proxy_set_header Upgrade $http_upgrade;
           proxy_set_header Connection 'upgrade';
           proxy_set_header Host $host;
           proxy_cache_bypass $http_upgrade;
       }
   }
   ```

4. Enable the site:
   ```bash
   sudo ln -s /etc/nginx/sites-available/ny-edu /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

#### Option 2: Cloud Hosting (AWS S3, Netlify, Vercel, etc.)

1. Build or compress your frontend files (optional):
   ```bash
   # Example: Using a simple compression tool
   npm install -g html-minifier
   html-minifier --collapse-whitespace --remove-comments --minify-css true --minify-js true frontend/index.html -o frontend/index.min.html
   ```

2. Upload to your preferred hosting service:
   - For AWS S3: Use the AWS CLI or console to upload files
   - For Netlify/Vercel: Connect your repository and configure the build settings

## Security Considerations

1. **HTTPS**: Always use HTTPS in production
2. **JWT Security**: Use strong secrets and appropriate token expiration
3. **Input Validation**: All user inputs are validated on both client and server
4. **CSRF Protection**: Implement CSRF tokens for state-changing operations
5. **Content Security Policy**: Configure appropriate CSP headers
6. **Rate Limiting**: Implement rate limiting for API endpoints

## Monitoring and Maintenance

1. **Logging**: Configure centralized logging with tools like Winston/Morgan
2. **Error Monitoring**: Use services like Sentry for error tracking
3. **Performance Monitoring**: Use tools like New Relic or PM2 monitoring
4. **Database Backups**: Schedule regular database backups
5. **Updates**: Keep all dependencies updated for security fixes

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin feature/your-feature-name`
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

For support or inquiries, please contact [your-email@example.com] 