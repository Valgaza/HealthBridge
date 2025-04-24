<?php
require_once 'config.php';
require_patient();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $doctor_specialty = sanitize_input($_POST['doctor_specialty']);
    $preferred_date = sanitize_input($_POST['preferred_date']);
    $reason_for_consultation = sanitize_input($_POST['reason_for_consultation']);
    $consultation_type = sanitize_input($_POST['consultation_type']);
    $preferred_time = sanitize_input($_POST['preferred_time']);
    $related_symptom = sanitize_input($_POST['related_symptom']);
    $additional_notes = sanitize_input($_POST['additional_notes']);

    // Get patient ID from session
    $patient_id = $_SESSION['user_id'];
    
    // Get doctor ID based on specialty
    $sql = "SELECT id FROM users WHERE user_type = 'doctor' AND specialization = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $doctor_specialty);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $doctor = $result->fetch_assoc();
        $doctor_id = $doctor['id'];
    } else {
        echo "No doctor found with this specialty.";
        exit();
    }

    // Insert the consultation request into the database
    $sql_insert = "INSERT INTO consultations (user_id, doctor_id, consultation_date, consultation_time, consultation_type, consultation_status, reason_for_consultation, related_symptom, additional_notes)
                   VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('iissssss', $patient_id, $doctor_id, $preferred_date, $preferred_time, $consultation_type, $reason_for_consultation, $related_symptom, $additional_notes);
    if ($stmt_insert->execute()) {
        // Redirect back to the consultations page
        header("Location: ../patient/consultations.php");
        exit();
    } else {
        echo "Error: " . $stmt_insert->error;
    }

    $stmt_insert->close();
    mysqli_close($conn);
}
?>
