<?php 
include "../Models/Student.php";
include_once "../Models/Certificate.php";
include "../Models/Course.php";

include "../Database.php";


function getSomeStudent($offset, $num){

	$db = new Database();
      $db_conn = $db->getConnection();
	$student_models = new Student($db_conn);

	$data = $student_models->getSome($offset, $num);
	
	return $data;
}

function getStudentCount(){
	$db = new Database();
      $db_conn = $db->getConnection();
	$student_models = new Student($db_conn);
	$res = $student_models->count();
	return $res;
}

function getStudentById($student_id){
	$db = new Database();
      $db_conn = $db->getConnection();
	$student = new Student($db_conn);
	$student->init($student_id);
	return $student->getData();
}

function getCertificate($student_id){

	$db = new Database();
    $db_conn = $db->getConnection();
	$certificate_model = new Certificate($db_conn);
	$certificates = $certificate_model->getAllByStudentId($student_id);
    
	$course_model = new Course($db_conn);
	$data = array();
    
	// Initialize with empty data
	$data[0] = array(
		'certificate_id' => "", 
		'course_title' => ""
	);

	if ($certificates && is_array($certificates)) {
		foreach ($certificates as $i => $certificate) {
			$c_id = $certificate['course_id'];
			$certif_id = $certificate['certificate_id'];
			$course = $course_model->getById($c_id);
			
			// Check if course data exists
			if ($course && isset($course['title'])) {
				$data[$i] = array(
					'certificate_id' => $certif_id, 
					'course_title' => $course['title']
				);
			}
		}
	}

	return $data;
}