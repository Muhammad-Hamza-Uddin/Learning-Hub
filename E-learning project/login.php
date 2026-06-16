<?php
session_start();
include "config.php";

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    // ✅ Get user by email only
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $user = $result->fetch_assoc();

        // ✅ Verify hashed password
        if(password_verify($password, $user['password'])){

            // ✅ Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // ✅ Redirect based on role
            if($user['role'] == 'admin'){
                header("Location: admin.php");
            } else {
                header("Location: courses.php");
            }
            exit();

        } else {
            echo "❌ Wrong password!";
        }

    } else {
        echo "❌ User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - LearnHub</title>

<style>
*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family: 'Segoe UI', sans-serif;
}

body{
  height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
  background: linear-gradient(135deg, #667eea, #764ba2);
}

/* Container */
.container{
  width:800px;
  height:500px;
  background:#fff;
  border-radius:15px;
  display:flex;
  overflow:hidden;
  box-shadow:0 20px 50px rgba(0,0,0,0.2);
}

/* Left Side */
.left{
  width:50%;
  background: linear-gradient(135deg, #ff4b2b, #ff416c);
  color:#fff;
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
  text-align:center;
  padding:30px;
}

.left h1{
  margin-bottom:10px;
}

.left p{
  font-size:14px;
}

/* Right Side */
.right{
  width:50%;
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
}

form{
  width:80%;
  display:flex;
  flex-direction:column;
}

form h2{
  margin-bottom:20px;
}

input, select{
  padding:12px;
  margin:8px 0;
  border:1px solid #ccc;
  border-radius:6px;
}

button{
  padding:12px;
  margin-top:10px;
  border:none;
  background:#ff416c;
  color:#fff;
  border-radius:6px;
  cursor:pointer;
  transition:0.3s;
}

button:hover{
  background:#ff4b2b;
}

.switch{
  margin-top:10px;
  font-size:14px;
  cursor:pointer;
  color:#555;
}

/* Toggle Forms */
.signup{
  display:none;
}
</style>
</head>

<body>

<div class="container">

  <!-- Left Panel -->
  <div class="left">
    <h1>Welcome Back!</h1>
    <p>Login to continue learning</p>
  </div>

  <!-- Right Panel -->
  <div class="right">

    <!-- LOGIN FORM -->
    <form id="loginForm" method="POST">
      <h2>Login</h2>

      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>

      <button name="login">Login</button>

      <div class="switch" onclick="showSignup()">Don't have account? Signup</div>
    </form>

    <!-- SIGNUP FORM -->
    <form id="signupForm" class="signup" action="signup.php" method="POST">
      <h2>Signup</h2>

      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>

      <select name="role" onchange="toggleAdminKey(this)">
        <option value="user">User</option>
        <option value="admin">Admin</option>
      </select>

      <input type="text" name="admin_key" id="adminKey" placeholder="Admin Secret Key" style="display:none;">

      <button name="signup">Signup</button>

      <div class="switch" onclick="showLogin()">Already have account? Login</div>
    </form>

  </div>
</div>

<script>
function showSignup(){
  document.getElementById("loginForm").style.display="none";
  document.getElementById("signupForm").style.display="flex";
}

function showLogin(){
  document.getElementById("loginForm").style.display="flex";
  document.getElementById("signupForm").style.display="none";
}

function toggleAdminKey(select){
  let keyField = document.getElementById("adminKey");
  if(select.value === "admin"){
    keyField.style.display = "block";
  } else {
    keyField.style.display = "none";
  }
}
</script>

</body>
</html>