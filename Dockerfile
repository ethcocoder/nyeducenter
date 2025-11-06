# Use an official Python runtime as a parent image
FROM python:3.9-slim-buster

# Set the working directory in the container
WORKDIR /app

RUN apt-get update && \
    apt-get install -y \
    build-essential \
    libpq-dev \
    --no-install-recommends && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install any needed packages specified in requirements.txt
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy the rest of the application code to the container
COPY . .

# Expose the port that Gunicorn will listen on
EXPOSE 5000

# Define environment variables for Flask and Gunicorn
ENV FLASK_APP=app.py
ENV FLASK_ENV=production
# Assuming 'db' is the service name for your PostgreSQL container in a Docker network
ENV SQLALCHEMY_DATABASE_URI="postgresql://webapp_user:password@db:5432/webapp_db"

# Run Gunicorn to serve the Flask application
# Adjust the number of workers and threads based on your server's CPU cores and expected load
CMD ["gunicorn", "--bind", "0.0.0.0:5000", "app:app"]
