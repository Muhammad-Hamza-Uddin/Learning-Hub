<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ─────────────────────────────────────────────────────────────
// Watched video IDs for this user (most recent first)
// ─────────────────────────────────────────────────────────────
$watchedResult = $conn->query(
    "SELECT video_id, watched_at
     FROM watch_history
     WHERE user_id='$user_id'
     ORDER BY watched_at DESC"
);

// ─────────────────────────────────────────────────────────────
// Build a map: video_id => course info   (from DB, not hardcoded)
// This way EVERY course that exists in the courses table shows up
// ─────────────────────────────────────────────────────────────
$courseMap = [];
$coursesResult = $conn->query("SELECT id, title, video_id FROM courses");
if($coursesResult && $coursesResult->num_rows > 0){
    while($c = $coursesResult->fetch_assoc()){
        $vid = $c['video_id'];
        $courseMap[$vid] = [
            'id'    => $c['id'],
            'title' => $c['title'],
            'thumb' => "https://img.youtube.com/vi/{$vid}/maxresdefault.jpg",
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Watch History – Learning Hub</title>
<style>
  *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f0f4fb;
    min-height: 100vh;
  }

  /* ── TOP BAR ── */
  .topbar {
    background: linear-gradient(135deg, #0C2554, #1E56AD);
    color: #fff;
    padding: 18px 36px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 18px rgba(0,0,0,0.18);
  }
  .topbar h1 { font-size: 1.35rem; font-weight: 700; letter-spacing: .03em; }
  .topbar h1 span { color: #F0C060; }
  .back-btn {
    background: rgba(255,255,255,0.15);
    color: #fff;
    text-decoration: none;
    padding: 8px 20px;
    border-radius: 6px;
    font-size: .9rem;
    transition: background .2s;
  }
  .back-btn:hover { background: rgba(255,255,255,0.28); }

  /* ── PAGE CONTENT ── */
  .page { max-width: 1200px; margin: 36px auto; padding: 0 24px; }

  .page-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #0C2554;
    margin-bottom: 6px;
  }
  .page-sub {
    color: #607090;
    font-size: .95rem;
    margin-bottom: 28px;
  }

  /* ── EMPTY STATE ── */
  .empty {
    text-align: center;
    padding: 80px 20px;
    color: #607090;
  }
  .empty .icon { font-size: 4rem; margin-bottom: 16px; }
  .empty h3 { font-size: 1.3rem; margin-bottom: 8px; color: #334; }
  .empty a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 28px;
    background: #1E56AD;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
  }

  /* ── GRID ── */
  .history-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
    gap: 24px;
  }

  /* ── CARD ── */
  .card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(12,37,84,0.10);
    transition: transform .22s, box-shadow .22s;
    position: relative;
  }
  .card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 36px rgba(12,37,84,0.18);
  }

  /* thumbnail */
  .thumb-wrap {
    position: relative;
    width: 100%;
    padding-top: 56.25%; /* 16:9 */
    overflow: hidden;
    background: #0C2554;
  }
  .thumb-wrap img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .3s;
  }
  .card:hover .thumb-wrap img { transform: scale(1.05); }

  /* play overlay */
  .play-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(7,19,48,0.38);
    opacity: 0;
    transition: opacity .22s;
  }
  .card:hover .play-overlay { opacity: 1; }
  .play-circle {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: rgba(240,192,96,0.92);
    display: flex; align-items: center; justify-content: center;
  }
  .play-circle svg { width:22px; height:22px; fill:#0C2554; margin-left:3px; }

  /* badge */
  .badge {
    position: absolute;
    top: 10px; left: 10px;
    background: #1E56AD;
    color: #fff;
    font-size: .72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    letter-spacing: .04em;
  }

  /* card body */
  .card-body { padding: 16px 18px 18px; }
  .card-body h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #0C2554;
    margin-bottom: 6px;
    line-height: 1.4;
    /* clamp to 2 lines */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .watched-date {
    font-size: .78rem;
    color: #8090a8;
    margin-bottom: 14px;
  }

  .watch-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    background: linear-gradient(135deg,#1E56AD,#3A86D4);
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-size: .88rem;
    font-weight: 600;
    transition: opacity .2s, transform .15s;
  }
  .watch-btn:hover { opacity: .88; transform: translateY(-1px); }
  .watch-btn svg { width:15px; height:15px; fill:#fff; }
</style>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
  <h1>Learning <span>Hub</span></h1>
  <a href="courses.php" class="back-btn">← Back to Courses</a>
</div>

<div class="page">

  <h2 class="page-title">📺 Your Watch History</h2>
  <p class="page-sub">Videos you have watched — pick up right where you left off.</p>

  <?php if($watchedResult && $watchedResult->num_rows > 0): ?>

    <div class="history-grid">
    <?php
      $shown = []; // prevent duplicate cards
      while($row = $watchedResult->fetch_assoc()):
        $vid  = $row['video_id'];
        $date = date('d M Y, h:i A', strtotime($row['watched_at']));

        // Skip if no matching course in DB or already shown
        if(!isset($courseMap[$vid]) || in_array($vid, $shown)) continue;
        $shown[] = $vid;
        $course  = $courseMap[$vid];
    ?>
      <div class="card">
        <div class="thumb-wrap">
          <img src="<?php echo $course['thumb']; ?>"
               alt="<?php echo htmlspecialchars($course['title']); ?>"
               onerror="this.src='https://img.youtube.com/vi/<?php echo $vid; ?>/hqdefault.jpg'">
          <div class="play-overlay">
            <div class="play-circle">
              <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </div>
          </div>
          <span class="badge">WATCHED</span>
        </div>
        <div class="card-body">
          <h4><?php echo htmlspecialchars($course['title']); ?></h4>
          <p class="watched-date">🕐 <?php echo $date; ?></p>
          <a class="watch-btn" href="courses.php?play=<?php echo urlencode($vid); ?>">
            <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            Watch Again
          </a>
        </div>
      </div>
    <?php endwhile; ?>
    </div>

  <?php else: ?>
    <div class="empty">
      <div class="icon">🎬</div>
      <h3>No watch history yet</h3>
      <p>Start watching courses and they will appear here automatically.</p>
      <a href="courses.php">Browse Courses →</a>
    </div>
  <?php endif; ?>

</div><!-- .page -->

</body>
</html>