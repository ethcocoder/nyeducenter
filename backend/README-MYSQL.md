# MySQL Database Setup with XAMPP

This document provides instructions for setting up the MySQL database for the e-learning system using XAMPP.

## Prerequisites

1. [XAMPP](https://www.apachefriends.org/download.html) installed on your system
2. Node.js and npm installed

## Quick Setup (Automated)

We've created automated setup scripts to handle the entire setup process. Simply:

1. Start XAMPP and make sure both MySQL and Apache are running
2. Run one of the following scripts based on your operating system:

### Option 1: Universal Setup (Recommended)

```bash
cd backend
node setup.js
```

This script will automatically detect your operating system and run the appropriate setup script.

### Option 2: Windows Setup

```bash
cd backend
setup.bat
```

### Option 3: Linux/Mac Setup

```bash
cd backend
chmod +x setup.sh
./setup.sh
```

The setup script will:
- Verify XAMPP services are running
- Create the database
- Import the schema
- Install dependencies if needed
- Initialize the database
- Start the backend server

## Manual Setup (Alternative)

If you prefer to set up the system manually, follow these steps:

### 1. Install Required Dependencies

```bash
cd backend
npm install mysql2 dotenv --save
```

### 2. Start XAMPP

1. Open the XAMPP Control Panel
2. Start the Apache and MySQL services
3. Verify both services are running (status should show "Running" in green)

**IMPORTANT: XAMPP must be running with MySQL service started before proceeding to the next steps. If MySQL is not running, you will get connection errors.**

### 3. Create the Database

There are two ways to create the database:

#### Option 1: Using phpMyAdmin (GUI) - Recommended for Beginners

1. Open your browser and navigate to `http://localhost/phpmyadmin`
2. Click on "New" in the left sidebar to create a new database
3. Enter "elearning" as the database name, select "utf8mb4_general_ci" as the collation, and click "Create"
4. Once created, you can import the schema manually by going to the "Import" tab and selecting the `backend/database/schema.sql` file

#### Option 2: Using the Command Line

1. Open a command prompt/terminal
2. Run:
   ```bash
   cd C:\xampp\mysql\bin
   mysql -u root -p
   ```
   (Press Enter when prompted for a password if you haven't set one)
3. Create the database and apply the schema:
   ```sql
   CREATE DATABASE IF NOT EXISTS elearning;
   USE elearning;
   source C:/path/to/project/backend/database/schema.sql;
   ```

### 4. Configure Environment Variables

Make sure your `.env` file in the backend directory has the following contents:

```
PORT=5000
NODE_ENV=development

# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=elearning

# JWT Secret for Authentication
JWT_SECRET=your-secret-jwt-token-for-elearning-system

# Migration Flag
MIGRATE_JSON_TO_MYSQL=true
```

Adjust the values as needed. If you set a password for your MySQL server, update the `DB_PASSWORD` field.

### 5. Initialize the Database

**Ensure XAMPP is running with MySQL service started**, then run:

```bash
cd backend
node utils/initDatabase.js
```

If you get an error about not being able to connect to the database, double-check that:
- XAMPP is running with MySQL service started
- The database "elearning" exists
- Your .env file has the correct database configuration

### 6. Start the Server

```bash
npm start
```

The server should now be running, connected to MySQL via XAMPP.

## Troubleshooting

### Connection Issues

If you encounter connection issues:

1. **Verify XAMPP is running**: Make sure both Apache and MySQL services are running in XAMPP Control Panel
2. **Check database existence**: Ensure you've created the "elearning" database
3. **Check credentials**: Ensure your .env file has the correct database credentials
4. **Try phpMyAdmin**: Verify you can connect to MySQL through phpMyAdmin at http://localhost/phpmyadmin
5. **Port conflict**: Make sure no other services are using port 3306 (default MySQL port)
6. **Firewall settings**: Check if your firewall is blocking MySQL connections
7. **MySQL logs**: Check XAMPP MySQL logs for any errors

If you still have issues, try:
```bash
cd C:\xampp\mysql\bin
mysql -u root -p
```
Then enter your password (or just press Enter if no password), and try:
```sql
SHOW DATABASES;
```
This should list all databases. If you can connect and see databases, the issue might be in your Node.js connection settings.

### Data Migration Issues

If data migration fails:

1. Check the JSON files in the `backend/data` directory to ensure they're valid
2. Review the error messages for specific problems
3. Try running the migration with fewer collections at a time
4. Make sure the schema was properly imported and tables exist

## Model Changes

All models have been updated from JSON file storage to use MySQL:

- User model: Maps to the `users` table
- Course model: Maps to the `courses` table
- Assignment model: Maps to the `assignments` and `assignment_submissions` tables
- Quiz model: Maps to the `quizzes`, `quiz_questions`, and `question_options` tables
- Grade model: Maps to the `grade_items` and `student_grades` tables

## Route Changes

The route handlers have been updated to work with the new MySQL models.