<?php 
define('APP_ROOT', __DIR__);
 include "Utils/Util.php";
 include "Utils/Validation.php";

 require "Controller/Student/Certificate.php";

 if (!isset($_GET['certificate_id'])) {
    
    Util::redirect("404.php", "error", "404");
 }
 $certificate_id = Validation::clean($_GET['certificate_id']);

  $certificate = getCertificateById($certificate_id);
  if ($certificate == 0) {
    
    Util::redirect("404.php", "error", "404");
 }
 
 $student = getStudent($certificate['student_id']);
 $course = getCourse($certificate['course_id']);
 ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css" >
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
     
        
    </style>
    <title>Certificate Design</title>
</head>
<body>

    <div class="certificate" id="certificate">
        <div class="header">Certificate of Achievement </div>
        <div class="content">
        	<img src="Upload/profile/<?=$student['profile_img']?>" alt="Profile Image" width="100" class="rounded-circle mb-3">
            <p>This is to certify that</p>
            <h2><?=$student['first_name']?> <?=$student['last_name']?></h2>
            <p>has successfully completed the course</p>
            <h3><?=$course['title']?></h3>
            <p>on this day, <?=$certificate['issue_date']?></p>
            <p>Certificate ID: #<?=$certificate['certificate_id']?></p>

        </div>
        <div class="signature">
            <p>Signature</p>
            <img src="Assets/img/Signature.jpeg" width="100">
        </div>
    </div>
    <div id="editor"></div>
    <div class="text-center mt-4">
    	<button class="btn btn-success" id="downloadBtn">Download Certificate (PDF)</button> &nbsp;&nbsp;
        <button class="btn btn-info" onclick="window.print()">Print Certificate</button> &nbsp;&nbsp; |
        <a href="index.php" class="btn btn-secondary">Back Home</a>
    </div>
    <script src="assets/js/jquery-3.5.1.min.js"></script>
    <script src="assets/js/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        document.getElementById('downloadBtn').addEventListener('click', function() {
            const certificateElement = document.getElementById('certificate');

            html2canvas(certificateElement).then(function(canvas) {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgWidth = 210; // A4 width in mm
                const pageHeight = 297; // A4 height in mm
                const imgHeight = canvas.height * imgWidth / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                pdf.save('Certificate.pdf');
            });
        });
    </script>
</body>
</html>
