<?php 
include "../Models/Instructor.php";
//include "../Models/Course.php";

include "../Database.php";



function getById($instructor_id){
	$db = new Database();
    $db_conn = $db->getConnection();
	$instructor = new Instructor($db_conn);
	$instructor->init($instructor_id);
	return $instructor->getData();
}