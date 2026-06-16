<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id']) || !isset($_GET['video'])){
    http_response_code(400);
    exit();
}

$user_id  = (int)$_SESSION['user_id'];
$video_id = $conn->real_escape_string(trim($_GET['video']));

if(empty($video_id)){
    http_response_code(400);
    exit();
}

// INSERT or UPDATE timestamp — so every watch is recorded
// and the video always appears at the top of history
$stmt = $conn->prepare(
    "INSERT INTO watch_history (user_id, video_id, watched_at)
     VALUES (?, ?, NOW())
     ON DUPLICATE KEY UPDATE watched_at = NOW()"
);
$stmt->bind_param("is", $user_id, $video_id);
$stmt->execute();
$stmt->close();
$conn->close();

echo "ok";
?>