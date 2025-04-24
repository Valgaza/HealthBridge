<?php
// Include database configuration
require_once 'config.php';

// Check if user is logged in and is a patient
require_patient();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $symptom_name = sanitize_input($_POST['symptom_name']);
    $symptom_date = sanitize_input($_POST['symptom_date']);
    $symptom_time = sanitize_input($_POST['symptom_time']);
    $symptom_severity = sanitize_input($_POST['symptom_severity']);
    $doctor_type = sanitize_input($_POST['doctor_type']);
    $symptom_notes = sanitize_input($_POST['symptom_notes']);
    $action = sanitize_input($_POST['action']);
    
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($symptom_name) || empty($symptom_date) || empty($symptom_time) || empty($symptom_severity)) {
        echo "Please fill in all required fields";
        exit();
    }
    
    // Insert symptom into database
    $sql = "INSERT INTO symptoms (user_id, symptom_name, symptom_date, symptom_time, symptom_severity, doctor_type, symptom_notes) 
            VALUES ('$user_id', '$symptom_name', '$symptom_date', '$symptom_time', '$symptom_severity', '$doctor_type', '$symptom_notes')";
    
    if (mysqli_query($conn, $sql)) {
        // Get the ID of the inserted symptom
        $symptom_id = mysqli_insert_id($conn);
        
        // If action is to book consultation
        if ($action == 'consult') {
            // Insert consultation request into database
            $consultation_date = date('Y-m-d');
            $consultation_status = 'pending';
            
            $sql = "INSERT INTO consultations (user_id, symptom_id, consultation_date, consultation_status) 
                    VALUES ('$user_id', '$symptom_id', '$consultation_date', '$consultation_status')";
            
            if (mysqli_query($conn, $sql)) {
                // Redirect to consultations page
                header("Location: ../patient/consultations.php");
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
        } else {
            // Redirect to symptoms page
            header("Location: ../patient/symptoms.php");
        }
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    
    // Close connection
    mysqli_close($conn);
} else {
    // If not a POST request, redirect to symptoms page
    header("Location: ../patient/symptoms.php");
    exit();
}
?>
