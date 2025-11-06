<?php
require_once '../../includes/common.php';
requireRole(['student']);

header('Content-Type: application/json');

try {
    // Validate input
    if (empty($_POST['attempt_id'])) {
        throw new Exception('Attempt ID is required');
    }

    $attempt_id = $_POST['attempt_id'];
    $action = $_POST['action'] ?? 'save';

    // Verify attempt belongs to student
    $sql = "SELECT * FROM quiz_attempt WHERE attempt_id = ? AND student_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$attempt_id, $_SESSION['user_id']]);
    $attempt = $stmt->fetch();

    if (!$attempt) {
        throw new Exception('Invalid attempt');
    }

    if ($attempt['status'] === 'completed') {
        throw new Exception('This quiz has already been submitted');
    }

    // Start transaction
    $pdo->beginTransaction();

    if ($action === 'submit') {
        // Get quiz details
        $sql = "SELECT * FROM quiz WHERE quiz_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$attempt['quiz_id']]);
        $quiz = $stmt->fetch();

        // Get questions and correct answers
        $sql = "SELECT q.*, o.option_id, o.is_correct 
                FROM question q 
                LEFT JOIN question_option o ON q.question_id = o.question_id 
                WHERE q.quiz_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$attempt['quiz_id']]);
        $questions = $stmt->fetchAll();

        $total_points = 0;
        $earned_points = 0;

        // Process each answer
        foreach ($_POST['answers'] as $question_id => $answer) {
            $question = array_filter($questions, function($q) use ($question_id) {
                return $q['question_id'] == $question_id;
            });
            $question = reset($question);

            $is_correct = false;
            $points_earned = 0;

            if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'true_false') {
                // Check if selected option is correct
                $sql = "SELECT is_correct FROM question_option WHERE option_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$answer['selected_option_id']]);
                $option = $stmt->fetch();
                $is_correct = $option['is_correct'] ?? false;
            } else if ($question['question_type'] === 'short_answer') {
                // For short answer, mark as correct if answer is not empty
                $is_correct = !empty($answer['text_answer']);
            }

            $points_earned = $is_correct ? $question['points'] : 0;
            $total_points += $question['points'];
            $earned_points += $points_earned;

            // Save answer
            $sql = "INSERT INTO quiz_answer (
                attempt_id, question_id, selected_option_id,
                text_answer, is_correct, points_earned
            ) VALUES (
                :attempt_id, :question_id, :selected_option_id,
                :text_answer, :is_correct, :points_earned
            )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'attempt_id' => $attempt_id,
                'question_id' => $question_id,
                'selected_option_id' => $answer['selected_option_id'] ?? null,
                'text_answer' => $answer['text_answer'] ?? null,
                'is_correct' => $is_correct,
                'points_earned' => $points_earned
            ]);
        }

        // Calculate score
        $score = $total_points > 0 ? ($earned_points / $total_points) * 100 : 0;

        // Update attempt
        $sql = "UPDATE quiz_attempt SET 
                status = 'completed',
                end_time = NOW(),
                score = :score
                WHERE attempt_id = :attempt_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'score' => $score,
            'attempt_id' => $attempt_id
        ]);

    } else {
        // Save progress
        foreach ($_POST['answers'] as $question_id => $answer) {
            // Check if answer already exists
            $sql = "SELECT * FROM quiz_answer 
                    WHERE attempt_id = ? AND question_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$attempt_id, $question_id]);
            $existing_answer = $stmt->fetch();

            if ($existing_answer) {
                // Update existing answer
                $sql = "UPDATE quiz_answer SET 
                        selected_option_id = :selected_option_id,
                        text_answer = :text_answer
                        WHERE answer_id = :answer_id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'selected_option_id' => $answer['selected_option_id'] ?? null,
                    'text_answer' => $answer['text_answer'] ?? null,
                    'answer_id' => $existing_answer['answer_id']
                ]);
            } else {
                // Insert new answer
                $sql = "INSERT INTO quiz_answer (
                    attempt_id, question_id, selected_option_id, text_answer
                ) VALUES (
                    :attempt_id, :question_id, :selected_option_id, :text_answer
                )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'attempt_id' => $attempt_id,
                    'question_id' => $question_id,
                    'selected_option_id' => $answer['selected_option_id'] ?? null,
                    'text_answer' => $answer['text_answer'] ?? null
                ]);
            }
        }
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => $action === 'submit' ? 'Quiz submitted successfully' : 'Progress saved successfully',
        'attempt_id' => $attempt_id
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 