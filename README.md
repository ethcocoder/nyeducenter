# Curriculem

## Engaging Learning Platform

Curriculem is a comprehensive web-based learning management system designed to deliver interactive educational content. It provides a structured environment for users to engage with quizzes, practical labs, coding sessions, and informative notes, all while tracking their progress and earning certificates.

## Features

*   **Dynamic Course Structure:** Organize content into hierarchical modules and submodules.
*   **Interactive Quizzes:** Engage with multiple-choice quizzes, track scores, and manage attempts with lockout mechanisms.
*   **Practical Labs:** Step-by-step guided labs with input validation to ensure hands-on learning.
*   **Coding Sessions:** Validate user-submitted HTML, CSS, JavaScript, and backend code against predefined requirements.
*   **Informative Notes:** Access detailed markdown-based notes linked to specific quizzes.
*   **User Progress Tracking:** Monitor completion status for individual content items and overall module progress.
*   **Certifications:** Award certificates upon successful completion of modules.
*   **Robust Admin Dashboard:**
    *   Full control over course content: create, edit, delete, move, duplicate, and publish modules and submodules.
    *   Seamless content integration: link quizzes, labs, sessions, and notes to the course structure.
    *   Content upload: easily add new quizzes, labs, and notes via markdown and YAML files.
*   **User Authentication:** Secure registration, login, and profile management.

## Technologies Used

*   **Backend:** Python, Flask, SQLAlchemy, Flask-Bcrypt, Flask-Login, Flask-Migrate, Click
*   **Frontend:** HTML, CSS, JavaScript, Jinja2
*   **Database:** PostgreSQL
*   **Content Parsing:** Markdown, PyYAML, BeautifulSoup

## Setup & Installation

Follow these steps to get Curriculem up and running on your local machine.

### Prerequisites

*   Python 3.8+
*   PostgreSQL

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/Curriculem.git
cd Curriculem
```

### 2. Set up a Virtual Environment

It's recommended to use a virtual environment to manage project dependencies.

```bash
python3 -m venv venv
```

**Activate the virtual environment:**

*   **On macOS/Linux:**
    ```bash
    source venv/bin/activate
    ```
*   **On Windows (Command Prompt):**
    ```bash
    .\venv\Scripts\activate.bat
    ```
*   **On Windows (PowerShell):**
    ```bash
    .\venv\Scripts\Activate.ps1
    ```

### 3. Install Dependencies

```bash
pip install -r requirements.txt
```

### 4. Database Setup

Ensure your PostgreSQL server is running. Update the `SQLALCHEMY_DATABASE_URI` in `app.py` if your database credentials or host are different.

```python
# app.py
app.config['SQLALCHEMY_DATABASE_URI'] = 'postgresql://webapp_user:password@localhost/webapp_db'
```

Then, initialize and run database migrations:

```bash
flask db init
flask db migrate -m "Initial migration"
flask db upgrade
```

### 5. Create an Admin User

You can create an admin user via the CLI:

```bash
flask create-admin
```
(Default credentials: username `admin`, password `123456`)

## Usage

### Running the Application

```bash
python app.py
```

The application will typically run on `http://127.0.0.1:5000/`.

### Accessing the Admin Dashboard

Log in with your admin credentials and navigate to `/admin`.

### CLI Commands

*   **`flask create-admin`**: Creates a default admin user.
*   **`flask process-quiz <filepath>`**: Processes a markdown quiz file and adds it to the database.
*   **`flask process-lab <filepath>`**: Processes a markdown lab file and adds it to the database.
*   **`flask promote <username>`**: Promotes an existing user to an admin role.

## Deployment with Docker

To deploy the application using Docker, follow these steps:

### 1. Build the Docker Image

Navigate to the root directory of the project where the `Dockerfile` is located and run:

```bash
docker build -t curriculem .
```

### 2. Run the Docker Container

You can run the Docker container. If you are using a separate PostgreSQL container, ensure it's accessible from the Flask container (e.g., via a Docker network).

```bash
docker run -p 5000:5000 --name curriculem-app curriculem
```

If you have a PostgreSQL container named `db` on the same Docker network, you might use:

```bash
docker run -p 5000:5000 --name curriculem-app --network your_docker_network curriculem
```

Remember to set up your PostgreSQL database and run migrations within the container if it's a fresh deployment. You can do this by exec-ing into the running container:

```bash
docker exec -it curriculem-app flask db upgrade
docker exec -it curriculem-app flask create-admin
```



We welcome contributions! Please see `CONTRIBUTING.md` for details.

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.
