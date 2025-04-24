<?php
// Include database configuration
require_once 'config.php';

// Check if user is logged in and is a doctor
require_doctor();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $patient_id = sanitize_input($_POST['patient_id']);
    $consultation_id = isset($_POST['consultation_id']) ? sanitize_input($_POST['consultation_id']) : null; // Make consultation_id optional
    $tip_title = sanitize_input($_POST['tip_title']);
    $tip_category = sanitize_input($_POST['tip_category']);
    $tip_content = sanitize_input($_POST['tip_content']);
    $visibility = sanitize_input($_POST['visibility']);
    
    // Get doctor ID from session
    $doctor_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($tip_title) || empty($tip_category) || empty($tip_content)) {
        echo "Please fill in all required fields";
        exit();
    }

    // Validate patient_id
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'patient'");
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        echo "Invalid patient ID.";
        exit();
    }
    $stmt->close();

    // Validate consultation_id if it's provided (optional field)
    if ($consultation_id) {
        // Check if consultation_id exists in the consultations table and belongs to the logged-in doctor
        $stmt = $conn->prepare("SELECT id FROM consultations WHERE id = ? AND doctor_id = ?");
        $stmt->bind_param('ii', $consultation_id, $doctor_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            echo "Invalid consultation ID.";
            exit();
        }
        $stmt->close();
    }

    // Get today's date for the health tip
    $tip_date = date('Y-m-d');
    
    // Insert health tip into the database using prepared statement
    $stmt = $conn->prepare("INSERT INTO health_tips (doctor_id, patient_id, consultation_id, tip_title, tip_category, tip_content, tip_date, visibility) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iiisssss', $doctor_id, $patient_id, $consultation_id, $tip_title, $tip_category, $tip_content, $tip_date, $visibility);
    
    // Execute the query
    if ($stmt->execute()) {
        // Redirect to the consultations page
        header("Location: ../doctor/consultations.php");
        exit();
    } else {
        // Error inserting data
        echo "Error: " . $stmt->error;
    }
    
    // Close the statement and connection
    $stmt->close();
    mysqli_close($conn);
} else {
    // If not a POST request, redirect to consultations page
    header("Location: ../doctor/consultations.php");
    exit();
}
?>
