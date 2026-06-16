<?php
session_start();
include "config.php";

$error = "";
$success = "";

if(isset($_POST['signup'])){

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $pass     = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // ✅ Password match check
    if($pass !== $confirm){
        $error = "Passwords do not match!";
    } else {

        $password = password_hash($pass, PASSWORD_DEFAULT);

        // ✅ Role determine karo POST se
        $role = "user";

        if(isset($_POST['role']) && $_POST['role'] == 'admin'){
            if(!empty($_POST['admin_key'])){
                if($_POST['admin_key'] == "LEARNHUB2024"){
                    $role = "admin";
                } else {
                    $error = "Wrong Admin Secret Key!";
                }
            } else {
                $error = "Admin Secret Key is required!";
            }
        }

        if(empty($error)){

            // ✅ Email already exists check
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if($check->num_rows > 0){
                $error = "Email already exists! Please login.";
            } else {

                // ✅ User insert karo
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $password, $role);

                if($stmt->execute()){
                    $success = "Account created successfully! You can now login.";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
                $stmt->close();
            }
            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up — LearnHub</title>
<link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

  :root {
    --teal:  #2D5F5D;
    --coral: #E07A5F;
    --amber: #F4A261;
    --dark:  #181818;
    --cream: #FDF8F3;
    --white: #FFFFFF;
    --muted: #6B6B6B;
    --border:#E0E0E0;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
  }

  /* ── BG ORBS ── */
  .bg { position:fixed; inset:0; z-index:0; pointer-events:none; overflow:hidden; }
  .bg span { position:absolute; border-radius:50%; opacity:.04; animation:drift 20s ease-in-out infinite; }
  .bg span:nth-child(1){ width:600px;height:600px;background:var(--teal);  top:-200px;right:-150px; }
  .bg span:nth-child(2){ width:450px;height:450px;background:var(--coral); bottom:-150px;left:-150px; animation-delay:8s; }
  .bg span:nth-child(3){ width:300px;height:300px;background:var(--amber); top:50%;left:50%; animation-delay:14s; }
  @keyframes drift {
    0%,100%{ transform:translate(0,0) scale(1); }
    40%{ transform:translate(50px,-50px) scale(1.08); }
    70%{ transform:translate(-30px,35px) scale(.94); }
  }

  /* ── WRAPPER ── */
  .wrapper {
    position: relative; z-index: 10;
    width: 100%; max-width: 860px;
    animation: riseUp .7s ease-out both;
  }
  @keyframes riseUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }

  .logo {
    text-align: center; margin-bottom: 2rem;
    font-family: 'Crimson Pro', serif;
    font-size: 2.2rem; font-weight: 700;
    color: var(--teal); text-decoration: none;
    display: block;
  }

  /* ── ALERTS ── */
  .alert-error {
    background: #fff0f0; color: #c0392b;
    padding: .85rem 1rem; border-radius: 10px;
    margin-bottom: 1.2rem; font-size: .9rem;
    border-left: 4px solid #c0392b;
    display: flex; align-items: center; gap: .5rem;
  }
  .alert-success {
    background: #f0fff4; color: #27ae60;
    padding: .85rem 1rem; border-radius: 10px;
    margin-bottom: 1.2rem; font-size: .9rem;
    border-left: 4px solid #27ae60;
    display: flex; align-items: center; gap: .5rem;
  }

  /* ── ROLE PANELS ── */
  .panels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .panel {
    background: var(--white);
    border: 2.5px solid var(--border);
    border-radius: 20px;
    padding: 2rem 1.5rem;
    cursor: pointer;
    transition: all .3s cubic-bezier(.22,1,.36,1);
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .panel::before {
    content:''; position:absolute; inset:0; opacity:0;
    transition: opacity .3s; border-radius: 18px;
  }
  .panel.user-panel::before  { background: linear-gradient(135deg,rgba(45,95,93,.06),rgba(45,95,93,.02)); }
  .panel.admin-panel::before { background: linear-gradient(135deg,rgba(224,122,95,.08),rgba(244,162,97,.04)); }

  .panel:hover, .panel.active {
    transform: translateY(-6px);
    box-shadow: 0 20px 60px rgba(0,0,0,.12);
  }
  .panel:hover::before, .panel.active::before { opacity:1; }
  .panel.user-panel:hover,  .panel.user-panel.active  { border-color: var(--teal); }
  .panel.admin-panel:hover, .panel.admin-panel.active { border-color: var(--coral); }

  .panel-icon { font-size: 3rem; margin-bottom: 1rem; display: block; transition: transform .3s; }
  .panel:hover .panel-icon { transform: scale(1.15); }

  .panel h3 {
    font-family: 'Crimson Pro', serif;
    font-size: 1.5rem; font-weight: 700;
    margin-bottom: .5rem; color: var(--dark);
  }
  .panel p { font-size: .88rem; color: var(--muted); line-height: 1.6; }

  .panel .perks { margin-top: 1rem; display: flex; flex-direction: column; gap: .35rem; text-align: left; }
  .perk { font-size: .82rem; color: var(--muted); display: flex; align-items: center; gap: .4rem; }
  .perk span.dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink:0; }
  .user-panel  .dot { background: var(--teal); }
  .admin-panel .dot { background: var(--coral); }

  .selected-badge {
    display: none; position: absolute; top: 14px; right: 14px;
    background: var(--teal); color: white;
    font-size: .72rem; font-weight: 700;
    padding: .3rem .7rem; border-radius: 50px; letter-spacing: .03em;
  }
  .admin-panel .selected-badge { background: var(--coral); }
  .panel.active .selected-badge { display: block; }

  /* ── FORM BOX ── */
  .form-box {
    background: var(--white); border-radius: 20px; padding: 2.5rem;
    box-shadow: 0 10px 50px rgba(0,0,0,.08);
    display: none; animation: riseUp .4s ease-out both;
  }
  .form-box.show { display: block; }

  .form-box h2 {
    font-family: 'Crimson Pro', serif;
    font-size: 1.8rem; font-weight: 700;
    margin-bottom: 1.5rem; color: var(--dark);
    display: flex; align-items: center; gap: .6rem;
  }

  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
  .form-group { display: flex; flex-direction: column; gap: .4rem; }
  .form-group.full { grid-column: 1 / -1; }

  label { font-size: .85rem; font-weight: 600; color: var(--dark); }

  input[type="text"],
  input[type="email"],
  input[type="password"] {
    padding: .85rem 1rem;
    border: 2px solid var(--border);
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: .95rem;
    transition: border-color .25s, box-shadow .25s;
    width: 100%;
  }
  input:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 4px rgba(45,95,93,.1); }
  .admin-form input:focus { border-color: var(--coral); box-shadow: 0 0 0 4px rgba(224,122,95,.1); }

  .key-hint { font-size: .78rem; color: var(--muted); margin-top: .3rem; display: flex; align-items: center; gap: .3rem; }

  .submit-btn {
    width: 100%; padding: 1rem; border: none; border-radius: 50px;
    font-family: 'DM Sans', sans-serif; font-size: 1rem; font-weight: 700;
    cursor: pointer; margin-top: 1.2rem; transition: all .3s; letter-spacing: .02em;
  }
  .user-form  .submit-btn { background: var(--teal);  color: white; }
  .admin-form .submit-btn { background: var(--coral); color: white; }
  .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 14px 40px rgba(0,0,0,.18); }

  .login-link { text-align:center; margin-top:1.2rem; font-size:.9rem; color:var(--muted); }
  .login-link a { color:var(--teal); font-weight:600; text-decoration:none; }
  .login-link a:hover { text-decoration:underline; }

  @media(max-width:640px){
    .panels { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<div class="bg"><span></span><span></span><span></span></div>

<div class="wrapper">

  <a href="index.html" class="logo">LearnHub</a>

  <?php if(!empty($error)): ?>
    <div class="alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if(!empty($success)): ?>
    <div class="alert-success">✅ <?php echo htmlspecialchars($success); ?> <a href="login.php" style="color:#27ae60;font-weight:700;margin-left:8px;">Login Now →</a></div>
  <?php endif; ?>

  <!-- ── ROLE SELECTION PANELS ── -->
  <div class="panels">

    <!-- STUDENT PANEL -->
    <div class="panel user-panel" onclick="selectRole('student')" id="panel-student">
      <span class="selected-badge">✓ Selected</span>
      <span class="panel-icon">🎓</span>
      <h3>Student</h3>
      <p>Join as a learner and access all courses, track your progress, and build your skills.</p>
      <div class="perks">
        <div class="perk"><span class="dot"></span> Browse all courses</div>
        <div class="perk"><span class="dot"></span> Watch & track videos</div>
        <div class="perk"><span class="dot"></span> View watch history</div>
        <div class="perk"><span class="dot"></span> Submit feedback</div>
      </div>
    </div>

    <!-- ADMIN PANEL -->
    <div class="panel admin-panel" onclick="selectRole('admin')" id="panel-admin">
      <span class="selected-badge">✓ Selected</span>
      <span class="panel-icon">⚙️</span>
      <h3>Admin</h3>
      <p>Full control over the platform — manage users, videos, courses, and view feedback.</p>
      <div class="perks">
        <div class="perk"><span class="dot"></span> Admin dashboard</div>
        <div class="perk"><span class="dot"></span> Manage & upload videos</div>
        <div class="perk"><span class="dot"></span> View all user accounts</div>
        <div class="perk"><span class="dot"></span> See student feedback</div>
      </div>
    </div>

  </div>

  <!-- ── STUDENT FORM ── -->
  <div class="form-box user-form" id="form-student">
    <h2>🎓 Student Sign Up</h2>
    <form method="POST" action="signup.php">
      <!-- ✅ Hidden role field -->
      <input type="hidden" name="role" value="user">
      <div class="form-row">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" placeholder="John Doe" required value="<?php echo isset($_POST['name']) && isset($_POST['role']) && $_POST['role']=='user' ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="john@email.com" required value="<?php echo isset($_POST['email']) && isset($_POST['role']) && $_POST['role']=='user' ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Create a password" required>
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="Repeat password" required>
        </div>
      </div>
      <button type="submit" name="signup" class="submit-btn">Create Student Account →</button>
    </form>
    <div class="login-link">Already have an account? <a href="login.php">Login here</a></div>
  </div>

  <!-- ── ADMIN FORM ── -->
  <div class="form-box admin-form" id="form-admin">
    <h2>⚙️ Admin Sign Up</h2>
    <form method="POST" action="signup.php">
      <!-- ✅ Hidden role field -->
      <input type="hidden" name="role" value="admin">
      <div class="form-row">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" placeholder="Admin Name" required value="<?php echo isset($_POST['name']) && isset($_POST['role']) && $_POST['role']=='admin' ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="admin@learnhub.com" required value="<?php echo isset($_POST['email']) && isset($_POST['role']) && $_POST['role']=='admin' ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Create a password" required>
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="Repeat password" required>
        </div>
        <div class="form-group full">
          <label>Admin Secret Key</label>
          <input type="password" name="admin_key" placeholder="Enter the admin secret key" required>
          <div class="key-hint">🔑 Admin key required — contact the platform owner</div>
        </div>
      </div>
      <button type="submit" name="signup" class="submit-btn">Create Admin Account →</button>
    </form>
    <div class="login-link">Already have an account? <a href="login.php">Login here</a></div>
  </div>

</div><!-- end wrapper -->

<script>
function selectRole(role) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.form-box').forEach(f => f.classList.remove('show'));
  document.getElementById('panel-' + role).classList.add('active');
  document.getElementById('form-' + role).classList.add('show');
  document.getElementById('form-' + role).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ✅ Error ke baad panel open rakho
<?php if(!empty($error) && isset($_POST['role'])): ?>
  var savedRole = '<?php echo $_POST['role'] == 'admin' ? 'admin' : 'student'; ?>';
  selectRole(savedRole);
<?php endif; ?>
</script>

</body>
</html>