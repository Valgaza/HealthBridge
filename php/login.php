<?php
// Include database configuration
require_once 'config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $user_type = sanitize_input($_POST['user_type']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo "Please fill in all fields";
        exit();
    }
    
    // Check if email exists in database
    $sql = "SELECT * FROM users WHERE email = '$email' AND user_type = '$user_type'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a new session
            session_start();
            
            // Store data in session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Redirect user to appropriate dashboard
            if ($user_type == 'patient') {
                header("Location: ../patient/dashboard.php");
            } else {
                header("Location: ../doctor/dashboard.php");
            }
        } else {
            // Password is incorrect
            echo "Invalid password";
        }
    } else {
        // Email doesn't exist
        echo "Invalid email or user type";
    }
    
    // Close connection
    mysqli_close($conn);
} else {
    // If not a POST request, redirect to login page
    header("Location: ../index.php");
    exit();
}
?>
