# JSON Database Backend

A simple JSON-based database system with authentication built using Node.js and Express.

## Features

- User authentication with JWT
- JSON-based data storage
- CRUD operations for tables and data
- Secure password hashing with bcrypt
- CORS support

## Setup

1. Install dependencies:
```bash
npm install
```

2. Create a `.env` file in the root directory with the following variables:
```
PORT=3000
JWT_SECRET=your-secret-key
```

3. Start the server:
```bash
npm start
```

For development with auto-reload:
```bash
npm run dev
```

## API Endpoints

### Authentication
- `POST /api/login` - Login with username and password
- `POST /api/register` - Register a new user

### Tables
- `GET /api/tables` - Get all tables
- `POST /api/tables` - Create a new table
- `DELETE /api/tables/:name` - Delete a table

### Table Data
- `GET /api/tables/:name/data` - Get all data from a table
- `POST /api/tables/:name/data` - Add data to a table
- `PUT /api/tables/:name/data/:id` - Update data in a table
- `DELETE /api/tables/:name/data/:id` - Delete data from a table

## Security

- Passwords are hashed using bcrypt
- JWT tokens are used for authentication
- All routes except login and register require authentication
- CORS is enabled for cross-origin requests

## Data Storage

Data is stored in JSON files in the `database` directory:
- `users.json` - User credentials
- `tables.json` - Table definitions
- Individual table data files are created as needed 