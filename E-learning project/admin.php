<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? 'Admin';

// ── Auto-create course_quizzes table ──
$conn->query("CREATE TABLE IF NOT EXISTS `course_quizzes` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `course_id`    int(11)      NOT NULL,
  `question`     text         NOT NULL,
  `option_a`     varchar(500) NOT NULL,
  `option_b`     varchar(500) NOT NULL,
  `option_c`     varchar(500) NOT NULL,
  `option_d`     varchar(500) NOT NULL,
  `correct`      char(1)      NOT NULL,
  `created_at`   timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Handle Video Upload ──
$upload_msg = '';
$new_course_id = 0;
if(isset($_POST['upload_video'])){
    $title       = $conn->real_escape_string($_POST['vid_title']);
    $video_id    = $conn->real_escape_string($_POST['vid_id']);
    $category    = $conn->real_escape_string($_POST['vid_cat']);
    $description = $conn->real_escape_string($_POST['vid_desc']);
    $level       = $conn->real_escape_string($_POST['vid_level']);
    $duration    = $conn->real_escape_string($_POST['vid_duration']);
    $instructor  = $conn->real_escape_string($_POST['vid_instructor']);

    $q = "INSERT INTO courses (title, video_id, category, description, level, duration, instructor)
          VALUES ('$title','$video_id','$category','$description','$level','$duration','$instructor')";
    if($conn->query($q)){
        $new_course_id = $conn->insert_id;
        $upload_msg = "success";

        // ── Save Quiz Questions ──
        $questions = $_POST['questions'] ?? [];
        foreach($questions as $qitem){
            $ques     = $conn->real_escape_string(trim($qitem['question']  ?? ''));
            $opt_a    = $conn->real_escape_string(trim($qitem['option_a']  ?? ''));
            $opt_b    = $conn->real_escape_string(trim($qitem['option_b']  ?? ''));
            $opt_c    = $conn->real_escape_string(trim($qitem['option_c']  ?? ''));
            $opt_d    = $conn->real_escape_string(trim($qitem['option_d']  ?? ''));
            $correct  = strtoupper(trim($qitem['correct'] ?? 'A'));
            if(empty($ques) || empty($opt_a) || empty($opt_b)) continue;
            $conn->query("INSERT INTO course_quizzes (course_id,question,option_a,option_b,option_c,option_d,correct)
                          VALUES ($new_course_id,'$ques','$opt_a','$opt_b','$opt_c','$opt_d','$correct')");
        }
    } else {
        $upload_msg = "error";
    }
}

// ── Handle Video Delete ──
if(isset($_GET['delete_video'])){
    $vid = (int)$_GET['delete_video'];
    $conn->query("DELETE FROM courses WHERE id=$vid");
    header("Location: admin.php?tab=videos");
    exit();
}

// ── Handle Video Edit ──
if(isset($_POST['edit_video'])){
    $id          = (int)$_POST['edit_id'];
    $title       = $conn->real_escape_string($_POST['edit_title']);
    $category    = $conn->real_escape_string($_POST['edit_cat']);
    $description = $conn->real_escape_string($_POST['edit_desc']);
    $level       = $conn->real_escape_string($_POST['edit_level']);
    $duration    = $conn->real_escape_string($_POST['edit_duration']);
    $instructor  = $conn->real_escape_string($_POST['edit_instructor']);
    $conn->query("UPDATE courses SET title='$title',category='$category',description='$description',level='$level',duration='$duration',instructor='$instructor' WHERE id=$id");

    // ── Save/Update Quiz Questions ──
    $conn->query("DELETE FROM course_quizzes WHERE course_id=$id");
    $edit_questions = $_POST['edit_questions'] ?? [];
    foreach($edit_questions as $qitem){
        $ques    = $conn->real_escape_string(trim($qitem['question']  ?? ''));
        $opt_a   = $conn->real_escape_string(trim($qitem['option_a']  ?? ''));
        $opt_b   = $conn->real_escape_string(trim($qitem['option_b']  ?? ''));
        $opt_c   = $conn->real_escape_string(trim($qitem['option_c']  ?? ''));
        $opt_d   = $conn->real_escape_string(trim($qitem['option_d']  ?? ''));
        $correct = strtoupper(trim($qitem['correct'] ?? 'A'));
        if(empty($ques) || empty($opt_a) || empty($opt_b)) continue;
        $conn->query("INSERT INTO course_quizzes (course_id,question,option_a,option_b,option_c,option_d,correct)
                      VALUES ($id,'$ques','$opt_a','$opt_b','$opt_c','$opt_d','$correct')");
    }

    header("Location: admin.php?tab=videos");
    exit();
}

// ── Stats ──
$total_users    = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'] ?? 0;
$total_videos   = $conn->query("SELECT COUNT(*) as c FROM courses")->fetch_assoc()['c'] ?? 0;
$total_watches  = $conn->query("SELECT COUNT(*) as c FROM watch_history")->fetch_assoc()['c'] ?? 0;
// Auto-create feedback table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS `feedback` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`    int(11)      DEFAULT NULL,
  `name`       varchar(150) NOT NULL,
  `email`      varchar(200) NOT NULL DEFAULT '',
  `course`     varchar(255) NOT NULL DEFAULT '',
  `role`       varchar(100) DEFAULT '',
  `rating`     tinyint(1)   NOT NULL DEFAULT 5,
  `title`      varchar(255) DEFAULT '',
  `message`    text         NOT NULL,
  `got_job`    varchar(100) DEFAULT '',
  `recommend`  varchar(100) DEFAULT '',
  `created_at` timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$total_feedback = $conn->query("SELECT COUNT(*) as c FROM feedback")->fetch_assoc()['c'] ?? 0;

// ── Active tab ──
$tab = $_GET['tab'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — LearnHub</title>
<link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

  :root {
    --teal:   #2D5F5D;
    --coral:  #E07A5F;
    --amber:  #F4A261;
    --dark:   #181818;
    --cream:  #FDF8F3;
    --white:  #FFFFFF;
    --muted:  #6B6B6B;
    --border: #EBEBEB;
    --sidebar-w: 260px;
  }

  body { font-family:'DM Sans',sans-serif; background:#F0F2F5; color:var(--dark); display:flex; min-height:100vh; }

  /* ── SIDEBAR ── */
  .sidebar {
  width: 250px;
  height: 100vh;          /* full screen height */
  overflow-y: auto;       /* scroll enable */
  position: fixed;
  top: 0;
  left: 0;
  background: var(--teal);
  }
  .sidebar-brand {
    padding: 2rem 1.5rem 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,.12);
     margin-bottom: 25px;
  }
  .sidebar-brand h2 {
    font-family:'Crimson Pro',serif;
    font-size:1.7rem; font-weight:700; color:white; letter-spacing:-.02em;
    margin-bottom: 30px;
  }
  .sidebar-brand p { font-size:.78rem; color:rgba(255,255,255,.6); margin-top:.2rem; }

  .sidebar-nav { flex:1; padding: 1rem 0; }
  .nav-label {
    font-size:.7rem; font-weight:700; letter-spacing:.12em;
    color:rgba(255,255,255,.4); text-transform:uppercase;
    padding: 1rem 1.5rem .4rem;
  }
  .nav-item {
    display: flex; align-items: center; gap: .85rem;
    padding: .8rem 1.5rem; color:rgba(255,255,255,.75);
    text-decoration:none; font-weight:500; font-size:.95rem;
    transition: all .25s; cursor:pointer; border:none; background:none; width:100%; text-align:left;
  }
  .nav-item:hover { background:rgba(255,255,255,.1); color:white; }
  .nav-item.active { background:rgba(255,255,255,.18); color:white; border-right:3px solid var(--amber); }
  .nav-item .icon { font-size:1.2rem; width:24px; text-align:center; }

  .sidebar-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(255,255,255,.12);
  }
  .admin-pill {
    display:flex; align-items:center; gap:.75rem; margin-bottom:1rem;
  }
  .admin-avatar {
    width:38px; height:38px; border-radius:50%;
    background:var(--amber); color:var(--dark);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.95rem; flex-shrink:0;
  }
  .admin-info p { font-size:.85rem; color:white; font-weight:600; }
  .admin-info span { font-size:.75rem; color:rgba(255,255,255,.5); }
  .logout-btn {
    display:block; width:100%; padding:.7rem;
    background:rgba(255,255,255,.1); color:rgba(255,255,255,.8);
    border:1px solid rgba(255,255,255,.15); border-radius:8px;
    text-align:center; text-decoration:none; font-size:.88rem;
    font-weight:600; transition:all .25s;
  }
  .logout-btn:hover { background:var(--coral); color:white; border-color:var(--coral); }

  /* ── MAIN ── */
  .main {
    margin-left: var(--sidebar-w);
    flex:1; padding: 2rem 2.5rem;
    min-height: 100vh;
  }

  .topbar {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom: 2rem;
  }
  .topbar h1 {
    font-family:'Crimson Pro',serif; font-size:2rem; font-weight:700; color:var(--dark);
  }
  .topbar p { color:var(--muted); font-size:.9rem; margin-top:.1rem; }
  .view-site {
    padding:.7rem 1.4rem; background:var(--teal); color:white;
    border-radius:50px; text-decoration:none; font-weight:600; font-size:.88rem;
    transition:all .25s;
  }
  .view-site:hover { background:var(--coral); transform:translateY(-2px); }

  /* ── STAT CARDS ── */
  .stats { display:grid; grid-template-columns:repeat(4,1fr); gap:1.5rem; margin-bottom:2.5rem; }
  .stat-card {
    background:var(--white); border-radius:16px; padding:1.5rem;
    box-shadow:0 4px 20px rgba(0,0,0,.06);
    display:flex; align-items:center; gap:1.2rem;
    transition:transform .3s;
  }
  .stat-card:hover { transform:translateY(-4px); }
  .stat-icon {
    width:54px; height:54px; border-radius:14px;
    display:flex; align-items:center; justify-content:center; font-size:1.6rem; flex-shrink:0;
  }
  .stat-icon.teal   { background:rgba(45,95,93,.1); }
  .stat-icon.coral  { background:rgba(224,122,95,.1); }
  .stat-icon.amber  { background:rgba(244,162,97,.1); }
  .stat-icon.purple { background:rgba(128,90,213,.1); }
  .stat-value { font-size:1.9rem; font-weight:700; color:var(--dark); line-height:1; }
  .stat-label { font-size:.82rem; color:var(--muted); margin-top:.2rem; }

  /* ── SECTION ── */
  .section { display:none; }
  .section.active { display:block; }

  .card {
    background:var(--white); border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,.06); overflow:hidden;
  }
  .card-header {
    padding:1.4rem 1.8rem; border-bottom:1px solid var(--border);
    display:flex; justify-content:space-between; align-items:center;
  }
  .card-header h3 { font-size:1.1rem; font-weight:700; }
  .card-body { padding:1.8rem; }

  /* ── TABLE ── */
  table { width:100%; border-collapse:collapse; }
  th {
    background:#F8F9FA; text-align:left; padding:.9rem 1rem;
    font-size:.8rem; font-weight:700; color:var(--muted);
    text-transform:uppercase; letter-spacing:.06em;
  }
  td { padding:.9rem 1rem; border-bottom:1px solid var(--border); font-size:.92rem; vertical-align:middle; }
  tr:last-child td { border-bottom:none; }
  tr:hover td { background:#FAFAFA; }

  /* ── BADGES ── */
  .badge {
    display:inline-block; padding:.3rem .75rem; border-radius:50px;
    font-size:.75rem; font-weight:700;
  }
  .badge-admin   { background:rgba(224,122,95,.15); color:#c0532a; }
  .badge-student { background:rgba(45,95,93,.12);   color:var(--teal); }
  .badge-beginner{ background:rgba(39,174,96,.12);  color:#27ae60; }
  .badge-inter   { background:rgba(244,162,97,.2);  color:#c07a10; }
  .badge-advanced{ background:rgba(224,122,95,.15); color:#c0532a; }

  .btn-sm {
    padding:.4rem .9rem; border-radius:8px; font-size:.8rem;
    font-weight:600; cursor:pointer; border:none; transition:all .2s;
  }
  .btn-edit   { background:#EBF5FB; color:#1a6fa8; }
  .btn-delete { background:#FDEDEC; color:#c0392b; }
  .btn-edit:hover   { background:#1a6fa8; color:white; }
  .btn-delete:hover { background:#c0392b; color:white; }

  /* ── UPLOAD FORM ── */
  .upload-form { display:grid; grid-template-columns:1fr 1fr; gap:1.2rem; }
  .form-group { display:flex; flex-direction:column; gap:.4rem; }
  .form-group.full { grid-column:1/-1; }
  label { font-size:.83rem; font-weight:600; color:var(--dark); }
  input[type="text"], input[type="url"], select, textarea {
    padding:.8rem 1rem; border:2px solid var(--border); border-radius:10px;
    font-family:'DM Sans',sans-serif; font-size:.92rem; transition:border-color .25s;
  }
  input:focus, select:focus, textarea:focus {
    outline:none; border-color:var(--teal); box-shadow:0 0 0 4px rgba(45,95,93,.08);
  }
  textarea { resize:vertical; min-height:80px; }
  .upload-btn {
    grid-column:1/-1; padding:.9rem; background:var(--teal); color:white;
    border:none; border-radius:50px; font-family:'DM Sans',sans-serif;
    font-size:1rem; font-weight:700; cursor:pointer; transition:all .3s;
  }
  .upload-btn:hover { background:var(--coral); transform:translateY(-2px); }

  .alert { padding:1rem 1.2rem; border-radius:10px; margin-bottom:1.5rem; font-size:.92rem; }
  .alert-success { background:#f0fff4; color:#27ae60; border-left:4px solid #27ae60; }
  .alert-error   { background:#fff0f0; color:#c0392b; border-left:4px solid #c0392b; }

  /* ── VIDEO GRID ── */
  .video-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:1.5rem; }
  .video-card {
    background:var(--white); border-radius:14px; overflow:hidden;
    box-shadow:0 4px 16px rgba(0,0,0,.07); transition:transform .3s;
  }
  .video-card:hover { transform:translateY(-4px); }
  .video-card img { width:100%; aspect-ratio:16/9; object-fit:cover; }
  .video-card-body { padding:1rem; }
  .video-card-body h4 { font-size:.92rem; font-weight:600; margin-bottom:.5rem; line-height:1.4; }
  .video-card-actions { display:flex; gap:.5rem; margin-top:.75rem; }

  /* ── FEEDBACK ── */
  .feedback-card {
    background:#FAFAFA; border:1px solid var(--border); border-radius:12px;
    padding:1.2rem 1.5rem; margin-bottom:1rem;
  }
  .feedback-header { display:flex; justify-content:space-between; margin-bottom:.6rem; }
  .feedback-name { font-weight:700; font-size:.95rem; }
  .feedback-date { font-size:.8rem; color:var(--muted); }
  .feedback-text { font-size:.9rem; color:#444; line-height:1.7; }
  .stars { color:var(--amber); font-size:1rem; margin-bottom:.4rem; }

  /* ── MODAL ── */
  .modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.5);
    z-index:1000; align-items:center; justify-content:center;
  }
  .modal-overlay.open { display:flex; }
  .modal {
    background:white; border-radius:20px; padding:2rem; width:540px; max-width:95vw;
    box-shadow:0 30px 80px rgba(0,0,0,.25);
  }
  .modal h3 { font-family:'Crimson Pro',serif; font-size:1.5rem; margin-bottom:1.5rem; }
  .modal-form { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
  .modal-form .form-group.full { grid-column:1/-1; }
  .modal-actions { display:flex; gap:.75rem; margin-top:1.5rem; justify-content:flex-end; }
  .btn-cancel { padding:.7rem 1.4rem; border:2px solid var(--border); border-radius:8px; background:none; cursor:pointer; font-weight:600; }
  .btn-save   { padding:.7rem 1.4rem; background:var(--teal); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; }

  /* ── RESPONSIVE ── */
  @media(max-width:900px){
    .stats { grid-template-columns:repeat(2,1fr); }
  }
</style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <h2>LearnHub</h2>
    <p>Admin Control Panel</p>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Main</div>
    <a class="nav-item <?= $tab=='dashboard'?'active':'' ?>" href="?tab=dashboard">
      <span class="icon">📊</span> Dashboard
    </a>

    <div class="nav-label">Management</div>
    <a class="nav-item <?= $tab=='users'?'active':'' ?>" href="?tab=users">
      <span class="icon">👥</span> All Users
    </a>
    <a class="nav-item <?= $tab=='videos'?'active':'' ?>" href="?tab=videos">
      <span class="icon">🎬</span> View Videos
    </a>
    <a class="nav-item <?= $tab=='upload'?'active':'' ?>" href="?tab=upload">
      <span class="icon">⬆️</span> Upload Video
    </a>
    <a class="nav-item <?= $tab=='watches'?'active':'' ?>" href="?tab=watches">
      <span class="icon">👁️</span> Watch Activity
    </a>

    <div class="nav-label">Insights</div>
    <a class="nav-item <?= $tab=='feedback'?'active':'' ?>" href="?tab=feedback">
      <span class="icon">💬</span> Student Feedback
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="admin-pill">
      <div class="admin-avatar"><?= strtoupper(substr($admin_name,0,2)) ?></div>
      <div class="admin-info">
        <p><?= htmlspecialchars($admin_name) ?></p>
        <span>Administrator</span>
      </div>
    </div>
    <a href="index.html" class="logout-btn" style="margin-bottom:.5rem; display:block;">🌐 View Site</a>
    <a href="logout.php" class="logout-btn">🚪 Logout</a>
  </div>
</aside>

<!-- ══ MAIN CONTENT ══ -->
<main class="main">

  <!-- ── TOPBAR ── -->
  <div class="topbar">
    <div>
      <h1>
        <?php
        $titles = ['dashboard'=>'Dashboard','users'=>'All Users','videos'=>'Videos','upload'=>'Upload Video','watches'=>'Watch Activity','feedback'=>'Student Feedback'];
        echo $titles[$tab] ?? 'Dashboard';
        ?>
      </h1>
      <p>Welcome back, <?= htmlspecialchars($admin_name) ?> 👋</p>
    </div>
    <a href="index.html" class="view-site">🌐 View Site</a>
  </div>

  <!-- ── STAT CARDS (always visible) ── -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-icon teal">👥</div>
      <div>
        <div class="stat-value"><?= $total_users ?></div>
        <div class="stat-label">Total Students</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon coral">🎬</div>
      <div>
        <div class="stat-value"><?= $total_videos ?></div>
        <div class="stat-label">Total Videos</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber">👁️</div>
      <div>
        <div class="stat-value"><?= $total_watches ?></div>
        <div class="stat-label">Total Watches</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon purple">💬</div>
      <div>
        <div class="stat-value"><?= $total_feedback ?></div>
        <div class="stat-label">Feedback Received</div>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════
       DASHBOARD TAB
  ════════════════════════════ -->
  <div class="section <?= $tab=='dashboard'?'active':'' ?>">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">

      <!-- Recent Users -->
      <div class="card">
        <div class="card-header">
          <h3>Recent Students</h3>
          <a href="?tab=users" style="font-size:.85rem; color:var(--teal); text-decoration:none; font-weight:600;">View All →</a>
        </div>
        <div class="card-body" style="padding:0;">
          <table>
            <thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
            <tbody>
            <?php
            $res = $conn->query("SELECT name, email, created_at FROM users WHERE role='user' ORDER BY id DESC LIMIT 5");
            if($res && $res->num_rows > 0){
              while($row=$res->fetch_assoc()){
                $date = isset($row['created_at']) ? date('M d', strtotime($row['created_at'])) : 'N/A';
                echo "<tr>
                  <td><strong>".htmlspecialchars($row['name'])."</strong></td>
                  <td style='color:var(--muted)'>".htmlspecialchars($row['email'])."</td>
                  <td style='color:var(--muted)'>$date</td>
                </tr>";
              }
            } else {
              echo "<tr><td colspan='3' style='text-align:center;color:var(--muted)'>No students yet</td></tr>";
            }
            ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Most Watched -->
      <div class="card">
        <div class="card-header">
          <h3>Most Watched Videos</h3>
          <a href="?tab=watches" style="font-size:.85rem; color:var(--teal); text-decoration:none; font-weight:600;">View All →</a>
        </div>
        <div class="card-body" style="padding:0;">
          <table>
            <thead><tr><th>Video ID</th><th>Watch Count</th></tr></thead>
            <tbody>
            <?php
            $res = $conn->query("SELECT w.video_id, COUNT(*) as cnt,
                                        COALESCE(c.title, w.video_id) as video_title
                                 FROM watch_history w
                                 LEFT JOIN courses c ON c.video_id = w.video_id
                                 GROUP BY w.video_id ORDER BY cnt DESC LIMIT 5");
            if($res && $res->num_rows > 0){
              while($row=$res->fetch_assoc()){
                echo "<tr>
                  <td><strong>".htmlspecialchars($row['video_title'])."</strong></td>
                  <td><span class='badge badge-student'>".$row['cnt']." watches</span></td>
                </tr>";
              }
            } else {
              echo "<tr><td colspan='2' style='text-align:center;color:var(--muted)'>No watch data yet</td></tr>";
            }
            ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <!-- ════════════════════════════
       USERS TAB
  ════════════════════════════ -->
  <div class="section <?= $tab=='users'?'active':'' ?>">
    <div class="card">
      <div class="card-header">
        <h3>All Registered Users</h3>
        <span style="font-size:.85rem; color:var(--muted);"><?= $total_users ?> users</span>
      </div>
      <div class="card-body" style="padding:0;">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Watches</th></tr></thead>
          <tbody>
          <?php
          $res = $conn->query("SELECT u.id, u.name, u.email, u.role, u.created_at,
                                COUNT(w.id) as watch_count
                               FROM users u
                               LEFT JOIN watch_history w ON w.user_id=u.id
                               GROUP BY u.id ORDER BY u.id DESC");
          if($res && $res->num_rows > 0){
            $i=1;
            while($row=$res->fetch_assoc()){
              $role_badge = $row['role']=='admin' ? "<span class='badge badge-admin'>Admin</span>" : "<span class='badge badge-student'>User</span>";
              $date = isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A';
              echo "<tr>
                <td style='color:var(--muted)'>{$i}</td>
                <td><strong>".htmlspecialchars($row['name'])."</strong></td>
                <td style='color:var(--muted)'>".htmlspecialchars($row['email'])."</td>
                <td>$role_badge</td>
                <td style='color:var(--muted)'>$date</td>
                <td><span class='badge badge-student'>{$row['watch_count']} videos</span></td>
              </tr>";
              $i++;
            }
          } else {
            echo "<tr><td colspan='6' style='text-align:center;color:var(--muted);padding:2rem'>No users found</td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════
       VIDEOS TAB
  ════════════════════════════ -->
  <div class="section <?= $tab=='videos'?'active':'' ?>">
    <?php if($upload_msg=='success') echo "<div class='alert alert-success'>✅ Video added successfully!</div>"; ?>
    <?php if($upload_msg=='error')   echo "<div class='alert alert-error'>❌ Failed to add video. Check your database.</div>"; ?>
    <div class="video-grid">
    <?php
    $res = $conn->query("SELECT * FROM courses ORDER BY id DESC");
    if($res && $res->num_rows > 0){
      while($row=$res->fetch_assoc()){
        $thumb = "https://img.youtube.com/vi/{$row['video_id']}/maxresdefault.jpg";
        $level_badge = match(strtolower($row['level'] ?? '')){
          'intermediate' => "badge-inter",
          'advanced'     => "badge-advanced",
          default        => "badge-beginner"
        };
        echo "
        <div class='video-card'>
          <img src='$thumb' alt='thumbnail'>
          <div class='video-card-body'>
            <span class='badge {$level_badge}'>".htmlspecialchars($row['level'] ?? 'Beginner')."</span>
            <h4>".htmlspecialchars($row['title'])."</h4>
            <div style='font-size:.8rem;color:var(--muted)'>".htmlspecialchars($row['category'] ?? '')." • ".htmlspecialchars($row['duration'] ?? '')."</div>
            <div class='video-card-actions'>
              <button class='btn-sm btn-edit' onclick='openEdit({$row['id']},".json_encode($row).")'>✏️ Edit</button>
              <a href='?tab=videos&delete_video={$row['id']}' class='btn-sm btn-delete' onclick='return confirm(\"Delete this video?\")'>🗑️ Delete</a>
            </div>
          </div>
        </div>";
      }
    } else {
      echo "<div style='grid-column:1/-1;text-align:center;color:var(--muted);padding:3rem'>No videos yet. <a href=\"?tab=upload\">Upload one →</a></div>";
    }
    ?>
    </div>
  </div>

  <!-- ════════════════════════════
       UPLOAD TAB
  ════════════════════════════ -->
  <div class="section <?= $tab=='upload'?'active':'' ?>">
    <div class="card">
      <div class="card-header"><h3>⬆️ Upload New Video</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="upload-form">
            <div class="form-group">
              <label>Video Title</label>
              <input type="text" name="vid_title" placeholder="e.g. React JS Complete Guide" required>
            </div>
            <div class="form-group">
              <label>YouTube Video ID</label>
              <input type="text" name="vid_id" placeholder="e.g. ua-CiDNNj30" required>
            </div>
            <div class="form-group">
              <label>Category</label>
              <select name="vid_cat">
                <option>Web Development</option>
                <option>Frontend Development</option>
                <option>Backend Development</option>
                <option>Data Science</option>
                <option>Artificial Intelligence</option>
                <option>Mobile Development</option>
                <option>Database</option>
                <option>Cyber Security</option>
                <option>Design</option>
                <option>Programming</option>
              </select>
            </div>
            <div class="form-group">
              <label>Level</label>
              <select name="vid_level">
                <option>Beginner</option>
                <option>Intermediate</option>
                <option>Advanced</option>
              </select>
            </div>
            <div class="form-group">
              <label>Duration (e.g. 12h 30m)</label>
              <input type="text" name="vid_duration" placeholder="e.g. 12h 30m">
            </div>
            <div class="form-group">
              <label>Instructor Name</label>
              <input type="text" name="vid_instructor" placeholder="e.g. John Smith">
            </div>
            <div class="form-group full">
              <label>Description</label>
              <textarea name="vid_desc" placeholder="Short course description..."></textarea>
            </div>
            <!-- QUIZ QUESTIONS SECTION -->
            <div style="grid-column:1/-1; margin-top:1.5rem; border-top:2px dashed var(--border); padding-top:1.5rem;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <div>
                  <h4 style="font-size:1rem; font-weight:700; color:var(--dark);">📝 Quiz Questions (Optional)</h4>
                  <p style="font-size:.82rem; color:var(--muted); margin-top:.2rem;">Add up to 10 questions. Students will take this quiz at the end of the video.</p>
                </div>
                <button type="button" onclick="addQuestion()" id="addQBtn" style="padding:.5rem 1.1rem; background:var(--teal); color:white; border:none; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer;">+ Add Question</button>
              </div>
              <div id="questionsContainer"></div>
              <div id="noQMsg" style="text-align:center; padding:1.2rem; background:#f4f6f9; border-radius:10px; color:var(--muted); font-size:.88rem;">
                No questions yet. Click <strong>"+ Add Question"</strong> to begin.
              </div>
            </div>

            <button type="submit" name="upload_video" class="upload-btn">⬆️ Upload Video to Platform</button>
          </div>
        </form>
        <div style="margin-top:1.5rem; padding:1rem; background:#F8F9FA; border-radius:10px; font-size:.85rem; color:var(--muted);">
          💡 <strong>How to get YouTube Video ID:</strong> Open any YouTube video. The ID is the part after <code>?v=</code> in the URL. Example: youtube.com/watch?v=<strong>ua-CiDNNj30</strong>
        </div>
      </div>
    </div>
  </div>

  <!-- QUIZ JS + CSS -->
  <style>
    .quiz-question-box {
      background:#fff; border:2px solid var(--border); border-radius:12px;
      padding:1.2rem 1.5rem; margin-bottom:1rem; position:relative;
      animation: qfadeIn .3s ease;
    }
    @keyframes qfadeIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
    .quiz-question-box .q-header {
      display:flex; justify-content:space-between; align-items:center; margin-bottom:.9rem;
    }
    .quiz-question-box .q-num {
      font-size:.78rem; font-weight:700; color:var(--teal);
      background:rgba(45,95,93,.1); padding:.3rem .75rem; border-radius:50px;
    }
    .quiz-question-box .q-remove {
      background:#fdedec; color:#c0392b; border:none; border-radius:6px;
      padding:.3rem .75rem; font-size:.8rem; font-weight:600; cursor:pointer; transition:.2s;
    }
    .quiz-question-box .q-remove:hover { background:#c0392b; color:white; }
    .quiz-question-box .q-text {
      width:100%; padding:.7rem .9rem; border:1.5px solid var(--border);
      border-radius:8px; font-family:'DM Sans',sans-serif; font-size:.9rem;
      margin-bottom:.75rem; background:white; box-sizing:border-box;
    }
    .quiz-question-box .q-text:focus {
      outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(45,95,93,.08);
    }
    .quiz-options-grid { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; margin-bottom:.75rem; }
    .quiz-option-row { display:flex; align-items:center; gap:.5rem; }
    .quiz-option-row .opt-label {
      font-size:.78rem; font-weight:700; color:white; background:var(--teal);
      width:22px; height:22px; border-radius:50%; display:flex; align-items:center;
      justify-content:center; flex-shrink:0; font-family:'DM Sans',sans-serif;
    }
    .quiz-option-row input {
      flex:1; padding:.6rem .9rem; border:1.5px solid var(--border); border-radius:8px;
      font-family:'DM Sans',sans-serif; font-size:.88rem; background:white;
    }
    .quiz-option-row input:focus { outline:none; border-color:var(--teal); }
    .quiz-correct-row { display:flex; align-items:center; gap:.6rem; margin-top:.3rem; }
    .quiz-correct-row .correct-label { font-size:.82rem; font-weight:600; color:var(--dark); }
    .quiz-correct-row select {
      padding:.45rem .9rem; border:1.5px solid var(--border); border-radius:8px;
      font-family:'DM Sans',sans-serif; font-size:.85rem; background:white; cursor:pointer;
    }
    .quiz-correct-row select:focus { outline:none; border-color:var(--teal); }
  </style>

  <script>
    var questionCount = 0;
    var maxQuestions  = 10;

    function addQuestion() {
      if(questionCount >= maxQuestions){ alert('Maximum 10 questions allowed!'); return; }
      questionCount++;
      var idx = Date.now(); // unique key
      var container = document.getElementById('questionsContainer');
      document.getElementById('noQMsg').style.display = 'none';

      var displayNum = container.children.length + 1;

      var html = `
        <div class="quiz-question-box" id="qbox_${idx}">
          <div class="q-header">
            <span class="q-num">Question ${displayNum}</span>
            <button type="button" class="q-remove" onclick="removeQuestion(${idx})">✕ Remove</button>
          </div>
          <input class="q-text" type="text" name="questions[${idx}][question]" placeholder="Write your question here..." required>
          <div class="quiz-options-grid">
            <div class="quiz-option-row">
              <span class="opt-label">A</span>
              <input type="text" name="questions[${idx}][option_a]" placeholder="Option A" required>
            </div>
            <div class="quiz-option-row">
              <span class="opt-label">B</span>
              <input type="text" name="questions[${idx}][option_b]" placeholder="Option B" required>
            </div>
            <div class="quiz-option-row">
              <span class="opt-label">C</span>
              <input type="text" name="questions[${idx}][option_c]" placeholder="Option C (optional)">
            </div>
            <div class="quiz-option-row">
              <span class="opt-label">D</span>
              <input type="text" name="questions[${idx}][option_d]" placeholder="Option D (optional)">
            </div>
          </div>
          <div class="quiz-correct-row">
            <span class="correct-label">✅ Correct Answer:</span>
            <select name="questions[${idx}][correct]">
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
            </select>
          </div>
        </div>`;

      container.insertAdjacentHTML('beforeend', html);
      if(questionCount >= maxQuestions){
        document.getElementById('addQBtn').style.opacity = '.45';
        document.getElementById('addQBtn').disabled = true;
      }
    }

    function removeQuestion(idx) {
      var box = document.getElementById('qbox_' + idx);
      if(box){ box.remove(); questionCount--; }
      document.getElementById('addQBtn').style.opacity = '1';
      document.getElementById('addQBtn').disabled = false;
      if(document.getElementById('questionsContainer').children.length === 0){
        document.getElementById('noQMsg').style.display = 'block';
      }
    }
  </script>

  <!-- ════════════════════════════
       WATCH ACTIVITY TAB
  ════════════════════════════ -->
  <div class="section <?= $tab=='watches'?'active':'' ?>">
    <div class="card">
      <div class="card-header">
        <h3>👁️ Watch Activity Log</h3>
        <span style="font-size:.85rem; color:var(--muted);"><?= $total_watches ?> total watches</span>
      </div>
      <div class="card-body" style="padding:0;">
        <table>
          <thead><tr><th>#</th><th>Student</th><th>Video ID</th><th>Watched At</th></tr></thead>
          <tbody>
          <?php
          $res = $conn->query("SELECT w.id, u.name, u.email, w.video_id, w.watched_at,
                                     COALESCE(c.title, w.video_id) as video_title
                               FROM watch_history w
                               JOIN users u ON u.id = w.user_id
                               LEFT JOIN courses c ON c.video_id = w.video_id
                               ORDER BY w.watched_at DESC LIMIT 100");
          if($res && $res->num_rows > 0){
            $i=1;
            while($row=$res->fetch_assoc()){
              $date = isset($row['watched_at']) ? date('M d, Y H:i', strtotime($row['watched_at'])) : 'N/A';
              $thumb = "https://img.youtube.com/vi/{$row['video_id']}/default.jpg";
              echo "<tr>
                <td style='color:var(--muted)'>{$i}</td>
                <td>
                  <strong>".htmlspecialchars($row['name'])."</strong><br>
                  <span style='font-size:.8rem;color:var(--muted)'>".htmlspecialchars($row['email'])."</span>
                </td>
                <td>
                  <img src='$thumb' style='width:60px;border-radius:6px;vertical-align:middle;margin-right:.5rem'>
                  <strong>".htmlspecialchars($row['video_title'])."</strong>
                </td>
                <td style='color:var(--muted);font-size:.85rem'>$date</td>
              </tr>";
              $i++;
            }
          } else {
            echo "<tr><td colspan='4' style='text-align:center;color:var(--muted);padding:2rem'>No watch activity yet</td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════
       FEEDBACK TAB
  ════════════════════════════ -->
  <div class="section <?= $tab=='feedback'?'active':'' ?>">
    <div class="card">
      <div class="card-header">
        <h3>💬 Student Feedback</h3>
        <span style="font-size:.85rem; color:var(--muted);"><?= $total_feedback ?> entries</span>
      </div>
      <div class="card-body">
      <?php
      $res = $conn->query("SELECT f.id, f.name, f.email, f.course, f.role, f.rating, f.title, f.message, f.got_job, f.recommend, f.created_at, f.user_id FROM feedback f ORDER BY f.id DESC");
      if($res && $res->num_rows > 0){
        while($row=$res->fetch_assoc()){
          $stars = str_repeat('★', $row['rating'] ?? 5) . str_repeat('☆', 5-($row['rating'] ?? 5));
          $date = isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : '';
          echo "
          <div class='feedback-card'>
            <div class='feedback-header'>
              <span class='feedback-name'>".htmlspecialchars($row['name'] ?? 'Anonymous')."</span>
              <span class='feedback-date'>$date</span>
            </div>
            <div class='stars'>$stars</div>
            <div class='feedback-text'>".htmlspecialchars($row['message'] ?? '')."</div>
            ".($row['course'] ? "<div style='font-size:.82rem;color:#888;margin-top:.4rem'>&#128218; Course: ".htmlspecialchars($row['course'])."</div>" : "")."
            ".($row['recommend'] ? "<div style='font-size:.8rem;color:#888;margin-top:.3rem'>&#128077; Recommend: ".htmlspecialchars($row['recommend'])."</div>" : "")."
          </div>";
        }
      } else {
        echo "<div style='text-align:center;color:var(--muted);padding:2rem'>No feedback submitted yet.</div>";
      }
      ?>
      </div>
    </div>
  </div>

</main>

<!-- ══ EDIT MODAL ══ -->
<style>
  .modal { max-height:90vh; overflow-y:auto; }
  .edit-quiz-section {
    margin-top:1.5rem; border-top:2px dashed var(--border); padding-top:1.2rem;
  }
  .edit-quiz-header {
    display:flex; justify-content:space-between; align-items:center; margin-bottom:.8rem;
  }
  .edit-quiz-header h4 { font-size:.95rem; font-weight:700; color:var(--dark); }
  .edit-quiz-header p  { font-size:.78rem; color:var(--muted); margin-top:.15rem; }
  #addEditQBtn {
    padding:.4rem .9rem; background:var(--teal); color:white; border:none;
    border-radius:7px; font-size:.82rem; font-weight:600; cursor:pointer; flex-shrink:0;
  }
  .eq-box {
    background:#f8f9fa; border:1.5px solid var(--border); border-radius:10px;
    padding:1rem 1.2rem; margin-bottom:.75rem; position:relative;
    animation:qfadeIn .25s ease;
  }
  .eq-box .eq-head {
    display:flex; justify-content:space-between; align-items:center; margin-bottom:.7rem;
  }
  .eq-box .eq-num {
    font-size:.75rem; font-weight:700; color:var(--teal);
    background:rgba(45,95,93,.1); padding:.25rem .65rem; border-radius:50px;
  }
  .eq-box .eq-del {
    background:#fdedec; color:#c0392b; border:none; border-radius:5px;
    padding:.25rem .65rem; font-size:.78rem; font-weight:600; cursor:pointer;
  }
  .eq-box .eq-del:hover { background:#c0392b; color:white; }
  .eq-box input[type="text"] {
    width:100%; padding:.55rem .8rem; border:1.5px solid var(--border);
    border-radius:7px; font-family:'DM Sans',sans-serif; font-size:.85rem;
    margin-bottom:.5rem; background:white; box-sizing:border-box;
  }
  .eq-opts { display:grid; grid-template-columns:1fr 1fr; gap:.45rem; margin-bottom:.5rem; }
  .eq-opt-row { display:flex; align-items:center; gap:.4rem; }
  .eq-opt-row .eq-lbl {
    font-size:.72rem; font-weight:700; color:white; background:var(--teal);
    width:20px; height:20px; border-radius:50%; display:flex; align-items:center;
    justify-content:center; flex-shrink:0;
  }
  .eq-opt-row input { flex:1; padding:.5rem .7rem; border:1.5px solid var(--border); border-radius:7px; font-size:.82rem; background:white; }
  .eq-correct-row { display:flex; align-items:center; gap:.5rem; margin-top:.2rem; }
  .eq-correct-row label { font-size:.8rem; font-weight:600; color:var(--dark); }
  .eq-correct-row select {
    padding:.35rem .7rem; border:1.5px solid var(--border); border-radius:6px;
    font-family:'DM Sans',sans-serif; font-size:.82rem; background:white; cursor:pointer;
  }
  #editQuestionsContainer .eq-box input:focus,
  #editQuestionsContainer .eq-opt-row input:focus,
  #editQuestionsContainer .eq-correct-row select:focus {
    outline:none; border-color:var(--teal);
  }
</style>

<div class="modal-overlay" id="editModal">
  <div class="modal">
    <h3>✏️ Edit Video</h3>
    <form method="POST">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="modal-form">
        <div class="form-group full">
          <label>Title</label>
          <input type="text" name="edit_title" id="edit_title" required>
        </div>
        <div class="form-group">
          <label>Category</label>
          <input type="text" name="edit_cat" id="edit_cat">
        </div>
        <div class="form-group">
          <label>Level</label>
          <select name="edit_level" id="edit_level">
            <option>Beginner</option><option>Intermediate</option><option>Advanced</option>
          </select>
        </div>
        <div class="form-group">
          <label>Duration</label>
          <input type="text" name="edit_duration" id="edit_duration">
        </div>
        <div class="form-group">
          <label>Instructor</label>
          <input type="text" name="edit_instructor" id="edit_instructor">
        </div>
        <div class="form-group full">
          <label>Description</label>
          <textarea name="edit_desc" id="edit_desc"></textarea>
        </div>

        <!-- ── QUIZ QUESTIONS SECTION IN EDIT ── -->
        <div class="form-group full edit-quiz-section">
          <div class="edit-quiz-header">
            <div>
              <h4>📝 Quiz Questions</h4>
              <p>Edit or add questions for this video's quiz</p>
            </div>
            <button type="button" id="addEditQBtn" onclick="addEditQuestion()">+ Add Question</button>
          </div>
          <div id="editQuestionsContainer"></div>
          <div id="editNoQMsg" style="text-align:center; padding:1rem; background:#f4f6f9; border-radius:8px; color:var(--muted); font-size:.85rem;">
            No questions yet. Click <strong>"+ Add Question"</strong> to add.
          </div>
        </div>

      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeEdit()">Cancel</button>
        <button type="submit" name="edit_video" class="btn-save">💾 Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
var editQCount = 0;
var editQMax   = 10;

function addEditQuestion(qData) {
  if(editQCount >= editQMax){ alert('Maximum 10 questions!'); return; }
  editQCount++;
  var idx = Date.now() + editQCount;
  var container = document.getElementById('editQuestionsContainer');
  document.getElementById('editNoQMsg').style.display = 'none';

  var q    = qData ? qData.question  : '';
  var oa   = qData ? qData.option_a  : '';
  var ob   = qData ? qData.option_b  : '';
  var oc   = qData ? qData.option_c  : '';
  var od   = qData ? qData.option_d  : '';
  var cor  = qData ? qData.correct   : 'A';
  var num  = container.children.length + 1;

  var html = `
    <div class="eq-box" id="eqbox_${idx}">
      <div class="eq-head">
        <span class="eq-num">Question ${num}</span>
        <button type="button" class="eq-del" onclick="delEditQuestion(${idx})">✕ Remove</button>
      </div>
      <input type="text" name="edit_questions[${idx}][question]" placeholder="Question text..." value="${escQ(q)}" required>
      <div class="eq-opts">
        <div class="eq-opt-row"><span class="eq-lbl">A</span><input type="text" name="edit_questions[${idx}][option_a]" placeholder="Option A" value="${escQ(oa)}" required></div>
        <div class="eq-opt-row"><span class="eq-lbl">B</span><input type="text" name="edit_questions[${idx}][option_b]" placeholder="Option B" value="${escQ(ob)}" required></div>
        <div class="eq-opt-row"><span class="eq-lbl">C</span><input type="text" name="edit_questions[${idx}][option_c]" placeholder="Option C (optional)" value="${escQ(oc)}"></div>
        <div class="eq-opt-row"><span class="eq-lbl">D</span><input type="text" name="edit_questions[${idx}][option_d]" placeholder="Option D (optional)" value="${escQ(od)}"></div>
      </div>
      <div class="eq-correct-row">
        <label>✅ Correct Answer:</label>
        <select name="edit_questions[${idx}][correct]">
          <option value="A" ${cor=='A'?'selected':''}>A</option>
          <option value="B" ${cor=='B'?'selected':''}>B</option>
          <option value="C" ${cor=='C'?'selected':''}>C</option>
          <option value="D" ${cor=='D'?'selected':''}>D</option>
        </select>
      </div>
    </div>`;

  container.insertAdjacentHTML('beforeend', html);
  if(editQCount >= editQMax){
    document.getElementById('addEditQBtn').style.opacity='.4';
    document.getElementById('addEditQBtn').disabled=true;
  }
}

function escQ(s){ return (s||'').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

function delEditQuestion(idx){
  var box = document.getElementById('eqbox_'+idx);
  if(box){ box.remove(); editQCount--; }
  document.getElementById('addEditQBtn').style.opacity='1';
  document.getElementById('addEditQBtn').disabled=false;
  if(document.getElementById('editQuestionsContainer').children.length===0){
    document.getElementById('editNoQMsg').style.display='block';
  }
}

function openEdit(id, data){
  document.getElementById('edit_id').value        = id;
  document.getElementById('edit_title').value     = data.title || '';
  document.getElementById('edit_cat').value       = data.category || '';
  document.getElementById('edit_desc').value      = data.description || '';
  document.getElementById('edit_duration').value  = data.duration || '';
  document.getElementById('edit_instructor').value= data.instructor || '';
  document.getElementById('edit_level').value     = data.level || 'Beginner';

  // Reset quiz area
  editQCount = 0;
  document.getElementById('editQuestionsContainer').innerHTML = '';
  document.getElementById('editNoQMsg').style.display = 'block';
  document.getElementById('addEditQBtn').style.opacity = '1';
  document.getElementById('addEditQBtn').disabled = false;

  // Load existing quiz questions via AJAX
  fetch('get_quiz.php?course_id=' + id)
    .then(r => r.json())
    .then(res => {
      if(res.success && res.questions.length > 0){
        document.getElementById('editNoQMsg').style.display = 'none';
        res.questions.forEach(q => addEditQuestion(q));
      }
    })
    .catch(() => {});

  document.getElementById('editModal').classList.add('open');
}

function closeEdit(){
  document.getElementById('editModal').classList.remove('open');
}
document.getElementById('editModal').addEventListener('click', function(e){
  if(e.target === this) closeEdit();
});
</script>
</body>
</html>