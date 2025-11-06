<?php
session_start();
require_once "../../Database.php";
require_once "../../Models/Project.php";
require_once "../../Models/Course.php";

// Check if user is instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: /login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$project = new Project($conn);
$course = new Course($conn);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_project'])) {
        // Verify course belongs to instructor
        $course_id = $_POST['course_id'];
        $course_data = $course->getById($course_id);
        
        if ($course_data && $course_data['instructor_id'] == $_SESSION['user_id']) {
            $data = [
                $course_id,
                $_POST['title'],
                $_POST['description'],
                $_POST['rubric'],
                $_POST['due_date']
            ];
            
            if ($project->create($data)) {
                $_SESSION['success'] = "Project added successfully!";
            } else {
                $_SESSION['error'] = "Failed to add project.";
            }
        } else {
            $_SESSION['error'] = "Unauthorized access.";
        }
    }
    
    if (isset($_POST['update_project'])) {
        $project_id = $_POST['project_id'];
        $project_data = $project->getById($project_id);
        
        // Verify project belongs to instructor's course
        if ($project_data) {
            $course_data = $course->getById($project_data['course_id']);
            if ($course_data && $course_data['instructor_id'] == $_SESSION['user_id']) {
                $data = [
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['rubric'],
                    $_POST['due_date']
                ];
                
                if ($project->update($project_id, $data)) {
                    $_SESSION['success'] = "Project updated successfully!";
                } else {
                    $_SESSION['error'] = "Failed to update project.";
                }
            } else {
                $_SESSION['error'] = "Unauthorized access.";
            }
        } else {
            $_SESSION['error'] = "Project not found.";
        }
    }
    
    if (isset($_POST['review_submission'])) {
        $submission_id = $_POST['submission_id'];
        $status = $_POST['status'];
        $feedback = $_POST['feedback'];
        
        if ($project->updateSubmission($submission_id, $status, $feedback, $_SESSION['user_id'])) {
            $_SESSION['success'] = "Submission reviewed successfully!";
        } else {
            $_SESSION['error'] = "Failed to review submission.";
        }
    }
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['delete'])) {
        $project_id = $_GET['delete'];
        $project_data = $project->getById($project_id);
        
        // Verify project belongs to instructor's course
        if ($project_data) {
            $course_data = $course->getById($project_data['course_id']);
            if ($course_data && $course_data['instructor_id'] == $_SESSION['user_id']) {
                if ($project->delete($project_id)) {
                    $_SESSION['success'] = "Project deleted successfully!";
                } else {
                    $_SESSION['error'] = "Failed to delete project.";
                }
            } else {
                $_SESSION['error'] = "Unauthorized access.";
            }
        } else {
            $_SESSION['error'] = "Project not found.";
        }
    }
}

// Redirect back to projects page
header('Location: ../Projects.php' . (isset($_POST['course_id']) ? '?course_id=' . $_POST['course_id'] : ''));
exit(); 