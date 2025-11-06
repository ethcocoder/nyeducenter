<?php
session_start();
header('Content-Type: application/json');

// Database connection (replace with your actual credentials)
$host = "localhost";
$db = "youth_center_db";
$user = "root";
$pass = "";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again later.']));
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$questionnaire_id = isset($_GET['questionnaire_id']) ? sanitize_input($_GET['questionnaire_id']) : null;

if (!$questionnaire_id) {
    echo json_encode(['success' => false, 'message' => 'Questionnaire ID is required.']);
    exit();
}

try {
    // Fetch questionnaire details
    $stmt = $pdo->prepare("SELECT id, title, description FROM questionnaires WHERE id = :questionnaire_id");
    $stmt->bindParam(':questionnaire_id', $questionnaire_id, PDO::PARAM_INT);
    $stmt->execute();
    $questionnaire = $stmt->fetch();

    if (!$questionnaire) {
        echo json_encode(['success' => false, 'message' => 'Questionnaire not found.']);
        exit();
    }

    // Fetch questions for the questionnaire
    $stmt = $pdo->prepare("SELECT id, question_text, question_type, options FROM questions WHERE questionnaire_id = :questionnaire_id ORDER BY id ASC");
    $stmt->bindParam(':questionnaire_id', $questionnaire_id, PDO::PARAM_INT);
    $stmt->execute();
    $questions = $stmt->fetchAll();

    // Decode options for multiple choice questions
    foreach ($questions as &$question) {
        if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'checkbox') {
            $question['options'] = json_decode($question['options'], true);
        } else {
            $question['options'] = null; // No options for text or textarea questions
        }
    }

    echo json_encode(['success' => true, 'questionnaire' => $questionnaire, 'questions' => $questions]);

} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching questionnaire: ' . $e->getMessage()]);
}
?>