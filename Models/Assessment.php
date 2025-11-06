<?php
class Assessment {
    private $table_name;
    private $conn;

    function __construct($db_conn) {
        $this->conn = $db_conn;
        $this->table_name = "assessments";
    }

    function getStudentAssessments($student_id, $offset = 0, $limit = 10) {
        try {
            $sql = 'SELECT a.*, 
                    (SELECT score FROM assessment_results WHERE assessment_id = a.assessment_id AND student_id = ?) as score
                    FROM '. $this->table_name.' a
                    WHERE a.status = "active" AND a.due_date >= CURDATE()
                    ORDER BY a.due_date ASC
                    LIMIT :offset, :limit';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute([$student_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Assessment getStudentAssessments error: " . $e->getMessage());
            throw new Exception("Failed to retrieve student assessments: " . $e->getMessage());
        }
    }

    function getStudentAssessmentCount($student_id) {
        try {
            $sql = 'SELECT COUNT(*) as count 
                    FROM '. $this->table_name.' 
                    WHERE status = "active" AND due_date >= CURDATE()';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'];
        } catch(PDOException $e) {
            error_log("Assessment getStudentAssessmentCount error: " . $e->getMessage());
            throw new Exception("Failed to count student assessments: " . $e->getMessage());
        }
    }

    function getById($id) {
        try {
            $sql = 'SELECT * FROM '. $this->table_name.' WHERE assessment_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            if (!$result) {
                throw new Exception("Assessment not found");
            }
            return $result;
        } catch(PDOException $e) {
            error_log("Assessment getById error: " . $e->getMessage());
            throw new Exception("Failed to retrieve assessment: " . $e->getMessage());
        }
    }

    function getQuestions($assessment_id) {
        try {
            $sql = 'SELECT * FROM assessment_questions 
                    WHERE assessment_id = ? 
                    ORDER BY question_order';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$assessment_id]);
            $questions = $stmt->fetchAll();
            if (empty($questions)) {
                throw new Exception("No questions found for this assessment");
            }
            return $questions;
        } catch(PDOException $e) {
            error_log("Assessment getQuestions error: " . $e->getMessage());
            throw new Exception("Failed to retrieve assessment questions: " . $e->getMessage());
        }
    }

    function submitStudentAnswers($student_id, $assessment_id, $answers) {
        try {
            $this->conn->beginTransaction();

            // Get correct answers
            $sql = 'SELECT question_id, correct_answer FROM assessment_questions 
                    WHERE assessment_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$assessment_id]);
            $correct_answers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            if (empty($correct_answers)) {
                throw new Exception("No questions found for this assessment");
            }

            // Calculate score
            $total_questions = count($correct_answers);
            $correct_count = 0;

            foreach ($answers as $question_id => $answer) {
                if (isset($correct_answers[$question_id]) && $correct_answers[$question_id] === $answer) {
                    $correct_count++;
                }
            }

            $score = ($correct_count / $total_questions) * 100;

            // Save result
            $sql = 'INSERT INTO assessment_results (student_id, assessment_id, score, submitted_at) 
                    VALUES (?, ?, ?, NOW())';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$student_id, $assessment_id, $score]);

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Assessment submitStudentAnswers error: " . $e->getMessage());
            throw new Exception("Failed to submit assessment answers: " . $e->getMessage());
        }
    }

    function getStudentResult($student_id, $assessment_id) {
        try {
            $sql = 'SELECT ar.*, a.title, a.description, a.duration 
                    FROM assessment_results ar 
                    JOIN '. $this->table_name.' a ON ar.assessment_id = a.assessment_id 
                    WHERE ar.student_id = ? AND ar.assessment_id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$student_id, $assessment_id]);
            $result = $stmt->fetch();
            if (!$result) {
                throw new Exception("No result found for this assessment");
            }
            return $result;
        } catch(PDOException $e) {
            error_log("Assessment getStudentResult error: " . $e->getMessage());
            throw new Exception("Failed to retrieve student result: " . $e->getMessage());
        }
    }
}
?> 