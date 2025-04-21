<?php
require_once '../php/config.php';
require_doctor();
$doctor_id = $_SESSION['user_id'];

// --- Stats queries ---
// Total distinct patients
$sql = "SELECT COUNT(DISTINCT user_id) AS cnt 
        FROM consultations 
        WHERE doctor_id = $doctor_id";
$res = mysqli_query($conn, $sql);
$total_patients = mysqli_fetch_assoc($res)['cnt'];

// Today's appointments
$sql = "SELECT COUNT(*) AS cnt 
        FROM consultations 
        WHERE doctor_id = $doctor_id 
          AND consultation_date = CURDATE()";
$res = mysqli_query($conn, $sql);
$today_appointments = mysqli_fetch_assoc($res)['cnt'];

// Pending requests
$sql = "SELECT COUNT(*) AS cnt 
        FROM consultations 
        WHERE doctor_id = $doctor_id 
          AND consultation_status = 'pending'";
$res = mysqli_query($conn, $sql);
$pending_requests = mysqli_fetch_assoc($res)['cnt'];

// Completed this week
$sql = "SELECT COUNT(*) AS cnt 
        FROM consultations 
        WHERE doctor_id = $doctor_id 
          AND consultation_status = 'completed' 
          AND YEARWEEK(consultation_date,1)=YEARWEEK(CURDATE(),1)";
$res = mysqli_query($conn, $sql);
$completed_week = mysqli_fetch_assoc($res)['cnt'];

// --- Upcoming consultations ---
$sql = "
  SELECT c.id, u.full_name, c.consultation_date, c.consultation_time,
         c.consultation_type, s.symptom_name, s.symptom_severity
  FROM consultations c
  JOIN users u       ON u.id = c.user_id
  JOIN symptoms s    ON s.id = c.symptom_id
  WHERE c.doctor_id = $doctor_id
    AND c.consultation_status IN('pending','accepted')
    AND c.consultation_date >= CURDATE()
  ORDER BY c.consultation_date, c.consultation_time
  LIMIT 5
";
$res_upcoming = mysqli_query($conn, $sql);

// --- Recent patient requests (pending only) ---
$sql = "
  SELECT c.id, u.full_name, s.symptom_name, s.symptom_severity, c.created_at
  FROM consultations c
  JOIN users u    ON u.id = c.user_id
  JOIN symptoms s ON s.id = c.symptom_id
  WHERE c.doctor_id = $doctor_id
    AND c.consultation_status = 'pending'
  ORDER BY c.created_at DESC
  LIMIT 5
";
$res_requests = mysqli_query($conn, $sql);

// --- Weekly schedule (Mon–Fri of this week) ---
$sql = "
  SELECT c.consultation_date, c.consultation_time, u.full_name
  FROM consultations c
  JOIN users u ON u.id = c.user_id
  WHERE c.doctor_id = $doctor_id
    AND c.consultation_status = 'accepted'
    AND c.consultation_date 
        BETWEEN DATE_SUB(CURDATE(),INTERVAL WEEKDAY(CURDATE()) DAY)
            AND DATE_ADD(
                DATE_SUB(CURDATE(),INTERVAL WEEKDAY(CURDATE()) DAY),
                INTERVAL 4 DAY
            )
  ORDER BY c.consultation_date, c.consultation_time
";
$res_schedule = mysqli_query($conn, $sql);

// Build schedule array keyed by weekday name
$schedule = [];
while ($row = mysqli_fetch_assoc($res_schedule)) {
    $day = date('l', strtotime($row['consultation_date']));
    $schedule[$day][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Doctor Dashboard – HealthBridge</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>HealthBridge</h2>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li class="active"><a href="dashboard.php">📊 Dashboard</a></li>
          <li><a href="consultations.php">👨‍⚕️ Consultations</a></li>
          <li><a href="patients.php">👥 Patients</a></li>
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
        <h1>Doctor Dashboard</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <img src="../images/doctor-avatar.png" alt="Avatar" class="user-avatar">
        </div>
      </header>

      <!-- Stats -->
      <div class="stats-container">
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-content">
            <h3>Total Patients</h3>
            <p class="stat-number"><?= $total_patients ?></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-content">
            <h3>Today's Appointments</h3>
            <p class="stat-number"><?= $today_appointments ?></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⏳</div>
          <div class="stat-content">
            <h3>Pending Requests</h3>
            <p class="stat-number"><?= $pending_requests ?></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-content">
            <h3>Completed This Week</h3>
            <p class="stat-number"><?= $completed_week ?></p>
          </div>
        </div>
      </div>

      <!-- Upcoming Consultations -->
      <div class="dashboard-summary">
        <div class="summary-card">
          <h3>Upcoming Consultations</h3>
          <div class="card-content">
            <?php if (mysqli_num_rows($res_upcoming) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($res_upcoming)): ?>
                <div class="consultation-item">
                  <div class="consultation-info">
                    <h4><?= htmlspecialchars($row['full_name']) ?></h4>
                    <p>Date: <span><?= $row['consultation_date'] ?></span></p>
                    <p>Time: <span><?= $row['consultation_time'] ?></span></p>
                    <p>Type: <span><?= ucfirst($row['consultation_type']) ?></span></p>
                    <p>Reason: <span><?= htmlspecialchars($row['symptom_name']) ?>, <?= ucfirst($row['symptom_severity']) ?></span></p>
                  </div>
                  <div class="consultation-actions">
                    <a href="consultation.php?id=<?= $row['id'] ?>" class="btn btn-primary">Start Consultation</a>
                    <a href="consultation.php?id=<?= $row['id'] ?>&action=reschedule" class="btn btn-outline">Reschedule</a>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p>No upcoming consultations.</p>
            <?php endif; ?>
            <a href="consultations.php" class="view-all">View all consultations →</a>
          </div>
        </div>

        <!-- Recent Requests -->
        <div class="summary-card">
          <h3>Recent Patient Requests</h3>
          <div class="card-content">
            <?php if (mysqli_num_rows($res_requests) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($res_requests)): ?>
                <div class="request-item">
                  <div class="request-info">
                    <h4><?= htmlspecialchars($row['full_name']) ?></h4>
                    <p>Symptom: <span><?= htmlspecialchars($row['symptom_name']) ?></span></p>
                    <p>Severity: <span class="severity <?= $row['symptom_severity'] ?>"><?= ucfirst($row['symptom_severity']) ?></span></p>
                    <p>Requested: <span><?= date('M d, Y', strtotime($row['created_at'])) ?></span></p>
                  </div>
                  <div class="request-actions">
                    <a href="requests.php?id=<?= $row['id'] ?>&action=accept" class="btn btn-primary">Accept</a>
                    <a href="requests.php?id=<?= $row['id'] ?>&action=decline" class="btn btn-outline">Decline</a>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p>No recent requests.</p>
            <?php endif; ?>
            <a href="requests.php" class="view-all">View all requests →</a>
          </div>
        </div>
      </div>

      <!-- Weekly Schedule -->
      <div class="weekly-schedule">
        <h3>Weekly Schedule</h3>
        <div class="schedule-container">
          <?php
          $days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
          foreach ($days as $dayName): ?>
            <div class="schedule-day">
              <h4><?= $dayName ?></h4>
              <div class="schedule-appointments">
                <?php if (!empty($schedule[$dayName])): ?>
                  <?php foreach ($schedule[$dayName] as $apt): ?>
                    <div class="appointment-item">
                      <span class="time"><?= $apt['consultation_time'] ?></span>
                      <span class="patient"><?= htmlspecialchars($apt['full_name']) ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p class="no-appointments">—</p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </main>
  </div>
  <script src="../js/doctor-dashboard.js"></script>
</body>
</html>
