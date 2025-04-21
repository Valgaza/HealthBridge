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
    
    // Get arrays of medication data
    $medications = $_POST['medication'];
    $dosages = $_POST['dosage'];
    $frequencies = $_POST['frequency'];
    $duration_values = $_POST['duration_value'];
    $duration_units = $_POST['duration_unit'];
    $instructions = $_POST['instructions'];
    
    // Get doctor ID from session
    $doctor_id = $_SESSION['user_id'];
    
    // Loop through medications and insert each one
    for ($i = 0; $i < count($medications); $i++) {
        $medication = sanitize_input($medications[$i]);
        $dosage = sanitize_input($dosages[$i]);
        $frequency = sanitize_input($frequencies[$i]);
        $duration_value = sanitize_input($duration_values[$i]);
        $duration_unit = sanitize_input($duration_units[$i]);
        $instruction = sanitize_input($instructions[$i]);
        
        // Validate input
        if (empty($medication) || empty($dosage) || empty($frequency) || empty($duration_value) || empty($duration_unit)) {
            echo "Please fill in all required fields for all medications";
            exit();
        }
        
        // Insert prescription into database
        $prescription_date = date('Y-m-d');
        
        $sql = "INSERT INTO prescriptions (patient_id, doctor_id, consultation_id, medication, dosage, frequency, duration_value, duration_unit, instructions, prescription_date) 
                VALUES ('$patient_id', '$doctor_id', '$consultation_id', '$medication', '$dosage', '$frequency', '$duration_value', '$duration_unit', '$instruction', '$prescription_date')";
        
        if (!mysqli_query($conn, $sql)) {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            exit();
        }
    }
    
    // Redirect to consultations page
    header("Location: ../doctor/consultations.php");
    
    // Close connection
    mysqli_close($conn);
} else {
    // If not a POST request, redirect to consultations page
    header("Location: ../doctor/consultations.php");
    exit();
}
?>
