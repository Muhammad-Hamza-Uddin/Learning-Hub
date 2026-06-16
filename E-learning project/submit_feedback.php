<?php
session_start();
include "config.php";

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit;
}

// Exact column names from your DB:
// id, user_id, message, created_at, name, email, course, role, rating, title, got_job, recommend

$user_id   = $_SESSION['user_id'] ?? null;
$name      = trim($_POST['name']         ?? '');
$email     = trim($_POST['email']        ?? '');
$course    = trim($_POST['course']       ?? '');
$role      = trim($_POST['role']         ?? '');
$rating    = intval($_POST['rating']     ?? 0);
$title     = trim($_POST['review_title'] ?? '');
$message   = trim($_POST['review_text']  ?? '');  // JS sends review_text → DB column: message
$got_job   = trim($_POST['got_job']      ?? '');
$recommend = trim($_POST['recommend']    ?? '');

// Fallbacks
if(empty($name))    $name    = 'Anonymous';
if(empty($message)) $message = trim($_POST['message'] ?? '');
if($rating < 1)     $rating  = 1;

$stmt = $conn->prepare(
    "INSERT INTO feedback (user_id, name, email, course, role, rating, title, message, got_job, recommend)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if(!$stmt){
    echo json_encode(['success'=>false,'error'=>'Prepare failed: '.$conn->error]);
    exit;
}

$stmt->bind_param("issssissss",
    $user_id, $name, $email, $course, $role,
    $rating, $title, $message, $got_job, $recommend
);

if($stmt->execute()){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Insert failed: '.$stmt->error]);
}

$stmt->close();
$conn->close();