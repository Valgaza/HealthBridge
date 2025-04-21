<?php
require_once __DIR__ . '/php/config.php';
// If already logged in, send to the appropriate dashboard
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'doctor') {
        header('Location: doctor/dashboard.php');
    } else {
        header('Location: patient/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>HealthBridge – Medical Portal</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="login-container">
    <div class="login-form-container">
      <div class="login-header">
        <img src="images/logo.png" alt="HealthBridge Logo" class="logo">
        <h1>HealthBridge</h1>
        <p>Your bridge to better health</p>
      </div>
      <form action="php/login.php" method="post" class="login-form">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
          <label for="user-type">I am a:</label>
          <select id="user-type" name="user_type">
            <option value="patient">Patient</option>
            <option value="doctor">Doctor</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
        <p class="register-link">
          Don't have an account?
          <a href="register.php">Register here</a>
        </p>
      </form>
    </div>
  </div>
  <script src="js/login.js"></script>
</body>
</html>
