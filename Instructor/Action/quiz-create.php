<?php
require_once '../../includes/common.php';
requireRole(['admin', 'instructor']);

header('Content-Type: application/json');

try {
    // Validate input
    $required_fields = ['course_id', 'title_en', 'title_am'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Start transaction
    $pdo->beginTransaction();

    // Insert quiz
    $sql = "INSERT INTO quiz (
        course_id, chapter_id, topic_id,
        title_en, title_am, title_ti, title_om,
        description_en, description_am, description_ti, description_om,
        passing_score, time_limit, status,
        created_by, created_by_role
    ) VALUES (
        :course_id, :chapter_id, :topic_id,
        :title_en, :title_am, :title_ti, :title_om,
        :description_en, :description_am, :description_ti, :description_om,
        :passing_score, :time_limit, :status,
        :created_by, :created_by_role
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'course_id' => $_POST['course_id'],
        'chapter_id' => $_POST['chapter_id'] ?? null,
        'topic_id' => $_POST['topic_id'] ?? null,
        'title_en' => $_POST['title_en'],
        'title_am' => $_POST['title_am'],
        'title_ti' => $_POST['title_ti'] ?? $_POST['title_en'],
        'title_om' => $_POST['title_om'] ?? $_POST['title_en'],
        'description_en' => $_POST['description_en'] ?? null,
        'description_am' => $_POST['description_am'] ?? null,
        'description_ti' => $_POST['description_ti'] ?? $_POST['description_en'],
        'description_om' => $_POST['description_om'] ?? $_POST['description_en'],
        'passing_score' => $_POST['passing_score'] ?? 70,
        'time_limit' => $_POST['time_limit'] ? $_POST['time_limit'] * 60 : null,
        'status' => $_POST['status'] ?? 'Public',
        'created_by' => $_SESSION['user_id'],
        'created_by_role' => $_SESSION['role']
    ]);

    $quiz_id = $pdo->lastInsertId();

    // Insert questions
    if (!empty($_POST['questions'])) {
        foreach ($_POST['questions'] as $index => $question) {
            // Insert question
            $sql = "INSERT INTO question (
                quiz_id, question_text_en, question_text_am,
                question_text_ti, question_text_om,
                question_type, points, `order`
            ) VALUES (
                :quiz_id, :question_text_en, :question_text_am,
                :question_text_ti, :question_text_om,
                :question_type, :points, :order
            )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'quiz_id' => $quiz_id,
                'question_text_en' => $question['question_text_en'],
                'question_text_am' => $question['question_text_am'],
                'question_text_ti' => $question['question_text_ti'] ?? $question['question_text_en'],
                'question_text_om' => $question['question_text_om'] ?? $question['question_text_en'],
                'question_type' => $question['question_type'],
                'points' => $question['points'] ?? 1,
                'order' => $index
            ]);

            $question_id = $pdo->lastInsertId();

            // Insert options for multiple choice and true/false questions
            if (in_array($question['question_type'], ['multiple_choice', 'true_false']) && !empty($question['options'])) {
                foreach ($question['options'] as $option_index => $option) {
                    $sql = "INSERT INTO question_option (
                        question_id, option_text_en, option_text_am,
                        option_text_ti, option_text_om,
                        is_correct, `order`
                    ) VALUES (
                        :question_id, :option_text_en, :option_text_am,
                        :option_text_ti, :option_text_om,
                        :is_correct, :order
                    )";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'question_id' => $question_id,
                        'option_text_en' => $option['option_text_en'],
                        'option_text_am' => $option['option_text_am'],
                        'option_text_ti' => $option['option_text_ti'] ?? $option['option_text_en'],
                        'option_text_om' => $option['option_text_om'] ?? $option['option_text_en'],
                        'is_correct' => $option_index == $question['correct_option'] ? 1 : 0,
                        'order' => $option_index
                    ]);
                }
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Quiz created successfully']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 