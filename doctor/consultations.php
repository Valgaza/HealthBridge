<?php
require_once __DIR__ . '/../php/config.php';
require_doctor();
$doctor_id = $_SESSION['user_id'];
$today     = new DateTime();

// --- Today's Schedule ---
$sql_today = "
  SELECT c.id, u.full_name, c.consultation_time,
         c.consultation_type, s.symptom_name, s.symptom_severity,
         c.consultation_status
    FROM consultations c
    JOIN users u       ON u.id = c.user_id
    LEFT JOIN symptoms s ON s.id = c.symptom_id
   WHERE c.doctor_id       = ?
     AND c.consultation_date = CURDATE()
   ORDER BY c.consultation_time
";
$stmt      = $conn->prepare($sql_today);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$res_today = $stmt->get_result();
$stmt->close();

// --- Upcoming Consultations ---
$sql_upcoming = "
  SELECT c.id, c.consultation_date, c.consultation_time, u.full_name,
         s.symptom_name, s.symptom_severity, c.consultation_type
    FROM consultations c
    JOIN users u       ON u.id = c.user_id
    LEFT JOIN symptoms s ON s.id = c.symptom_id
   WHERE c.doctor_id       = ?
     AND c.consultation_date > CURDATE()
   ORDER BY c.consultation_date, c.consultation_time
";
$stmt           = $conn->prepare($sql_upcoming);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$res_upcoming   = $stmt->get_result();
$stmt->close();

// --- Past Consultations ---
$sql_past = "
  SELECT c.id, c.consultation_date, u.full_name, s.symptom_name,
         c.consultation_type, c.diagnosis, c.follow_up, c.follow_up_date
    FROM consultations c
    JOIN users u       ON u.id = c.user_id
    LEFT JOIN symptoms s ON s.id = c.symptom_id
   WHERE c.doctor_id       = ?
     AND c.consultation_date < CURDATE()
     AND c.consultation_status = 'completed'
   ORDER BY c.consultation_date DESC
";
$stmt       = $conn->prepare($sql_past);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$res_past   = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Consultations – HealthBridge</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="../images/logo.png" alt="HealthBridge Logo" class="logo">
        <h2>HealthBridge</h2>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="dashboard.php">📊 Dashboard</a></li>
          <li class="active"><a href="consultations.php">👨‍⚕️ Consultations</a></li>
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

    <!-- Main Content -->
    <main class="main-content">
      <header class="content-header">
        <h1>Consultations</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <img src="../images/doctor-avatar.png" alt="Doctor Avatar" class="user-avatar">
        </div>
      </header>

      <!-- Tabs -->
      <div class="content-tabs">
        <button class="tab-btn active" data-tab="today">Today's Schedule</button>
        <button class="tab-btn" data-tab="upcoming">Upcoming</button>
        <button class="tab-btn" data-tab="past">Past Consultations</button>
        <button class="tab-btn" data-tab="availability">Manage Availability</button>
      </div>

      <!-- Today's Schedule Tab -->
      <div class="tab-content active" id="today">
        <div class="today-schedule">
          <div class="schedule-header">
            <h3>Today's Appointments</h3>
            <span class="current-date"><?= date('F j, Y') ?></span>
          </div>
          <div class="timeline">
            <?php if ($res_today->num_rows > 0): ?>
              <?php while ($row = $res_today->fetch_assoc()): 
                $cls = $row['consultation_status']=='completed'
                     ? 'completed'
                     : ($row['consultation_status']=='pending' ? 'current' : '');
              ?>
              <div class="timeline-item <?= $cls ?>">
                <div class="timeline-time"><?= date('g:i A', strtotime($row['consultation_time'])) ?></div>
                <div class="timeline-content">
                  <div class="patient-info">
                    <img src="../images/avatar.png" class="patient-avatar">
                    <div class="patient-details">
                      <h4><?= htmlspecialchars($row['full_name']) ?></h4>
                      <p>
                        <?= htmlspecialchars($row['symptom_name'] ?: 'Consultation') ?>,
                        <?= ucfirst(htmlspecialchars($row['symptom_severity'] ?: 'N/A')) ?>
                      </p>
                      <span class="consultation-type <?= htmlspecialchars($row['consultation_type']) ?>">
                        <?= ucfirst(htmlspecialchars($row['consultation_type'])) ?>
                      </span>
                    </div>
                  </div>
                  <div class="timeline-actions">
                    <?php if ($row['consultation_status']=='completed'): ?>
                      <span class="timeline-status">Completed</span>
                      <button class="btn btn-small">View Notes</button>
                    <?php elseif ($row['consultation_status']=='pending'): ?>
                      <button class="btn btn-primary"
                              onclick="location.href='consultation.php?id=<?= $row['id'] ?>'">
                        Start Consultation
                      </button>
                      <button class="btn btn-outline">Reschedule</button>
                    <?php else: ?>
                      <button class="btn btn-outline">View Patient History</button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p class="no-appointments">No appointments today.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Upcoming Consultations Tab -->
      <div class="tab-content" id="upcoming">
        <div class="filter-bar">
          <div class="search-container">
            <input type="text" id="search-upcoming" placeholder="Search patients…">
          </div>
          <div class="filter-container">
            <select id="filter-date-range">
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="all">All Upcoming</option>
            </select>
            <select id="filter-type">
              <option value="">All Types</option>
              <option value="online">Online</option>
              <option value="in-person">In-Person</option>
            </select>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th><th>Time</th><th>Patient</th>
                <th>Reason</th><th>Type</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($res_upcoming->num_rows > 0): ?>
                <?php while ($row = $res_upcoming->fetch_assoc()): ?>
                <tr>
                  <td><?= date('M d, Y', strtotime($row['consultation_date'])) ?></td>
                  <td><?= date('g:i A', strtotime($row['consultation_time'])) ?></td>
                  <td><?= htmlspecialchars($row['full_name']) ?></td>
                  <td>
                    <?= htmlspecialchars($row['symptom_name'] ?: 'Consultation') ?>
                    <?= $row['symptom_severity']
                      ? ", ".ucfirst(htmlspecialchars($row['symptom_severity']))
                      : '' ?>
                  </td>
                  <td>
                    <span class="consultation-type <?= htmlspecialchars($row['consultation_type']) ?>">
                      <?= ucfirst(htmlspecialchars($row['consultation_type'])) ?>
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-small"
                            onclick="location.href='consultation.php?id=<?= $row['id'] ?>'">
                      View Details
                    </button>
                    <button class="btn btn-small btn-outline"
                            onclick="location.href='consultation.php?id=<?= $row['id'] ?>&action=reschedule'">
                      Reschedule
                    </button>
                  </td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6">No upcoming consultations.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Past Consultations Tab -->
      <div class="tab-content" id="past">
        <div class="filter-bar">
          <div class="search-container">
            <input type="text" id="search-past" placeholder="Search patients or diagnoses…">
          </div>
          <div class="filter-container">
            <select id="filter-past-date">
              <option value="week">Last Week</option>
              <option value="month">Last Month</option>
              <option value="3months">Last 3 Months</option>
              <option value="year">Last Year</option>
            </select>
            <select id="filter-past-type">
              <option value="">All Types</option>
              <option value="online">Online</option>
              <option value="in-person">In-Person</option>
            </select>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th><th>Patient</th><th>Reason</th>
                <th>Type</th><th>Diagnosis</th><th>Follow‑up</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($res_past->num_rows > 0): ?>
                <?php while ($row = $res_past->fetch_assoc()): ?>
                <tr>
                  <td><?= date('M d, Y', strtotime($row['consultation_date'])) ?></td>
                  <td><?= htmlspecialchars($row['full_name']) ?></td>
                  <td><?= htmlspecialchars($row['symptom_name'] ?: 'Consultation') ?></td>
                  <td>
                    <span class="consultation-type <?= htmlspecialchars($row['consultation_type']) ?>">
                      <?= ucfirst(htmlspecialchars($row['consultation_type'])) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($row['diagnosis'] ?: '—') ?></td>
                  <td>
                    <?php if ($row['follow_up']=='yes'): ?>
                      Yes – <?= date('M d, Y', strtotime($row['follow_up_date'])) ?>
                    <?php else: ?>
                      No
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn btn-small"
                            onclick="location.href='consultation.php?id=<?= $row['id'] ?>'">
                      View Details
                    </button>
                  </td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7">No past consultations.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Manage Availability Tab -->
      <div class="tab-content" id="availability">
        <div class="availability-container">
          <!-- your availability UI here -->
        </div>
      </div>
    </main>
  </div>

  <script src="../js/doctor-consultations.js"></script>
</body>
</html>
