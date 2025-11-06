<?php
session_start();
include "../../Utils/Util.php";
include "../../Controller/Teacher/LearningPath.php";

if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $module_id = $_POST['module_id'];
        $type = $_POST['type'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $duration = $_POST['duration'];
        $status = $_POST['status'];
        
        // Validate required fields
        if (empty($module_id) || empty($type) || empty($title) || 
            empty($description) || empty($duration) || empty($status)) {
            $em = "All required fields must be filled";
            Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
        }
        
        // Verify teacher has access to the module
        $module = getModuleDetails($module_id);
        if (!$module || $module['teacher_id'] != $_SESSION['teacher_id']) {
            $em = "Module not found or you don't have access";
            Util::redirect("../Learning-Paths.php", "error", $em);
        }
        
        // Handle resource type specific data
        $resource_data = [];
        
        if ($type == 'video') {
            $video_source = $_POST['video_source'];
            
            if ($video_source == 'upload') {
                if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] != 0) {
                    $em = "Please upload a video file";
                    Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
                }
                
                $video_file = $_FILES['video_file'];
                $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
                $max_size = 500 * 1024 * 1024; // 500MB
                
                if (!in_array($video_file['type'], $allowed_types)) {
                    $em = "Invalid video format. Supported formats: MP4, WebM, OGG";
                    Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
                }
                
                if ($video_file['size'] > $max_size) {
                    $em = "Video file size exceeds 500MB limit";
                    Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
                }
                
                $resource_data['video_source'] = 'upload';
                $resource_data['video_file'] = $video_file;
            } else {
                $video_url = $_POST['video_url'];
                if (empty($video_url)) {
                    $em = "Please enter a video URL";
                    Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
                }
                
                $resource_data['video_source'] = $video_source;
                $resource_data['video_url'] = $video_url;
            }
        } elseif ($type == 'document') {
            if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] != 0) {
                $em = "Please upload a document file";
                Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
            }
            
            $document_file = $_FILES['document_file'];
            $allowed_types = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];
            $max_size = 50 * 1024 * 1024; // 50MB
            
            if (!in_array($document_file['type'], $allowed_types)) {
                $em = "Invalid document format. Supported formats: PDF, DOC, DOCX, PPT, PPTX";
                Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
            }
            
            if ($document_file['size'] > $max_size) {
                $em = "Document file size exceeds 50MB limit";
                Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
            }
            
            $resource_data['document_file'] = $document_file;
        } elseif ($type == 'quiz') {
            $time_limit = $_POST['time_limit'];
            $passing_score = $_POST['passing_score'];
            $show_answers = isset($_POST['show_answers']) ? 1 : 0;
            
            if ($time_limit < 1 || $time_limit > 180) {
                $em = "Time limit must be between 1 and 180 minutes";
                Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
            }
            
            if ($passing_score < 0 || $passing_score > 100) {
                $em = "Passing score must be between 0 and 100";
                Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
            }
            
            $resource_data['time_limit'] = $time_limit;
            $resource_data['passing_score'] = $passing_score;
            $resource_data['show_answers'] = $show_answers;
        } elseif ($type == 'assignment') {
            $due_date = $_POST['due_date'];
            $max_points = $_POST['max_points'];
            $instructions = $_POST['instructions'];
            
            if (!empty($due_date)) {
                $due_timestamp = strtotime($due_date);
                if ($due_timestamp < time()) {
                    $em = "Due date must be in the future";
                    Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
                }
            }
            
            if ($max_points < 1 || $max_points > 100) {
                $em = "Maximum points must be between 1 and 100";
                Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
            }
            
            $resource_data['due_date'] = $due_date;
            $resource_data['max_points'] = $max_points;
            $resource_data['instructions'] = $instructions;
        }
        
        // Create resource
        $result = createResource(
            $module_id,
            $type,
            $title,
            $description,
            $duration,
            $status,
            $resource_data
        );
        
        if ($result) {
            $sm = "Resource created successfully";
            Util::redirect("../Manage-Resources.php?module_id=" . $module_id, "success", $sm);
        } else {
            $em = "Error creating resource";
            Util::redirect("../Add-Resource.php?module_id=" . $module_id . "&type=" . $type, "error", $em);
        }
    } else {
        $em = "Invalid request method";
        Util::redirect("../Learning-Paths.php", "error", $em);
    }
} else {
    $em = "First login ";
    Util::redirect("../../login.php", "error", $em);
}
?> 