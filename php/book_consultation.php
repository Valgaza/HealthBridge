<?php
require_once 'config.php';
require_patient();

// Only handle POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize helper and session user_id
    $patient_id      = $_SESSION['user_id'];
    $doctor_specialty= sanitize_input($_POST['doctor_type']);
    $consult_date    = sanitize_input($_POST['preferred_date']);
    $consult_time    = sanitize_input($_POST['preferred_time']);
    $consult_type    = sanitize_input($_POST['consultation_type']);
    $reason          = sanitize_input($_POST['reason']);
    $related_symptom = ($_POST['symptom_relation']==='yes')
                        ? (int)$_POST['related_symptom']
                        : null;
    $additional      = sanitize_input($_POST['additional_notes']);

    // look up a doctor with that specialty
    $stmt = $conn->prepare(
      "SELECT id FROM users WHERE user_type='doctor' AND specialization=?"
    );
    $stmt->bind_param('s', $doctor_specialty);
    $stmt->execute();
    $doc = $stmt->get_result()->fetch_assoc();
    if (!$doc) {
        exit("No doctor found for “{$doctor_specialty}”.");
    }
    $doctor_id = $doc['id'];
    $stmt->close();

    // build a single notes field
    $notes = "Reason: {$reason}";
    if ($additional) {
        $notes .= "\n\nNotes: {$additional}";
    }

    // insert into your consultations table
    $ins = $conn->prepare(
      "INSERT INTO consultations
         (user_id, doctor_id, symptom_id,
          consultation_date, consultation_time,
          consultation_type, consultation_status,
          consultation_notes)
       VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)"
    );
    $ins->bind_param(
      'iiissss',
      $patient_id,
      $doctor_id,
      $related_symptom,
      $consult_date,
      $consult_time,
      $consult_type,
      $notes
    );

    if ($ins->execute()) {
        header("Location: ../patient/consultations.php");
        exit;
    } else {
        echo "Insert error: " . $ins->error;
    }
}
?>
