# API Documentation

## Endpoint: `/admin_dashboard_data.php`

- **Method:** `GET`
- **Description:** Retrieves data for the admin dashboard, including questionnaire responses, questionnaires, and subscribers.
- **Authentication:** Required (Admin)
- **Parameters:** None
- **Success Response:**
  ```json
  {
    "success": true,
    "responses": [
      {
        "response_id": "1",
        "questionnaire_id": "101",
        "user_id": "user123",
        "submitted_at": "2023-10-26 10:00:00",
        "answers": [
          {"question_id": "q1", "answer_text": "Answer 1"},
          {"question_id": "q2", "answer_text": "Answer 2"}
        ]
      }
    ],
    "questionnaires": [
      {"id": "101", "title": "Customer Feedback", "created_at": "2023-01-15"},
      {"id": "102", "title": "Employee Satisfaction", "created_at": "2023-02-20"}
    ],
    "subscribers": [
      {"id": "1", "email": "test1@example.com", "subscribed_at": "2023-03-01"},
      {"id": "2", "email": "test2@example.com", "subscribed_at": "2023-03-05"}
    ],
    "training_needs_analysis": {
      "total_responses": 150,
      "average_score": 75.5,
      "top_skills": ["Communication", "Leadership"],
      "gap_areas": ["Project Management"]
    }
  }
  ```
- **Error Response:**
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "An unexpected error occurred. Please try again later."
    }
    ```

## Endpoint: `/admin_login.php`

- **Method:** `POST`
- **Description:** Handles administrator login.
- **Authentication:** None (This is the authentication endpoint itself)
- **Parameters:**
  - `email` (string, required): The administrator's email address.
  - `password` (string, required): The administrator's password.
- **Success Response:**
  - `200 OK`:
    ```json
    {
      "success": true,
      "message": "Login successful.",
      "user": {
        "id": "admin_id",
        "email": "admin@example.com",
        "role": "admin"
      }
    }
    ```
- **Error Responses:**
  - `400 Bad Request`:
    ```json
    {
      "success": false,
      "message": "Email and password are required."
    }
    ```
  - `401 Unauthorized`:
    ```json
    {
      "success": false,
      "message": "Invalid email or password."
    }
    ```
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Database error: [error details]"
    }
    ```

## Endpoint: `/admin_question_management.php`

- **Method:** `GET`
- **Description:** Retrieves questions, optionally filtered by questionnaire ID.
- **Authentication:** Required (Admin)
- **Parameters:**
  - `questionnaire_id` (integer, optional): The ID of the questionnaire to filter questions by.
- **Success Response:**
  ```json
  {
    "success": true,
    "questions": [
      {
        "id": "1",
        "questionnaire_id": "101",
        "question_text": "What is your favorite color?",
        "question_type": "text",
        "options": null
      },
      {
        "id": "2",
        "questionnaire_id": "101",
        "question_text": "How satisfied are you?",
        "question_type": "rating",
        "options": "1,2,3,4,5"
      }
    ]
  }
  ```
- **Error Response:**
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Database error: [error details]"
    }
    ```

- **Method:** `POST`
- **Description:** Creates a new question.
- **Authentication:** Required (Admin)
- **Parameters (JSON Body):**
  - `questionnaire_id` (integer, required): The ID of the questionnaire the question belongs to.
  - `question_text` (string, required): The text of the question.
  - `question_type` (string, required): The type of the question (e.g., 'text', 'rating', 'multiple_choice').
  - `options` (string, optional): Comma-separated options for multiple-choice or rating questions.
- **Success Response:**
  ```json
  {
    "success": true,
    "message": "Question added successfully.",
    "id": "new_question_id"
  }
  ```
- **Error Responses:**
  - `400 Bad Request`:
    ```json
    {
      "success": false,
      "message": "Invalid input: Missing required fields or invalid data."
    }
    ```
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Failed to add question."
    }
    ```

- **Method:** `PUT`
- **Description:** Updates an existing question.
- **Authentication:** Required (Admin)
- **Parameters (JSON Body):**
  - `id` (integer, required): The ID of the question to update.
  - `questionnaire_id` (integer, optional): The ID of the questionnaire the question belongs to.
  - `question_text` (string, optional): The updated text of the question.
  - `question_type` (string, optional): The updated type of the question.
  - `options` (string, optional): Updated comma-separated options.
- **Success Response:**
  ```json
  {
    "success": true,
    "message": "Question updated successfully."
  }
  ```
- **Error Responses:**
  - `400 Bad Request`:
    ```json
    {
      "success": false,
      "message": "Invalid input: Missing question ID or no data to update."
    }
    ```
  - `404 Not Found`:
    ```json
    {
      "success": false,
      "message": "Question not found."
    }
    ```
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Failed to update question."
    }
    ```

- **Method:** `DELETE`
- **Description:** Deletes a question.
- **Authentication:** Required (Admin)
- **Parameters (JSON Body):**
  - `id` (integer, required): The ID of the question to delete.
- **Success Response:**
  ```json
  {
    "success": true,
    "message": "Question deleted successfully."
  }
  ```
- **Error Responses:**
  - `400 Bad Request`:
    ```json
    {
      "success": false,
      "message": "Invalid input: Missing question ID."
    }
    ```
  - `404 Not Found`:
    ```json
    {
      "success": false,
      "message": "Question not found."
    }
    ```
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Failed to delete question."
    }
    ```

## Endpoint: `/admin_subscriber_management.php`

- **Method:** `GET`
- **Description:** Retrieves a list of all subscribers.
- **Authentication:** Required (Admin)
- **Parameters:** None
- **Success Response:**
  ```json
  {
    "success": true,
    "subscribers": [
      {
        "id": "1",
        "email": "subscriber1@example.com",
        "subscribed_at": "2023-01-01 12:00:00"
      },
      {
        "id": "2",
        "email": "subscriber2@example.com",
        "subscribed_at": "2023-01-05 14:30:00"
      }
    ]
  }
  ```
- **Error Response:**
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Database error: [error details]"
    }
  ```

- **Method:** `POST`
- **Description:** Adds a new subscriber.
- **Authentication:** Required (Admin)
- **Parameters (JSON Body):**
  - `email` (string, required): The email address of the subscriber to add.
- **Success Response:**
  ```json
  {
    "success": true,
    "message": "Subscriber added successfully",
    "id": "new_subscriber_id",
    "email": "new_subscriber@example.com",
    "subscribed_at": "2023-10-26 15:30:00"
  }
  ```
- **Error Responses:**
  - `400 Bad Request`:
    ```json
    {
      "success": false,
      "message": "Invalid email format"
    }
    ```
  - `409 Conflict`:
    ```json
    {
      "success": false,
      "message": "Email already subscribed"
    }
    ```
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Failed to add subscriber"
    }
    ```

- **Method:** `DELETE`
- **Description:** Deletes a subscriber.
- **Authentication:** Required (Admin)
- **Parameters (JSON Body):**
  - `id` (integer, required): The ID of the subscriber to delete.
- **Success Response:**
  ```json
  {
    "success": true,
    "message": "Subscriber deleted successfully"
  }
  ```
- **Error Responses:**
  - `400 Bad Request`:
    ```json
    {
      "success": false,
      "message": "Invalid subscriber ID"
    }
    ```
  - `404 Not Found`:
    ```json
    {
      "success": false,
      "message": "Subscriber not found"
    }
    ```
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Failed to delete subscriber"
    }
    ```

## Endpoint: `/generate_training_report.php`

- **Method:** `GET`
- **Description:** Generates a training needs analysis report based on questionnaire responses.
- **Authentication:** Required (Admin)
- **Parameters:** None
- **Success Response:**
  ```json
  {
    "success": true,
    "analysis": {
      "question_id_1": {
        "question_text": "Question 1 text",
        "question_type": "multiple_choice",
        "summary": {
          "Option A": 10,
          "Option B": 5
        }
      },
      "question_id_2": {
        "question_text": "Question 2 text",
        "question_type": "text",
        "summary": {
          "sample_answers": [
            "Sample answer 1",
            "Sample answer 2"
          ],
          "total_responses": 15
        }
      }
    }
  }
  ```
- **Error Responses:**
  - `401 Unauthorized`:
    ```json
    {
      "success": false,
      "message": "Unauthorized access."
    }
    ```
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Error generating report: [error details]"
    }
    ```

## Endpoint: `/get_questionnaire.php`

- **Method:** `GET`
- **Description:** Retrieves a specific questionnaire and its associated questions.
- **Authentication:** None
- **Parameters:**
  - `questionnaire_id` (integer, required): The ID of the questionnaire to retrieve.
- **Success Response:**
  ```json
  {
    "success": true,
    "questionnaire": {
      "id": "1",
      "title": "Sample Questionnaire",
      "description": "This is a sample questionnaire."
    },
    "questions": [
      {
        "id": "101",
        "question_text": "What is your name?",
        "question_type": "text",
        "options": null
      },
      {
        "id": "102",
        "question_text": "Choose your favorite color:",
        "question_type": "multiple_choice",
        "options": ["Red", "Green", "Blue"]
      }
    ]
  }
  ```
- **Error Responses:**
  - `400 Bad Request`:
    ```json
    {
      "success": false,
      "message": "Questionnaire ID is required."
    }
    ```
  - `404 Not Found`:
    ```json
    {
      "success": false,
      "message": "Questionnaire not found."
    }
    ```
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Error fetching questionnaire: [error details]"
    }
    ```

## Endpoint: `/get_training_needs.php`

- **Method:** `GET`
- **Description:** Provides placeholder data for training needs analysis. In a real application, this would involve complex queries and aggregation based on questionnaire responses.
- **Authentication:** Required (Admin)
- **Parameters:** None
- **Success Response:**
  ```json
  {
    "success": true,
    "data": [
      {"label": "Communication Skills", "value": 75},
      {"label": "Leadership Development", "value": 60},
      {"label": "Technical Skills", "value": 80},
      {"label": "Teamwork & Collaboration", "value": 70},
      {"label": "Problem Solving", "value": 65}
    ]
  }
  ```
- **Error Response:**
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Database connection failed: [error details]"
    }
    ```

## Endpoint: `/submit.php`

- **Method:** `POST`
- **Description:** Submits questionnaire responses and answers.
- **Authentication:** None
- **Parameters (Form Data):**
  - `user_email` (string, required): The email of the user submitting the questionnaire.
  - `questionnaire_id` (integer, required): The ID of the questionnaire being submitted.
  - `answers` (array, required): An associative array where keys are `question_id` (integer) and values are `answer_text` (string).
- **Success Response:**
  ```json
  {
    "success": true,
    "message": "Questionnaire submitted successfully!"
  }
  ```
- **Error Responses:**
  - `400 Bad Request`:
    ```json
    {
      "success": false,
      "message": "Validation errors",
      "errors": [
        "User email is required.",
        "Invalid email format.",
        "Questionnaire ID is required.",
        "No answers provided."
      ]
    }
    ```
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "An unexpected error occurred during submission. Please try again later."
    }
    ```

## Endpoint: `/export_responses_pdf.php`

- **Method:** `GET`
- **Description:** Generates a PDF report of questionnaire responses, including response ID, questionnaire title, user email, submission time, and answers.
- **Authentication:** Required (Admin)
- **Parameters:** None (fetches all responses; future support for `questionnaire_id` filtering planned)
- **Success Response:** PDF file download with formatted responses and answers.
- **Error Response:**
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Failed to generate PDF report."
    }
    ```

## Endpoint: `/export_responses_csv.php`

- **Method:** `GET`
- **Description:** Exports questionnaire responses to a CSV file, including response ID, user email, submission time, and answers.
- **Authentication:** Required (Admin)
- **Parameters:**
  - `questionnaire_id` (integer, optional): Filters responses by questionnaire ID (omits to export all responses).
- **Success Response:** CSV file download with structured response data.
- **Error Response:**
  - `500 Internal Server Error`:
    ```json
    {
      "success": false,
      "message": "Failed to generate CSV export."
    }
    ```