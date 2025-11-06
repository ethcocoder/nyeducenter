<?php
require_once "../../includes/header.php";
requireRole(['student']);

// Get database connection
$conn = getDBConnection();

// Include models
require_once "../../Models/Project.php";
require_once "../../Models/Course.php";
require_once "../../Models/EnrolledStudent.php";

$project = new Project($conn);
$course = new Course($conn);
$enrolled = new EnrolledStudent($conn);

// Handle project submission
if (isset($_POST['submit_project'])) {
    $project_id = $_POST['project_id'] ?? null;
    $submission_url = $_POST['submission_url'] ?? null;
    
    if (!$project_id || !$submission_url) {
        setFlashMessage('Missing required fields', 'danger');
        redirect('Projects.php');
    }
    
    // Validate URL
    if (!filter_var($submission_url, FILTER_VALIDATE_URL)) {
        setFlashMessage('Invalid submission URL', 'danger');
        redirect('Project-View.php?id=' . $project_id);
    }
    
    // Verify student is enrolled in the course
    $project_details = $project->getById($project_id);
    if (!$project_details) {
        setFlashMessage('Project not found', 'danger');
        redirect('Projects.php');
    }
    
    $is_enrolled = false;
    $enrolled_courses = $enrolled->getByStudentId($_SESSION['user_id']);
    foreach ($enrolled_courses as $ec) {
        if ($ec['course_id'] == $project_details['course_id']) {
            $is_enrolled = true;
            break;
        }
    }
    
    if (!$is_enrolled) {
        setFlashMessage('You are not enrolled in this course', 'danger');
        redirect('Projects.php');
    }
    
    // Submit project
    if ($project->submitProject($project_id, $_SESSION['user_id'], $submission_url)) {
        setFlashMessage('Project submitted successfully', 'success');
    } else {
        setFlashMessage('Failed to submit project', 'danger');
    }
    
    redirect('Project-View.php?id=' . $project_id);
}

// Handle project resubmission
if (isset($_POST['resubmit_project'])) {
    $project_id = $_POST['project_id'] ?? null;
    $submission_url = $_POST['submission_url'] ?? null;
    
    if (!$project_id || !$submission_url) {
        setFlashMessage('Missing required fields', 'danger');
        redirect('Projects.php');
    }
    
    // Validate URL
    if (!filter_var($submission_url, FILTER_VALIDATE_URL)) {
        setFlashMessage('Invalid submission URL', 'danger');
        redirect('Project-View.php?id=' . $project_id);
    }
    
    // Resubmit project
    if ($project->submitProject($project_id, $_SESSION['user_id'], $submission_url)) {
        setFlashMessage('Project resubmitted successfully', 'success');
    } else {
        setFlashMessage('Failed to resubmit project', 'danger');
    }
    
    redirect('Project-View.php?id=' . $project_id);
}

// If no action matched, redirect to projects page
redirect('Projects.php'); 