<?php
session_start();
require_once 'db_connect.php';
require_once 'auth_middleware.php'; // Ensure admin authentication

header('Content-Type: application/json');

// Placeholder for training needs analysis data
// In a real application, this would involve complex queries and aggregation
// based on questionnaire responses.

// For demonstration purposes, we'll return some dummy data.
$trainingNeedsData = [
    ['label' => 'Communication Skills', 'value' => 75],
    ['label' => 'Leadership Development', 'value' => 60],
    ['label' => 'Technical Skills', 'value' => 80],
    ['label' => 'Teamwork & Collaboration', 'value' => 70],
    ['label' => 'Problem Solving', 'value' => 65],
];

echo json_encode(['success' => true, 'data' => $trainingNeedsData]);

?>