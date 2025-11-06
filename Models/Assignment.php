<?php
class Assignment {
    private $table_name;
    private $conn;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
        $this->table_name = "assignments";
    }

    public function getStudentAssignments($student_id, $offset = 0, $limit = 10) {
        try {
            // This query fetches assignments and checks if the student has submitted them
            $sql = "SELECT a.*, 
                           (SELECT status FROM assignment_submissions WHERE assignment_id = a.assignment_id AND student_id = :student_id LIMIT 1) as status
                    FROM " . $this->table_name . " a
                    WHERE a.status = 'active' -- Assuming assignments have a status
                    ORDER BY a.due_date ASC
                    LIMIT :offset, :limit";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Assignment getStudentAssignments error: " . $e->getMessage());
            return [];
        }
    }

    public function getStudentAssignmentCount($student_id) {
        try {
            $sql = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'active'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'];
        } catch(PDOException $e) {
            error_log("Assignment getStudentAssignmentCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function getById($assignment_id) {
        try {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE assignment_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$assignment_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Assignment getById error: " . $e->getMessage());
            return null;
        }
    }

    public function submitAssignment($student_id, $assignment_id, $submission_data) {
        try {
            // Check if a submission already exists for this assignment and student
            $check_sql = "SELECT submission_id FROM assignment_submissions WHERE student_id = ? AND assignment_id = ? LIMIT 1";
            $check_stmt = $this->conn->prepare($check_sql);
            $check_stmt->execute([$student_id, $assignment_id]);

            if ($check_stmt->rowCount() > 0) {
                // Update existing submission
                $sql = "UPDATE assignment_submissions SET submission_text = ?, submitted_at = NOW(), status = 'submitted' WHERE student_id = ? AND assignment_id = ?";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$submission_data['submission_text'], $student_id, $assignment_id]);
            } else {
                // Insert new submission
                $sql = "INSERT INTO assignment_submissions (student_id, assignment_id, submission_text, submitted_at, status) VALUES (?, ?, ?, NOW(), 'submitted')";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$student_id, $assignment_id, $submission_data['submission_text']]);
            }
        } catch(PDOException $e) {
            error_log("Assignment submitAssignment error: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentSubmission($student_id, $assignment_id) {
        try {
            $sql = "SELECT * FROM assignment_submissions WHERE student_id = ? AND assignment_id = ? LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$student_id, $assignment_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Assignment getStudentSubmission error: " . $e->getMessage());
            return null;
        }
    }
}
?> 