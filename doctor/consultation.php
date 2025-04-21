<?php
require_once '../php/config.php';
require_doctor();

// ————————————————————————————————————————————————————————————
// 1) Handle “accept” / “decline” actions immediately and redirect
// ————————————————————————————————————————————————————————————
if (isset($_GET['action'], $_GET['id']) && is_numeric($_GET['id'])) {
    $consult_id = (int)$_GET['id'];
    $doctor_id  = $_SESSION['user_id'];

    if ($_GET['action'] === 'accept') {
        $new_status = 'accepted';
    } elseif ($_GET['action'] === 'decline') {
        $new_status = 'declined';
    }

    if (!empty($new_status)) {
        $stmt = $conn->prepare(
            "UPDATE consultations
                SET consultation_status = ?
              WHERE id = ?
                AND doctor_id = ?"
        );
        $stmt->bind_param('sii', $new_status, $consult_id, $doctor_id);
        $stmt->execute();
        $stmt->close();

        header('Location: consultations.php');
        exit;
    }
}

// ————————————————————————————————————————————————————————————
// 2) Fetch & display the consultation detail
// ————————————————————————————————————————————————————————————

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: consultations.php');
    exit;
}
$consult_id = (int)$_GET['id'];
$doctor_id  = $_SESSION['user_id'];

// **Fixed here**: JOIN users ON u.id = c.user_id (not c.patient_id)
$sql_consult = "
  SELECT
    c.*,
    u.full_name   AS patient_name,
    u.location    AS patient_location
  FROM consultations c
  JOIN users        u ON u.id = c.user_id
  WHERE c.id        = $consult_id
    AND c.doctor_id = $doctor_id
";
$res_consult = mysqli_query($conn, $sql_consult);
if (mysqli_num_rows($res_consult) === 0) {
    header('Location: consultations.php');
    exit;
}
$consult    = mysqli_fetch_assoc($res_consult);
$patient_id = $consult['user_id'];

// Fetch current symptom
$current_symptom = null;
if ($consult['symptom_id']) {
    $sql_sym = "SELECT * FROM symptoms WHERE id = {$consult['symptom_id']}";
    $res_sym = mysqli_query($conn, $sql_sym);
    $current_symptom = mysqli_fetch_assoc($res_sym);
}

// Fetch past symptoms (excluding current)
$sql_history = "
  SELECT *
    FROM symptoms
   WHERE user_id = $patient_id
     AND id      != {$consult['symptom_id']}
   ORDER BY symptom_date DESC, symptom_time DESC
";
$res_history = mysqli_query($conn, $sql_history);

// Fetch past prescriptions for patient
$sql_presc = "
  SELECT
    p.*,
    u.full_name AS doctor_name
  FROM prescriptions p
  JOIN users u ON u.id = p.doctor_id
 WHERE p.patient_id = $patient_id
 ORDER BY p.prescription_date DESC
 LIMIT 5
";
$res_presc = mysqli_query($conn, $sql_presc);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Consultation Detail – HealthBridge</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
  <link rel="stylesheet" href="../css/consultation.css">
</head>
<body>
  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="../images/logo.png" alt="Logo" class="logo">
        <h2>HealthBridge</h2>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php">📊 Dashboard</a></li>
          <li class="active"><a href="consultations.php">👨‍⚕️ Consultations</a></li>
          <li><a href="patients.php">👥 Patients</a></li>
          <li><a href="health-tips.php">💡 Health Tips</a></li>
          <li><a href="prescriptions.php">💊 Prescriptions</a></li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="../php/logout.php" class="logout-btn">🚪 Logout</a>
      </div>
    </aside>
    <main class="main-content">
      <header class="content-header">
        <h1>Patient Consultation</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <img src="../images/doctor-avatar.png" alt="Avatar" class="user-avatar">
        </div>
      </header>
      <div class="consultation-container">
        <div class="patient-info-panel">
          <div class="patient-header">
            <img src="../images/avatar.png" alt="Avatar" class="patient-avatar">
            <div class="patient-details">
              <h2><?= htmlspecialchars($consult['patient_name']) ?></h2>
              <p>Location: <?= htmlspecialchars($consult['patient_location']) ?></p>
              <p>Consultation ID: C<?= $consult_id ?></p>
            </div>
          </div>
          <div class="consultation-tabs">
            <button class="tab-btn active" data-tab="current-symptoms">Current Symptoms</button>
            <button class="tab-btn" data-tab="medical-history">Medical History</button>
            <button class="tab-btn" data-tab="past-prescriptions">Past Prescriptions</button>
          </div>

          <!-- Current Symptoms -->
          <div class="tab-content active" id="current-symptoms">
            <?php if ($current_symptom): ?>
              <div class="symptom-detail">
                <h3><?= htmlspecialchars($current_symptom['symptom_name']) ?></h3>
                <p><strong>Reported:</strong>
                  <?= date('M j, Y', strtotime($current_symptom['symptom_date'])) ?>
                  at <?= date('g:i A', strtotime($current_symptom['symptom_time'])) ?>
                </p>
                <p><strong>Severity:</strong>
                  <span class="severity <?= $current_symptom['symptom_severity'] ?>">
                    <?= ucfirst($current_symptom['symptom_severity']) ?>
                  </span>
                </p>
                <p><strong>Notes:</strong>
                  <?= nl2br(htmlspecialchars($current_symptom['symptom_notes'])) ?>
                </p>
              </div>
            <?php else: ?>
              <p>No symptom details available.</p>
            <?php endif; ?>
          </div>

          <!-- Medical History -->
          <div class="tab-content" id="medical-history">
            <?php if (mysqli_num_rows($res_history) > 0): ?>
              <div class="medical-history-list">
                <?php while ($h = mysqli_fetch_assoc($res_history)): ?>
                  <div class="history-item">
                    <h4>
                      <?= htmlspecialchars($h['symptom_name']) ?> –
                      <?= date('M j, Y', strtotime($h['symptom_date'])) ?>
                    </h4>
                    <p>Severity:
                      <span class="severity <?= $h['symptom_severity'] ?>">
                        <?= ucfirst($h['symptom_severity']) ?>
                      </span>
                    </p>
                    <p>Notes: <?= nl2br(htmlspecialchars($h['symptom_notes'])) ?></p>
                  </div>
                <?php endwhile; ?>
              </div>
            <?php else: ?>
              <p>No past symptoms found.</p>
            <?php endif; ?>
          </div>

          <!-- Past Prescriptions -->
          <div class="tab-content" id="past-prescriptions">
            <?php if (mysqli_num_rows($res_presc) > 0): ?>
              <div class="prescription-list">
                <?php while ($p = mysqli_fetch_assoc($res_presc)): ?>
                  <div class="prescription-item">
                    <h4>
                      <?= htmlspecialchars($p['medication']) ?> <?= htmlspecialchars($p['dosage']) ?>
                    </h4>
                    <p>Prescribed by:
                      <span><?= htmlspecialchars($p['doctor_name']) ?></span>
                    </p>
                    <p>Date:
                      <span><?= date('M j, Y', strtotime($p['prescription_date'])) ?></span>
                    </p>
                    <p>Instructions:
                      <?= nl2br(htmlspecialchars($p['instructions'])) ?>
                    </p>
                  </div>
                <?php endwhile; ?>
              </div>
            <?php else: ?>
              <p>No past prescriptions found.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Actions Panel (Diagnosis, Prescription, Health Tips) -->
        <div class="consultation-actions-panel">
          <!-- …your tabs/forms go here unchanged… -->
        </div>
      </div>
    </main>
  </div>
  <script src="../js/consultation.js"></script>
</body>
</html>
