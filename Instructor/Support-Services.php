<?php
session_start();
if (!isset($_SESSION['instructor_id'])) {
    header("Location: ../index.php");
    exit();
}

$instructor_id = $_SESSION['instructor_id'];
$title = "Support Services";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?> - Instructor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'inc/NavBar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">Support Services</h1>
                </div>

                <!-- Support Services Grid -->
                <div class="row">
                    <!-- Technical Support -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-laptop-code me-2"></i>Technical Support
                                </h5>
                                <p class="card-text">Get help with technical issues, platform navigation, or system-related problems.</p>
                                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#technicalSupportModal">
                                    <i class="fa fa-headset me-2"></i>Get Help
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Support -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-book me-2"></i>Academic Support
                                </h5>
                                <p class="card-text">Get assistance with course content, assignments, or learning materials.</p>
                                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#academicSupportModal">
                                    <i class="fa fa-graduation-cap me-2"></i>Request Help
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Counseling Services -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-heart me-2"></i>Counseling Services
                                </h5>
                                <p class="card-text">Access counseling services for academic and personal support.</p>
                                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#counselingModal">
                                    <i class="fa fa-user-friends me-2"></i>Schedule Session
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Section -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-question-circle me-2"></i>FAQs
                                </h5>
                                <p class="card-text">Find answers to frequently asked questions about the platform.</p>
                                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#faqModal">
                                    <i class="fa fa-search me-2"></i>View FAQs
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-comment-alt me-2"></i>Feedback
                                </h5>
                                <p class="card-text">Share your feedback to help us improve the platform.</p>
                                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                                    <i class="fa fa-paper-plane me-2"></i>Submit Feedback
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-phone-alt me-2"></i>Emergency Contact
                                </h5>
                                <p class="card-text">Get immediate assistance for urgent matters.</p>
                                <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#emergencyModal">
                                    <i class="fa fa-exclamation-circle me-2"></i>Contact Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Support Modals -->
    <!-- Technical Support Modal -->
    <div class="modal fade" id="technicalSupportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Technical Support</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="technicalSupportForm">
                        <div class="mb-3">
                            <label class="form-label">Issue Type</label>
                            <select class="form-select" required>
                                <option value="">Select Issue Type</option>
                                <option value="platform">Platform Access</option>
                                <option value="course">Course Content</option>
                                <option value="assignment">Assignment Submission</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Support Modal -->
    <div class="modal fade" id="academicSupportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Academic Support</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="academicSupportForm">
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <select class="form-select" required>
                                <option value="">Select Course</option>
                                <!-- Course options will be populated dynamically -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Support Type</label>
                            <select class="form-select" required>
                                <option value="">Select Support Type</option>
                                <option value="content">Course Content</option>
                                <option value="assignment">Assignment Help</option>
                                <option value="exam">Exam Preparation</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Counseling Modal -->
    <div class="modal fade" id="counselingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Counseling Session</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="counselingForm">
                        <div class="mb-3">
                            <label class="form-label">Session Type</label>
                            <select class="form-select" required>
                                <option value="">Select Session Type</option>
                                <option value="academic">Academic Counseling</option>
                                <option value="career">Career Guidance</option>
                                <option value="personal">Personal Counseling</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preferred Time</label>
                            <input type="time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Schedule Session</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Modal -->
    <div class="modal fade" id="faqModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Frequently Asked Questions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="accordion" id="faqAccordion">
                        <!-- FAQ items will be populated dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Feedback</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="feedbackForm">
                        <div class="mb-3">
                            <label class="form-label">Feedback Type</label>
                            <select class="form-select" required>
                                <option value="">Select Feedback Type</option>
                                <option value="platform">Platform Experience</option>
                                <option value="course">Course Content</option>
                                <option value="support">Support Services</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="rating">
                                <!-- Star rating will be implemented with JavaScript -->
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Contact Modal -->
    <div class="modal fade" id="emergencyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Emergency Contact</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        For immediate assistance, please contact:
                    </div>
                    <div class="list-group">
                        <a href="tel:+1234567890" class="list-group-item list-group-item-action bg-dark text-white">
                            <i class="fa fa-phone me-2"></i>Emergency Hotline: (123) 456-7890
                        </a>
                        <a href="mailto:emergency@edupulse.com" class="list-group-item list-group-item-action bg-dark text-white">
                            <i class="fa fa-envelope me-2"></i>Emergency Email: emergency@edupulse.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html> 