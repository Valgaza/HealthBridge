<?php
require_once __DIR__ . '/../php/config.php';
require_doctor();

$doctor_id = $_SESSION['user_id'];

// Fetch all pending consultation requests for this doctor
$sql = "
    SELECT c.id, u.full_name AS patient_name, s.symptom_name, s.symptom_severity, c.consultation_date, c.consultation_time, c.consultation_type
    FROM consultations c
    JOIN users u ON u.id = c.user_id
    LEFT JOIN symptoms s ON s.id = c.symptom_id
    WHERE c.doctor_id = ?
    AND c.consultation_status = 'pending'
    ORDER BY c.consultation_date DESC, c.consultation_time DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Function to handle accepting or declining a consultation request
if (isset($_GET['action']) && isset($_GET['consultation_id'])) {
    $action = $_GET['action']; // 'accept' or 'decline'
    $consultation_id = $_GET['consultation_id'];

    if ($action == 'accept') {
        $status = 'accepted';
    } elseif ($action == 'decline') {
        $status = 'declined';
    } else {
        $status = 'pending';
    }

    // Update the consultation status in the database
    $update_sql = "UPDATE consultations SET consultation_status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('si', $status, $consultation_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Redirect back to the requests page
    header("Location: requests.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Requests – HealthBridge</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="../images/logo.png" class="logo" alt="Logo">
        <h2>HealthBridge</h2>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php">📊 Dashboard</a></li>
          <li><a href="consultations.php">👨‍⚕️ Consultations</a></li>
          <li><a href="patients.php">👥 Patients</a></li>
          <li><a href="health-tips.php">💡 Health Tips</a></li>
          <li><a href="prescriptions.php">💊 Prescriptions</a></li>
          <li class="active"><a href="requests.php">📩 Requests</a></li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="../php/logout.php" class="logout-btn">🚪 Logout</a>
      </div>
    </aside>

    <main class="main-content">
      <header class="content-header">
        <h1>Consultation Requests</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <img src="../images/doctor-avatar.png" class="user-avatar" alt="Doctor Avatar">
        </div>
      </header>

      <div class="requests-container">
        <?php if (empty($requests)): ?>
          <p>No pending consultation requests.</p>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <div class="request-item">
              <div class="request-header">
                <h4><?= htmlspecialchars($r['patient_name']) ?></h4>
                <span class="consultation-type <?= htmlspecialchars($r['consultation_type']) ?>">
                  <?= ucfirst($r['consultation_type']) ?>
                </span>
              </div>
              <div class="request-details">
                <p><strong>Date:</strong> <?= date('F j, Y', strtotime($r['consultation_date'])) ?></p>
                <p><strong>Time:</strong> <?= date('g:i A', strtotime($r['consultation_time'])) ?></p>
                <p><strong>Reason:</strong>
                  <?= htmlspecialchars($r['symptom_name']) ?>,
                  <?= ucfirst(htmlspecialchars($r['symptom_severity'])) ?>
                </p>
              </div>
              <div class="request-actions">
                <a href="requests.php?action=accept&consultation_id=<?= $r['id'] ?>" class="btn btn-primary">Accept</a>
                <a href="requests.php?action=decline&consultation_id=<?= $r['id'] ?>" class="btn btn-outline">Decline</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script src="../js/doctor-requests.js"></script>
</body>
</html>
