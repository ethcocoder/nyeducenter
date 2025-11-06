<?php
session_start();
header('Content-Type: application/json');

// Include necessary files
require_once 'auth_middleware.php'; // For authentication and authorization

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

// Ensure only authenticated admin users can access this report
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Fetch all questions to map answers to questions
try {
    $stmt_questions = $pdo->query("SELECT id, question_text, question_type FROM questions ORDER BY id ASC");
    $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);
    $question_map = [];
    foreach ($questions as $question) {
        $question_map[$question['id']] = $question;
    }

    // Fetch all responses and their answers
    $stmt_responses = $pdo->query("SELECT r.id as response_id, r.user_email, r.submitted_at, a.question_id, a.answer_text FROM responses r JOIN answers a ON r.id = a.response_id ORDER BY r.id, a.question_id");
    $raw_data = $stmt_responses->fetchAll(PDO::FETCH_ASSOC);

    $report_data = [];
    foreach ($raw_data as $row) {
        $response_id = $row['response_id'];
        $question_id = $row['question_id'];
        $answer_text = $row['answer_text'];

        if (!isset($report_data[$response_id])) {
            $report_data[$response_id] = [
                'user_email' => $row['user_email'],
                'submitted_at' => $row['submitted_at'],
                'answers' => []
            ];
        }
        $report_data[$response_id]['answers'][$question_id] = $answer_text;
    }

    // Process data for training needs analysis
    // This is a basic example; more complex analysis would go here.
    // For instance, counting responses for each option in multiple-choice questions.
    $analysis_results = [];
    foreach ($question_map as $q_id => $question) {
        $analysis_results[$q_id] = [
            'question_text' => $question['question_text'],
            'question_type' => $question['question_type'],
            'summary' => []
        ];

        if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'checkbox') {
            // For multiple choice/checkbox, count occurrences of each option
            $options_counts = [];
            foreach ($report_data as $response) {
                if (isset($response['answers'][$q_id])) {
                    $answer = $response['answers'][$q_id];
                    // Handle multiple selections for checkbox
                    if ($question['question_type'] === 'checkbox') {
                        $selected_options = json_decode($answer, true);
                        if (is_array($selected_options)) {
                            foreach ($selected_options as $opt) {
                                $options_counts[$opt] = ($options_counts[$opt] ?? 0) + 1;
                            }
                        }
                    } else {
                        $options_counts[$answer] = ($options_counts[$answer] ?? 0) + 1;
                    }
                }
            }
            $analysis_results[$q_id]['summary'] = $options_counts;
        } else {
            // For text/textarea, just list some sample answers or provide a general overview
            $sample_answers = [];
            $count = 0;
            foreach ($report_data as $response) {
                if (isset($response['answers'][$q_id]) && $count < 5) { // Limit to 5 sample answers
                    $sample_answers[] = $response['answers'][$q_id];
                    $count++;
                }
            }
            $analysis_results[$q_id]['summary'] = ['sample_answers' => $sample_answers, 'total_responses' => count($report_data)];
        }
    }

    echo json_encode(['success' => true, 'analysis' => $analysis_results]);

} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error generating report: ' . $e->getMessage()]);
}

?>