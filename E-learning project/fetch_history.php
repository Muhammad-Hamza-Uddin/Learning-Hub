<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    echo "<p style='color:#aaa;padding:1rem'>Please login first.</p>";
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// ── Get ALL watched videos joined with courses table ──
// This works for EVERY video in DB, not just hardcoded ones
$sql = "SELECT wh.video_id, wh.watched_at,
               COALESCE(c.title, wh.video_id) AS title
        FROM watch_history wh
        LEFT JOIN courses c ON c.video_id = wh.video_id
        WHERE wh.user_id = $user_id
        ORDER BY wh.watched_at DESC";

$result = $conn->query($sql);

if(!$result || $result->num_rows === 0){
    echo "<p style='color:#aaa;padding:1rem;text-align:center;'>No videos watched yet. Start watching to build your history!</p>";
    exit();
}

$shown = []; // avoid duplicates

while($row = $result->fetch_assoc()){
    $vid   = htmlspecialchars($row['video_id']);
    $title = htmlspecialchars($row['title']);
    $date  = date('d M Y', strtotime($row['watched_at']));
    $thumb = "https://img.youtube.com/vi/{$row['video_id']}/maxresdefault.jpg";

    if(in_array($vid, $shown)) continue;
    $shown[] = $vid;

    echo "
    <div class='history-card' onclick=\"openHistoryVideo('{$row['video_id']}', '$title')\">
      <div class='hc-thumb'>
        <img src='$thumb'
             onerror=\"this.src='https://img.youtube.com/vi/{$row['video_id']}/hqdefault.jpg'\">
        <div class='hc-play'>▶</div>
      </div>
      <div class='hc-info'>
        <p class='hc-title'>$title</p>
        <span class='hc-date'>$date</span>
      </div>
    </div>";
}

$conn->close();
?>