<?php 
require_once __DIR__ . '/../php/config.php';
require_patient();

if (empty($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

// Recent Symptoms (last 3)
$symptoms = [];
$sql = "SELECT symptom_name, symptom_date, symptom_severity 
        FROM symptoms 
        WHERE user_id = ? 
        ORDER BY symptom_date DESC, symptom_time DESC 
        LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $symptoms[] = $row;
}
$stmt->close();

// Past Prescriptions (last 2)
$prescriptions = [];
$sql = "SELECT medication, prescription_date, instructions 
        FROM prescriptions 
        WHERE patient_id = ? 
        ORDER BY prescription_date DESC 
        LIMIT 2";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $prescriptions[] = $row;
}
$stmt->close();

// Health Tips (last 2 visible to this patient or public)
$health_tips = [];
$sql = "SELECT ht.tip_title, ht.tip_content, u.full_name AS doctor_name
        FROM health_tips ht
        JOIN users u ON ht.doctor_id = u.id
        WHERE (ht.patient_id = ? OR ht.visibility = 'public')
          AND ht.tip_date <= CURDATE()
        ORDER BY ht.tip_date DESC
        LIMIT 2";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $health_tips[] = $row;
}
$stmt->close();

// Next upcoming consultation (accepted)
$next_consult = null;
$sql = "SELECT c.consultation_date, c.consultation_time, c.consultation_type, u.full_name AS doctor_name, u.specialization
        FROM consultations c
        JOIN users u ON c.doctor_id = u.id
        WHERE c.user_id = ?
          AND c.consultation_status = 'accepted'
          AND c.consultation_date >= CURDATE()
        ORDER BY c.consultation_date, c.consultation_time
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $next_consult = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Patient Dashboard - HealthBridge</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="../images/logo.png" alt="HealthBridge Logo" class="logo">
        <h2>HealthBridge</h2>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li class="active"><a href="dashboard.php"><span class="icon">📊</span> Dashboard</a></li>
          <li><a href="symptoms.php"><span class="icon">🤒</span> Symptoms</a></li>
          <li><a href="health-tips.php"><span class="icon">💡</span> Health Tips</a></li>
          <li><a href="fitness-goals.php"><span class="icon">🏃</span> Fitness Goals</a></li>
          <li><a href="diet-plans.php"><span class="icon">🥗</span> Diet Plans</a></li>
          <li><a href="consultations.php"><span class="icon">👨‍⚕️</span> Consultations</a></li>
          <li><a href="prescriptions.php"><span class="icon">💊</span> Prescription</a></li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="../php/logout.php" class="logout-btn"><span class="icon">🚪</span> Logout</a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="content-header">
        <h1>Patient Dashboard</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($full_name) ?></span>
          <img src="../images/avatar.png" alt="User Avatar" class="user-avatar">
        </div>
      </header>

      <div class="dashboard-summary">
        <!-- Recent Symptoms -->
        <div class="summary-card">
          <h3>Recent Symptoms</h3>
          <div class="card-content">
            <?php foreach ($symptoms as $s): ?>
              <div class="symptom-item">
                <div class="symptom-info">
                  <h4><?= htmlspecialchars($s['symptom_name']) ?></h4>
                  <p>Reported on: <span><?= date('M j, Y', strtotime($s['symptom_date'])) ?></span></p>
                  <p>Severity: <span class="severity <?= $s['symptom_severity'] ?>">
                    <?= ucfirst($s['symptom_severity']) ?>
                  </span></p>
                </div>
                <div class="symptom-actions">
                  <a href="consultations.php" class="btn btn-small">Book Consultation</a>
                </div>
              </div>
            <?php endforeach; ?>
            <a href="symptoms.php" class="view-all">View all symptoms →</a>
          </div>
        </div>

        <!-- Past Prescriptions -->
        <div class="summary-card">
          <h3>Past Prescriptions</h3>
          <div class="card-content">
            <?php foreach ($prescriptions as $p): ?>
              <div class="prescription-item">
                <h4><?= htmlspecialchars($p['medication']) ?></h4>
                <p>Prescribed on: <span><?= date('M j, Y', strtotime($p['prescription_date'])) ?></span></p>
                <p>Instructions: <span><?= htmlspecialchars($p['instructions']) ?></span></p>
              </div>
            <?php endforeach; ?>
            <a href="prescriptions.php" class="view-all">View all prescriptions →</a>
          </div>
        </div>

        <!-- Health Tips -->
        <div class="summary-card">
          <h3>Health Tips From Doctors</h3>
          <div class="card-content">
            <?php foreach ($health_tips as $t): ?>
              <div class="health-tip-item">
                <h4><?= htmlspecialchars($t['tip_title']) ?></h4>
                <p>From: <span><?= htmlspecialchars($t['doctor_name']) ?></span></p>
                <p><?= htmlspecialchars($t['tip_content']) ?></p>
              </div>
            <?php endforeach; ?>
            <a href="health-tips.php" class="view-all">View all health tips →</a>
          </div>
        </div>
      </div>

      <!-- Upcoming Consultation -->
      <?php if ($next_consult): ?>
      <div class="upcoming-consultations">
        <h3>Upcoming Consultation</h3>
        <div class="consultation-list">
          <div class="consultation-item">
            <div class="consultation-info">
              <h4><?= htmlspecialchars($next_consult['doctor_name']) ?></h4>
              <p><?= htmlspecialchars($next_consult['specialization']) ?></p>
              <p>Date: <span><?= date('M j, Y', strtotime($next_consult['consultation_date'])) ?></span></p>
              <p>Time: <span><?= date('g:i A', strtotime($next_consult['consultation_time'])) ?></span></p>
              <p>Type: <span><?= ucfirst($next_consult['consultation_type']) ?></span></p>
            </div>
            <div class="consultation-actions">
              <button class="btn btn-secondary" disabled>Scheduled</button>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </main>
  </div>

  <script src="../js/dashboard.js"></script>
</body>
</html>
