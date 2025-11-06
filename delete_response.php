<?php
header('Content-Type: application/json');


$servername = "sql100.infinityfree.com";
$username = "if0_40118513";
$password = "changed1221";
$dbname = "if0_40118513_youth_center_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
}

$id = $_POST['id'];

if (isset($id)) {
    $stmt = $conn->prepare("DELETE FROM user_responses WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}

$conn->close();

?>