<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . "/../../Controller/Admin/Reports.php";
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$title?></title>
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css" >
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/richtext.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">

    <script src="../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../assets/js/jquery.richtext.min.js"></script>
    <script src="../assets/js/chart.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a2e; /* Dark background */
            color: #e0e0e0; /* Light text color */
            margin: 0;
            padding: 0;
        }
        .container-fluid {
            padding: 0;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar styling */
        .sidebar {
            width: 250px;
            background-color: #16213e; /* Darker blue for sidebar */
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.2);
            flex-shrink: 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        .sidebar .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar .logo img {
            max-width: 80px;
        }
        .sidebar .logo h2 {
            color: #e0e0e0;
            font-size: 1.5rem;
            margin-top: 10px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 15px;
        }
        .sidebar ul li a {
            color: #e0e0e0;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #0f3460;
        }
        /* Main content area */
        .main-content {
            flex-grow: 1;
            margin-left: 250px; /* Offset for sidebar */
        }
        .navbar {
            background-color: #16213e;
            margin-bottom: 20px;
            border-bottom: 1px solid #0f3460;
        }
        .navbar-brand {
            color: #e0e0e0 !important;
        }
        .card {
            background-color: #0f3460; /* Dark blue for cards */
            color: #e0e0e0;
            border: none;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #0a2342;
            border-bottom: 1px solid #0f3460;
            color: #e0e0e0;
        }
        .card-body {
            padding: 20px;
        }
        .alert-info {
            background-color: #0f3460;
            color: #e0e0e0;
            border-color: #0a2342;
        }
        .table {
            color: #e0e0e0;
        }
        .table th,
        .table td {
            border-color: #0f3460;
        }
        .btn-primary {
            background-color: #e94560;
            border-color: #e94560;
        }
        .btn-primary:hover {
            background-color: #d1304d;
            border-color: #d1304d;
        }
        .btn-secondary {
            background-color: #555;
            border-color: #555;
        }
        .btn-secondary:hover {
            background-color: #666;
            border-color: #666;
        }
        /* Specific styling for the dashboard cards */
        .dashboard-card {
            padding: 15px;
            border-radius: 8px;
            color: white;
            text-align: center;
        }
        .dashboard-card h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        .dashboard-card p {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0;
        }
        .card-blue {
            background-color: #007bff; /* Bootstrap primary blue */
        }
        .card-green {
            background-color: #28a745; /* Bootstrap success green */
        }
        .card-yellow {
            background-color: #ffc107; /* Bootstrap warning yellow */
        }
        .card-red {
            background-color: #dc3545; /* Bootstrap danger red */
        }
    </style>
  </head>
  <body>