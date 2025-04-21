<?php
// Include database configuration
require_once 'config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = sanitize_input($_POST['user_type']);
    $location = sanitize_input($_POST['location']);
    
    // For doctors, get specialization
    $specialization = '';
    if ($user_type == 'doctor') {
        $specialization = sanitize_input($_POST['specialization']);
    }
    
    // Validate input
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($location)) {
        echo "Please fill in all required fields";
        exit();
    }
    
    if ($password != $confirm_password) {
        echo "Passwords do not match";
        exit();
    }
    
    if ($user_type == 'doctor' && empty($specialization)) {
        echo "Please select your specialization";
        exit();
    }
    
    // Check if email already exists
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        echo "Email already exists";
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $sql = "INSERT INTO users (full_name, email, password, user_type, location, specialization) 
            VALUES ('$full_name', '$email', '$hashed_password', '$user_type', '$location', '$specialization')";
    
    if (mysqli_query($conn, $sql)) {
        // Registration successful
        echo "Registration successful! Please login with your new account.";
        header("Location: ../index.php");
    } else {
        // Registration failed
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    
    // Close connection
    mysqli_close($conn);
} else {
    // If not a POST request, redirect to registration page
    header("Location: ../register.php");
    exit();
}
?>
