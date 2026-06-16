<?php
session_start();
include "config.php";

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit();
}

$course_id = (int)($_GET['course_id'] ?? 0);
if($course_id <= 0){
    echo json_encode(['success'=>false,'error'=>'Invalid course ID']);
    exit();
}

$result = $conn->query("SELECT id, question, option_a, option_b, option_c, option_d, correct FROM course_quizzes WHERE course_id=$course_id ORDER BY id ASC");

$questions = [];
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $questions[] = $row;
    }
}

echo json_encode(['success'=>true,'questions'=>$questions]);
?>
