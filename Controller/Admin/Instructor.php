<?php 
include "../Models/Instructor.php";
include_once "../Models/Certificate.php";
include "../Models/Course.php";

include "../Database.php";


function getSomeInstructors($offset, $num){

	$db = new Database();
      $db_conn = $db->getConnection();
	$student_models = new Instructor($db_conn);

	$data = $student_models->getSome($offset, $num);
	
	return $data;
}

function getInstructorCount(){
	$db = new Database();
      $db_conn = $db->getConnection();
	$student_models = new Instructor($db_conn);
	$res = $student_models->count();
	return $res;
}

function getInstructorById($instructor_id){
	$db = new Database();
    $db_conn = $db->getConnection();
	$student = new Instructor($db_conn);
	$student->init($instructor_id);
	return $student->getData();
}

function getInstructorCourseById($instructor_id){
	$db = new Database();
    $db_conn = $db->getConnection();
    $course_model = new Course($db_conn);
	$courses = $course_model->getByInstructorId($instructor_id);
	return $courses;
}

function getCount(){
	$db = new Database();
    $db_conn = $db->getConnection();
	$instructor_model = new Instructor($db_conn);
	$res = $instructor_model->count();
	return $res;
}