<?php
session_start();
require_once "../../Database.php";
require_once "../../Models/NanodegreeProgram.php";

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$program = new NanodegreeProgram($conn);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_program'])) {
        $data = [
            $_POST['title'],
            $_POST['description'],
            $_POST['duration_weeks'],
            $_POST['level'],
            $_POST['price'],
            $_POST['partner_id'] ?: null
        ];
        
        if ($program->create($data)) {
            $_SESSION['success'] = "Program added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add program.";
        }
    }
    
    if (isset($_POST['update_program'])) {
        $program_id = $_POST['program_id'];
        $data = [
            $_POST['title'],
            $_POST['description'],
            $_POST['duration_weeks'],
            $_POST['level'],
            $_POST['price'],
            $_POST['partner_id'] ?: null,
            $_POST['status']
        ];
        
        if ($program->update($program_id, $data)) {
            $_SESSION['success'] = "Program updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update program.";
        }
    }
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['delete'])) {
        $program_id = $_GET['delete'];
        
        if ($program->delete($program_id)) {
            $_SESSION['success'] = "Program deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete program.";
        }
    }
}

// Redirect back to programs page
header('Location: ../Programs.php');
exit(); 