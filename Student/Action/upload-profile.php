<?php 
session_start();
include "../../Utils/Util.php";
include "../../Utils/Validation.php";
if (isset($_SESSION['username']) &&
    isset($_SESSION['student_id'])) {
    
   if (isset($_FILES['profile_picture']['name'])) {
      include "../../Database.php";
      include "../../Models/Student.php";

       $db = new Database();
       $conn = $db->connect();
       $student = new Student($conn);
       $student_id = $_SESSION['student_id'];
       $old_pp = $student->getProfileImg($student_id);
       $username = $_SESSION['username'];

       $img_name = $_FILES['profile_picture']['name'];
       $tmp_name = $_FILES['profile_picture']['tmp_name'];
       $error = $_FILES['profile_picture']['error'];
       $file_size = $_FILES['profile_picture']['size'];
       $file_type = $_FILES['profile_picture']['type'];

       // Maximum file size (2MB)
       $max_size = 2 * 1024 * 1024;

       if($error === 0){
            // Validate file size
            if ($file_size > $max_size) {
                throw new Exception("File size exceeds 2MB limit");
            }

            // Validate MIME type
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, JPEG, and PNG files are allowed");
            }

            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_to_lc = strtolower($img_ex);

            $allowed_exs = array('jpg', 'jpeg', 'png');
            if(in_array($img_ex_to_lc, $allowed_exs)){
               // Generate secure filename
               $new_img_name = bin2hex(random_bytes(16)) . '.' . $img_ex_to_lc;
               $img_upload_path = '../../Upload/profile/' . $new_img_name;

               // Create directory if it doesn't exist
               if (!file_exists('../../Upload/profile/')) {
                   mkdir('../../Upload/profile/', 0755, true);
               }

               // Delete old profile pic
               $old_pp_des = '../../Upload/profile/' . $old_pp;
               if ($old_pp != "default.jpg" && file_exists($old_pp_des)) {
                   unlink($old_pp_des);
               }

               // Move uploaded file
               if (move_uploaded_file($tmp_name, $img_upload_path)) {
                   // Set proper permissions
                   chmod($img_upload_path, 0644);
                   
                   // Update database
                   if ($student->updateProfile($student_id, $new_img_name)) {
                       $_SESSION['profile_img'] = $new_img_name;
                       Util::redirect("../Profile-Edit.php", "success", "Profile picture updated successfully");
                   } else {
                       // If database update fails, delete the uploaded file
                       unlink($img_upload_path);
                       throw new Exception("Failed to update profile in database");
                   }
               } else {
                   throw new Exception("Failed to upload file");
               }
            } else {
               throw new Exception("Invalid file type. Only JPG, JPEG, and PNG files are allowed");
            }
         } else {
            throw new Exception("File upload error: " . $error);
         }
   } else { 
      throw new Exception("No file uploaded");
   } 
} else { 
   Util::redirect("../../login.php", "error", "Please login first");
} 