<?php
require_once '../php/config.php';
require_doctor();
$doctor_id = $_SESSION['user_id'];

// 1) Get all patients
$sql = "
  SELECT id, full_name, location, created_at
    FROM users
   WHERE user_type = 'patient'
   ORDER BY full_name
";
$res = mysqli_query($conn, $sql);

// Helper: fetch last and next consult for one patient
function get_consult_info($conn, $patient_id, $doctor_id) {
  // Last consultation
  $last_q = "
    SELECT c.consultation_date, s.symptom_name
      FROM consultations c
      LEFT JOIN symptoms s
        ON s.id = c.symptom_id
     WHERE c.user_id = $patient_id
       AND c.doctor_id = $doctor_id
       AND c.consultation_status = 'completed'
     ORDER BY c.consultation_date DESC
     LIMIT 1
  ";
  $last_r = mysqli_query($conn, $last_q);
  $last   = mysqli_fetch_assoc($last_r) ?: ['consultation_date'=>'—','symptom_name'=>'—'];

  // Next appointment
  $next_q = "
    SELECT consultation_date
      FROM consultations
     WHERE user_id = $patient_id
       AND doctor_id = $doctor_id
       AND consultation_date > CURDATE()
     ORDER BY consultation_date ASC
     LIMIT 1
  ";
  $next_r = mysqli_query($conn, $next_q);
  $next   = mysqli_fetch_assoc($next_r);

  return [$last, $next];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Patients – HealthBridge</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="../images/logo.png" alt="Logo" class="logo">
        <h2>HealthBridge</h2>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php">📊 Dashboard</a></li>
          <li><a href="consultations.php">👨‍⚕️ Consultations</a></li>
          <li class="active"><a href="patients.php">👥 Patients</a></li>
          <li><a href="health-tips.php">💡 Health Tips</a></li>
          <li><a href="prescriptions.php">💊 Prescriptions</a></li>
          <li><a href="requests.php">📩 Requests</a></li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="../php/logout.php" class="logout-btn">🚪 Logout</a>
      </div>
    </aside>

    <!-- Main -->
    <main class="main-content">
      <header class="content-header">
        <h1>Patients</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <img src="../images/doctor-avatar.png" class="user-avatar">
        </div>
      </header>

      <!-- Filters (static—JS can hook in) -->
      <div class="filter-bar">
        <div class="search-container">
          <input type="text" id="search-patients" placeholder="Search patients by name or ID…">
        </div>
      </div>

      <!-- Patients Grid -->
      <div class="patients-grid">
        <?php while($row = mysqli_fetch_assoc($res)): 
          list($last, $next) = get_consult_info($conn, $row['id'], $doctor_id);

          // Determine status
          $created = new DateTime($row['created_at']);
          $diff    = (new DateTime())->diff($created)->days;
          if ($diff <= 7) {
            $status_class = 'new';
            $status_label = 'New Patient';
          } else {
            $status_class = 'active';
            $status_label = 'Active Patient';
          }
        ?>
        <div class="patient-card">
          <div class="patient-header">
            <img src="../images/avatar.png" alt="Avatar" class="patient-avatar">
            <div class="patient-basic-info">
              <h3><?= htmlspecialchars($row['full_name']) ?></h3>
              <p>ID: P<?= $row['id'] ?> | Location: <?= htmlspecialchars($row['location']) ?></p>
              <span class="patient-status <?= $status_class ?>"><?= $status_label ?></span>
            </div>
            <div class="patient-actions">
            </div>
          </div>
          <div class="patient-content">
            <div class="patient-details">
              <div class="detail-section">
                <h4>Recent Consultation</h4>
                <p>Last Visit: <span><?= $last['consultation_date'] ?></span></p>
                <p>Reason: <span><?= htmlspecialchars($last['symptom_name']) ?></span></p>
                <p>Next Appointment: <span><?= $next['consultation_date'] ?? '—' ?></span></p>
              </div>
            </div>
            <div class="patient-quick-actions">
              <a href="prescriptions.php?patient=<?= $row['id'] ?>" class="btn btn-small">Prescriptions</a>
              <a href="consultations.php?new=&user=<?= $row['id'] ?>" class="btn btn-small">Schedule Follow‑up</a>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </main>
  </div>
  <script src="../js/patients.js"></script>
</body>
</html>
