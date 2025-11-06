<?php 
include "../Models/Student.php";
include "../Database.php";

$db = new Database();
$db_conn = $db->getConnection();
$student_obj = new Student($db_conn);