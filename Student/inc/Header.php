<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$title?></title>
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css" >
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <style>
      .wrapper {
        display: flex;
        min-height: 100vh;
      }
      .sidebar {
        width: 250px;
        background-color: #16213e;
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
      .main-content {
        flex-grow: 1;
        margin-left: 250px;
        padding: 20px;
      }
      body {
        background-color: #1a1a2e;
        color: #e0e0e0;
      }
      .card {
        background-color: #0f3460;
        color: #e0e0e0;
        border: none;
        border-radius: 8px;
        margin-bottom: 20px;
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
      .table, .table td, .table th {
        color: #e0e0e0 !important;
        background-color: #22223a !important;
        border-color: #444 !important;
      }
      .table tr:nth-child(even) td {
        background-color: #23234a !important;
      }
    </style>
  </head>
  <body>