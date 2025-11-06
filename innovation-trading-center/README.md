# Innovation Trading Center Platform

A modern web platform for Ethiopian innovators and sponsors/investors to connect, showcase innovations, and collaborate. Built with custom PHP MVC, MySQL, and Bootstrap.

## Features
- Multi-role authentication (Innovator, Sponsor/Investor, Admin)
- Innovation posting, editing, and browsing
- Messaging system (user-to-user, user-to-admin)
- Favorites, dashboards, and admin management
- Modern, responsive UI with Bootstrap
- User registration (Innovator, Sponsor, Admin)
- Post, edit, and manage innovations
- Sponsor innovations (Sponsors can sponsor any innovation)
- Innovators can view all sponsorships for their innovations on a dedicated page
- Innovators can update the status of each sponsorship (Pending, Approved, Completed, Rejected)

## Sponsorship Feature
- Sponsors can sponsor any innovation via the "Sponsor This Innovation" button on the innovation detail page.
- Innovators can view all sponsorships for their innovations by clicking the **Sponsorships** link in the sidebar.
- On the Sponsorships page, innovators can change the status of each sponsorship using the dropdown and update button.

## Default Admin Login
- **Email:** admin@innovationcenter.et
- **Password:** admin123

## Folder Structure
```
innovation-trading-center/
  app/           # (MVC app code)
  controllers/   # Main controllers
  models/        # Data models
  views/         # UI templates
  public/        # (Optional) public web root
  bootstrap/     # Local Bootstrap assets
  config/        # Config and database
  index.php      # Main entry point/router
```

## Requirements
- PHP 8.1+
- MySQL (or MariaDB)
- Composer (for dependencies, if needed)

## Setup & Running Locally

1. **Clone the repository**
2. **Install dependencies** (if any):
   ```sh
   composer install
   ```
3. **Set up the database:**
   - Create a MySQL database (e.g., `inotrade`)
   - Import the schema from `config/schema.sql`
   - Update `config/database.php` with your DB credentials
4. **Run the PHP built-in server:**
   - For the main entry point:
     ```sh
     cd innovation-trading-center
     php -S localhost:8000
     ```
   - Or, if using the `public/` folder as web root:
     ```sh
     cd innovation-trading-center/public
     php -S localhost:8000
     ```
5. **Open in your browser:**
   - Go to [http://localhost:8000](http://localhost:8000)

## Notes
- All Bootstrap assets are local (see `bootstrap/` folder)
- For production, configure a real web server (Apache/Nginx) and secure your `.env`/config files

---

**Enjoy building with the Innovation Trading Center Platform!** 

## Setup
1. Import the schema from `config/schema.sql` into your MySQL database.
2. (If using sponsorships) Also run the SQL for the `sponsorships` table (see below if not already in your schema):

```sql
CREATE TABLE sponsorships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sponsor_id INT NOT NULL,
    innovation_id INT NOT NULL,
    amount DECIMAL(12,2) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sponsor_id) REFERENCES users(id),
    FOREIGN KEY (innovation_id) REFERENCES innovations(id)
);
```

3. Configure your database connection in `config/database.php`.
4. Start the PHP server and log in with the admin credentials above.

---
For more details, see the code comments and documentation in each controller and model. 