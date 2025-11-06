<?php
function updateFile($file) {
    $content = file_get_contents($file);
    $content = str_replace('$db->connect()', '$db->getConnection()', $content);
    file_put_contents($file, $content);
    echo "Updated: $file\n";
}

$files = [
    'create_users.php',
    'Controller/Student.php',
    'Controller/Student/EnrolledStudent.php',
    'Controller/Student/LearningPath.php',
    'Controller/Student/Student.php',
    'Controller/Student/Course.php',
    'Controller/Student/Certificate.php',
    'Controller/Instructor/Course.php',
    'Controller/Instructor/Instructor.php',
    'Controller/Instructor/CoursesMaterial.php',
    'Controller/Admin/Course.php',
    'Controller/Admin/system.php',
    'Controller/Admin/Student.php',
    'Controller/Admin/LearningModule.php',
    'Controller/Admin/Instructor.php',
    'Admin/Profile.php',
    'Admin/index.php',
    'Admin/Action/Program-Action.php',
    'Admin/Action/reset-password.php',
    'Admin/Action/course-delete.php',
    'Admin/Action/course-add.php',
    'Admin/Action/instructor-add.php',
    'Admin/Action/active-student.php',
    'Admin/Action/active-Instructor.php',
    'Admin/Action/active-course.php',
    'Action/login.php',
    'Action/signup.php',
    'Instructor/Action/active-course.php',
    'Instructor/Action/course-topic-add.php',
    'Instructor/Action/load-chapters.php',
    'Instructor/Action/create-content.php',
    'Instructor/Action/load-content.php',
    'Instructor/Action/Project-Action.php',
    'Instructor/Action/load-topics.php',
    'Instructor/Action/course-delete.php',
    'Instructor/Action/upload-profile-details.php',
    'Instructor/Action/upload-profile.php',
    'Instructor/Action/upload-materials.php',
    'Instructor/Action/course-chapter-add.php',
    'Instructor/Action/content-update.php',
    'Instructor/Action/course-add.php',
    'Instructor/Action/change-password.php',
    'Student/Action/generateCertificate.php',
    'Student/Action/upload-profile-details.php',
    'Student/Projects.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        updateFile($file);
    }
}

echo "Update complete!\n";
?> 