<?php
session_start();
include "../../Utils/Util.php";
include "../../Controller/Admin/LearningModule.php";

if (isset($_POST['path_id']) && isset($_POST['title']) && isset($_POST['description']) && isset($_POST['duration'])) {
    
    $data = array(
        'path_id' => $_POST['path_id'],
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'duration' => $_POST['duration']
    );
    
    $result = addModule($data);
    
    if ($result) {
        $em = "Module added successfully";
        Util::redirect("../Learning-Path-Edit.php?id=" . $_POST['path_id'], "success", $em);
    } else {
        $em = "Error adding module";
        Util::redirect("../Learning-Path-Edit.php?id=" . $_POST['path_id'], "error", $em);
    }
} else {
    $em = "All fields are required";
    Util::redirect("../Learning-Paths.php", "error", $em);
}
?> 