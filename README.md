# Library Management System

A modern, responsive web-based library management system built with PHP, MySQL, and Bootstrap. This system allows users to manage and read PDF books with features like grade categories, personal folders, and recent activity tracking.

## Features

### User Features
- **User Authentication**: Secure login and registration system
- **Grade Categories**: Browse books organized by grade levels
- **Personal Library**: Create custom folders and upload personal PDF books
- **PDF Viewer**: Integrated PDF.js viewer for reading books
- **Recent Activity**: Track recently opened books
- **Responsive Design**: Works on desktop, tablet, and mobile devices

### Admin Features
- **Admin Dashboard**: Comprehensive statistics and management
- **User Management**: View and manage all users
- **Category Management**: Manage grade categories
- **Book Management**: Add, edit, and delete system books
- **Activity Monitoring**: View all user activities
- **System Settings**: Configure application settings

### Technical Features
- **Modern UI**: Clean, professional design with animations
- **Security**: Input validation, CSRF protection, secure file handling
- **File Management**: Secure PDF upload and storage
- **Database**: Optimized queries with proper indexing
- **Responsive**: Mobile-first design approach

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Server (Apache/Nginx)
- PDO PHP Extension
- Write permissions for file uploads

## Installation

1. **Clone or download** the project files to your web server
2. **Create a MySQL database** for the project
3. **Update database configuration** in `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```
4. **Run the setup script** by accessing `setup.php` in your browser
5. **Login with admin credentials** (created during setup)
6. **Start using the system!**

## Default Admin Account

After running setup.php, you can login with:
- **Username**: admin
- **Password**: admin123

⚠️ **Important**: Change the admin password immediately after first login!

## File Structure

```
library-system/
├── api/                    # API endpoints
│   ├── add-recent-activity.php
│   ├── admin-*.php        # Admin API endpoints
│   ├── create-folder.php
│   ├── delete-*.php
│   └── upload-books.php
├── assets/                 # Static assets
│   ├── css/               # Stylesheets
│   ├── images/            # Images
│   ├── js/                # JavaScript files
│   └── uploads/           # User uploaded files
│       ├── system-books/  # System PDF books
│       └── user-books/    # User uploaded PDFs
├── includes/              # Core PHP files
│   ├── config.php         # Configuration
│   ├── db.php            # Database connection
│   └── functions.php     # Utility functions
├── pages/                 # Application pages
│   ├── admin/            # Admin panel
│   ├── dashboard.php      # User dashboard
│   ├── login.php         # Login page
│   ├── register.php      # Registration page
│   └── pdf-viewer.php    # PDF viewer
├── index.php             # Landing page
└── setup.php            # Database setup script
```

## Usage

### For Users
1. Register a new account or login
2. Browse books by grade categories
3. Create personal folders for organizing books
4. Upload PDF books to your folders
5. Read books using the integrated PDF viewer
6. View your recent activity

### For Admins
1. Login with admin credentials
2. Access the admin dashboard
3. Manage users, categories, and system books
4. Monitor user activities
5. Configure system settings

## Security Features

- Input validation and sanitization
- CSRF token protection
- Secure file upload handling
- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- Session management
- File type and size validation

## Customization

### Adding New Grade Categories
Categories can be added through the admin panel or directly in the database.

### Modifying Upload Limits
Edit the `MAX_FILE_SIZE` constant in `includes/config.php`:
```php
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
```

### Changing Application Name
Update the `APP_NAME` constant in `includes/config.php`:
```php
define('APP_NAME', 'Your Library Name');
```

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config.php`
   - Ensure MySQL is running
   - Verify database exists

2. **File Upload Issues**
   - Check folder permissions (`assets/uploads/`)
   - Verify PHP upload limits in `php.ini`
   - Ensure PDF file type validation

3. **PDF Viewer Not Working**
   - Check if PDF.js library is loading properly
   - Verify file paths are correct
   - Check browser console for errors

4. **Admin Access Issues**
   - Ensure you're logged in as admin
   - Check user role in database
   - Verify session is working properly

### Support

For issues and questions:
1. Check the troubleshooting section above
2. Review the error logs
3. Ensure all requirements are met
4. Verify file permissions are set correctly

## License

This project is open source and available under the MIT License.

## Contributing

Feel free to contribute to this project by:
- Reporting bugs
- Suggesting new features
- Submitting pull requests
- Improving documentation

## Credits

- Built with [Bootstrap](https://getbootstrap.com/) for responsive design
- PDF viewing powered by [PDF.js](https://mozilla.github.io/pdf.js/)
- Icons by [Font Awesome](https://fontawesome.com/)
- JavaScript libraries from CDN for optimal performance