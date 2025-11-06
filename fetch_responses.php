<?php
// --- Database config ---
$host = 'sql100.infinityfree.com';
$db   = 'if0_40118513_youth_center_db';
$user = 'if0_40118513';
$pass = 'changed1221';

// --- Connect to database ---
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die(json_encode([]));
}

// --- Ensure UTF-8 everywhere ---
$mysqli->query("SET NAMES 'utf8mb4'");
$mysqli->query("SET CHARACTER SET utf8mb4");
$mysqli->query("SET SESSION collation_connection = 'utf8mb4_general_ci'");
$mysqli->set_charset("utf8mb4");

// --- Fetch responses ---
$result = $mysqli->query("SELECT * FROM user_responses ORDER BY submitted_at DESC");

$responses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Normalize gender
        $gender = strtolower(trim($row['gender']));
        if ($gender === 'male' || $gender === 'm') $row['gender'] = 'Male';
        else if ($gender === 'female' || $gender === 'f') $row['gender'] = 'Female';
        else $row['gender'] = 'Other';

        // Normalize age
        $age = trim($row['age']);
        if (!in_array($age, ["18-24", "25-34", "35-44", "45+"])) $row['age'] = "Other";

        // Normalize topics
        $row['topics'] = trim($row['topics']);

        $responses[] = $row;
    }
}

// --- Output JSON safely ---
header('Content-Type: application/json; charset=utf-8');
echo json_encode($responses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$mysqli->close();
?>
