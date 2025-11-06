#!/bin/bash

echo "E-Learning System Database and Backend Setup"
echo "============================================"

# Check if MySQL is running
if ! nc -z localhost 3306; then
  echo "MySQL is not running. Please start MySQL service and try again."
  exit 1
fi

# Check if Apache is running
if ! nc -z localhost 80; then
  echo "Apache is not running. Please start Apache service and try again."
  exit 1
fi

echo "Database services are running."

# Create database and import schema
echo "Creating database and importing schema..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS elearning"
mysql -u root elearning < database/schema.sql

if [ $? -ne 0 ]; then
  echo "Database setup failed. Please check the error message."
  exit 1
fi

echo "Database setup completed successfully."

# Install dependencies if needed
if [ ! -d "node_modules" ]; then
  echo "Installing dependencies..."
  npm install
fi

# Initialize database structure and migrate data
echo "Initializing database..."
node utils/initDatabase.js

if [ $? -ne 0 ]; then
  echo "Database initialization failed. Please check the error message."
  exit 1
fi

echo "Database initialization completed."

# Start the server
echo "Starting the backend server..."
echo "You can stop the server by pressing Ctrl+C"
npm start 