<?php
// Database configuration
$host = 'sql100.infinityfree.com';
$db   = 'if0_40118513_youth_center_db'; 
$user = 'if0_40118513';              
$pass = 'changed1221';                  

// Create connection
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die(json_encode(['success'=>false,'message'=>"Connection failed: ".$mysqli->connect_error]));
}
$mysqli->set_charset("utf8mb4"); // Important for Amharic

// Get form data
$email           = trim($_POST['email'] ?? '');
$gender          = $_POST['gender'] ?? '';
$age             = $_POST['age'] ?? '';
$education       = trim($_POST['educational_level'] ?? '');
$work_experience = trim($_POST['work_experience'] ?? '');
$workplace_name  = trim($_POST['workplace_name'] ?? '');
$department      = trim($_POST['department'] ?? '');
$job_position    = trim($_POST['job_position'] ?? '');
$field_of_study  = trim($_POST['field_of_study'] ?? '');
$topics          = $_POST['topics'] ?? [];
$other_topics    = trim($_POST['other_topics'] ?? '');

// --- Normalize Data ---

// Normalize gender
$gender = strtolower(trim($gender));
if ($gender === 'male' || $gender === 'm') $gender = 'Male';
else if ($gender === 'female' || $gender === 'f') $gender = 'Female';
else $gender = 'Other';

// Normalize age groups
$age = trim($age);
if (!in_array($age, ["18-24","25-34","35-44","45+"])) $age = "Other";

// Normalize topics
$topicsArray = array_map('trim', $topics);
$topics = implode(',', $topicsArray);

// --- Prepare and execute SQL ---
$stmt = $mysqli->prepare("INSERT INTO user_responses 
(email, gender, age, education, work_experience, workplace_name, department, job_position, field_of_study, topics, other_topics) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sssssssssss",
    $email,
    $gender,
    $age,
    $education,
    $work_experience,
    $workplace_name,
    $department,
    $job_position,
    $field_of_study,
    $topics,
    $other_topics
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Response submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
