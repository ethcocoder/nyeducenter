<?php
function db_connect() {
    $servername = "sql100.infinityfree.com";
    $username = "if0_40118513";
    $password = "changed1221";
    $dbname = "if0_40118513_youth_center_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>