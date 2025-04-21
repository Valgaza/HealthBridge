<?php
require_once __DIR__ . '/php/config.php';
// If already logged in, redirect to dashboard
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
  <title>Register – HealthBridge</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="login-container">
    <div class="login-form-container register-form-container">
      <div class="login-header">
        <img src="images/logo.png" alt="HealthBridge Logo" class="logo">
        <h1>Create Account</h1>
        <p>Join HealthBridge today</p>
      </div>
      <form action="php/register.php" method="post" class="login-form">
        <div class="form-group">
          <label for="full-name">Full Name</label>
          <input type="text" id="full-name" name="full_name" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
          <label for="confirm-password">Confirm Password</label>
          <input type="password" id="confirm-password" name="confirm_password" required>
        </div>
        <div class="form-group">
          <label for="user-type">I am a:</label>
          <select id="user-type" name="user_type">
            <option value="patient">Patient</option>
            <option value="doctor">Doctor</option>
          </select>
        </div>
        <div class="form-group doctor-fields" style="display: none;">
          <label for="specialization">Specialization</label>
          <select id="specialization" name="specialization">
            <option value="general">General Physician</option>
            <option value="cardiology">Cardiology</option>
            <option value="neurology">Neurology</option>
            <option value="dermatology">Dermatology</option>
            <option value="orthopedics">Orthopedics</option>
            <option value="pediatrics">Pediatrics</option>
            <option value="psychiatry">Psychiatry</option>
          </select>
        </div>
        <div class="form-group">
          <label for="location">Location</label>
          <input type="text" id="location" name="location" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
        <p class="register-link">
          Already have an account?
          <a href="index.php">Login here</a>
        </p>
      </form>
    </div>
  </div>
  <script src="js/register.js"></script>
</body>
</html>
