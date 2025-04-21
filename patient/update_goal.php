<?php
require_once __DIR__ . '/../php/config.php';
require_patient();

$patient_id = $_SESSION['user_id'];
$goal_id    = isset($_GET['id'])     ? (int)$_GET['id']      : 0;
$action     = isset($_GET['action']) ? $_GET['action']       : 'complete';

if (!$goal_id) {
    header('Location: fitness-goals.php');
    exit;
}

// Decide the SQL based on action
if ($action === 'reopen') {
    $sql = "UPDATE fitness_goals
               SET status   = 'active',
                   end_date = NULL
             WHERE id = ? AND patient_id = ?";
} else {
    // default: mark complete
    $sql = "UPDATE fitness_goals
               SET status   = 'completed',
                   end_date = CURDATE()
             WHERE id = ? AND patient_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $goal_id, $patient_id);
$stmt->execute();
$stmt->close();

// back where we came from
header('Location: fitness-goals.php');
exit;
