<?php
// Include database configuration
require_once 'config.php';

// Check if user is logged in and is a doctor
require_doctor();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $patient_id = sanitize_input($_POST['patient_id']);
    $consultation_id = sanitize_input($_POST['consultation_id']);
    $diagnosis = sanitize_input($_POST['diagnosis']);
    $consultation_notes = sanitize_input($_POST['consultation_notes']);
    $follow_up = sanitize_input($_POST['follow_up']);
    $follow_up_date = '';
    
    if ($follow_up == 'yes') {
        $follow_up_date = sanitize_input($_POST['follow_up_date']);
    }
    
    // Get doctor ID from session
    $doctor_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($diagnosis) || empty($consultation_notes)) {
        echo "Please fill in all required fields";
        exit();
    }
    
    // Update consultation in database
    $sql = "UPDATE consultations 
            SET diagnosis = '$diagnosis', consultation_notes = '$consultation_notes', 
                follow_up = '$follow_up', follow_up_date = '$follow_up_date', 
                consultation_status = 'completed', doctor_id = '$doctor_id' 
            WHERE id = '$consultation_id'";
    
    if (mysqli_query($conn, $sql)) {
        // Redirect to consultations page
        header("Location: ../doctor/consultations.php");
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    
    // Close connection
    mysqli_close($conn);
} else {
    // If not a POST request, redirect to consultations page
    header("Location: ../doctor/consultations.php");
    exit();
}
?>
