<?php
session_start();
include "config.php";

// Only admin can save quizzes
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit();
}

// Auto-create course_quizzes table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS `course_quizzes` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `course_id`    int(11)      NOT NULL,
  `question`     text         NOT NULL,
  `option_a`     varchar(500) NOT NULL,
  `option_b`     varchar(500) NOT NULL,
  `option_c`     varchar(500) NOT NULL,
  `option_d`     varchar(500) NOT NULL,
  `correct`      char(1)      NOT NULL COMMENT 'A, B, C, or D',
  `created_at`   timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$course_id = (int)($_POST['course_id'] ?? 0);
if($course_id <= 0){
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'error'=>'Invalid course ID']);
    exit();
}

// Delete old questions for this course (replace mode)
$conn->query("DELETE FROM course_quizzes WHERE course_id=$course_id");

$questions = $_POST['questions'] ?? [];
$saved = 0;

foreach($questions as $q){
    $question  = trim($q['question']  ?? '');
    $option_a  = trim($q['option_a']  ?? '');
    $option_b  = trim($q['option_b']  ?? '');
    $option_c  = trim($q['option_c']  ?? '');
    $option_d  = trim($q['option_d']  ?? '');
    $correct   = strtoupper(trim($q['correct'] ?? 'A'));

    // Skip empty questions
    if(empty($question) || empty($option_a) || empty($option_b)) continue;

    $stmt = $conn->prepare("INSERT INTO course_quizzes (course_id, question, option_a, option_b, option_c, option_d, correct) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("issssss", $course_id, $question, $option_a, $option_b, $option_c, $option_d, $correct);
    if($stmt->execute()) $saved++;
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode(['success'=>true,'saved'=>$saved]);
?>
