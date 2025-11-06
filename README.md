# Youth Center Frontend

## Project Overview
A responsive frontend for the Gulelekfle Ketema Wereda 4 No 2 Youth Center, featuring a public website, admin dashboard, and training needs questionnaire. Designed to empower youth through education and community engagement.

## Key Features
- **Public Website**: Home page with mission, services, and newsletter subscription (index.html)
- **Admin Dashboard**: Secure login (admin_login.html) and management interface (admin_dashboard.html) for questionnaire responses, subscriptions, and reports
- **Training Needs Questionnaire**: Amharic-language form (questionnaire.html) with responsive design
- **Responsive Layout**: Optimized for mobile, tablet, and desktop
- **Consistent Design System**: Unified color scheme, typography, and UI components
- **Interactive Elements**: Chart.js integration for admin reports, form validation, and tab navigation

## Tech Stack
- HTML5
- CSS3 (with custom properties and gradients)
- JavaScript (vanilla)
- Chart.js (for data visualization)
- Google Fonts (Inter, Noto Sans Ethiopic)

## Setup Instructions
1. **Clone the Repository**: Copy the project files to your local machine
2. **Launch a Web Server**: Use XAMPP, MAMP, or a static server (e.g., `python -m http.server`)
3. **Access the Site**: 
   - Public site: `http://localhost/w4no2qustionary/index.html`
   - Admin login: `http://localhost/w4no2qustionary/admin_login.html`
   - Questionnaire: `http://localhost/w4no2qustionary/questionnaire.html`

## Usage Guidelines
- **Admin Dashboard**: Use the sidebar to manage questionnaire content, view responses, and generate reports
- **Questionnaire**: Users can submit responses which are stored (backend integration required for persistence)
- **Newsletter**: Subscriptions are collected via the homepage form

## Design System
- **Colors**: Primary (blue), Accent (orange), Neutral (grays)
- **Typography**: Inter (Latin) and Noto Sans Ethiopic (Amharic)
- **Responsive Breakpoints**: 1024px (tablet), 768px (mobile)

## Development Notes
- All CSS is inline for simplicity; consider extracting to a separate file for larger projects
- Add backend integration (e.g., Firebase, PHP) for data persistence
- Implement user authentication for the admin dashboard
- Add form validation for the questionnaire
nati
$2y$10$tnObZmZP5sGnmFFn4tNPAulnR4JvkROQDJpqFAcBBgk/iwOFVZEGi