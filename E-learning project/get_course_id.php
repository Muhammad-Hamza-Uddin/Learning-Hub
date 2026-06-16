<?php
session_start();
include "config.php";
header('Content-Type: application/json');

$video_id = $conn->real_escape_string($_GET['video_id'] ?? '');
if(empty($video_id)){
    echo json_encode(['course_id'=>null]);
    exit();
}

$result = $conn->query("SELECT id FROM courses WHERE video_id='$video_id' LIMIT 1");
if($result && $result->num_rows > 0){
    $row = $result->fetch_assoc();
    echo json_encode(['course_id'=>(int)$row['id']]);
} else {
    echo json_encode(['course_id'=>null]);
}
?>
