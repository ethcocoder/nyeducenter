<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root"; // your DB username
$password = ""; // your DB password
$dbname = "youth_center_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode([]));
}

// Filters
$email = isset($_GET['email']) ? $_GET['email'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$age = isset($_GET['age']) ? $_GET['age'] : '';

$sql = "SELECT * FROM responses WHERE 1";

if($email) $sql .= " AND email LIKE '%".$conn->real_escape_string($email)."%'";
if($gender) $sql .= " AND gender='".$conn->real_escape_string($gender)."'";
if($age) $sql .= " AND age='".$conn->real_escape_string($age)."'";

$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);

$data = [];
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        // Join topics array if stored as JSON
        if(isset($row['topics'])) $row['topics'] = implode(", ", explode(",", $row['topics']));
        $data[] = $row;
    }
}

echo json_encode($data);
$conn->close();
?>
