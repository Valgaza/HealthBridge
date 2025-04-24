<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "healthbridge";

// Create database connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($conn, "utf8");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input data
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is a patient
function is_patient() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'patient';
}

// Function to check if user is a doctor
function is_doctor() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'doctor';
}

// Function to redirect user if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: /healthbridge/index.php");
        exit();
    }
}

// Function to redirect user if not a patient
function require_patient() {
    require_login();
    if (!is_patient()) {
        header("Location: /healthbridge/doctor/dashboard.php");
        exit();
    }
}

// Function to redirect user if not a doctor
function require_doctor() {
    require_login();
    if (!is_doctor()) {
        header("Location: /healthbridge/patient/dashboard.php");
        exit();
    }
}
?>
