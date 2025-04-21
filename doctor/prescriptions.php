<?php
require_once __DIR__ . '/../php/config.php';
require_doctor();
$doctor_id = $_SESSION['user_id'];

// --- Recent prescriptions by this doctor ---
$sql_recent = "
  SELECT p.*, u.full_name AS patient_name
    FROM prescriptions p
    JOIN users u ON u.id = p.patient_id
   WHERE p.doctor_id = ?
   ORDER BY p.prescription_date DESC
";
$stmt = $conn->prepare($sql_recent);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$res_recent = $stmt->get_result();
$stmt->close();

// --- Patients list for form dropdown ---
$sql_patients = "
  SELECT id, full_name
    FROM users
   WHERE user_type = 'patient'
   ORDER BY full_name
";
$res_patients = mysqli_query($conn, $sql_patients);

// --- Consultations list for form dropdown ---
$sql_consults = "
  SELECT c.id
       , c.consultation_date
       , c.consultation_time
       , u.full_name   AS patient_name
       , s.symptom_name
    FROM consultations c
    JOIN users      u ON u.id = c.user_id
    LEFT JOIN symptoms s ON s.id = c.symptom_id
   WHERE c.doctor_id = ?
   ORDER BY c.consultation_date DESC, c.consultation_time DESC
";
$stmt = $conn->prepare($sql_consults);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$res_consults = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Prescriptions – HealthBridge</title>
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
          <li><a href="patients.php">👥 Patients</a></li>
          <li><a href="health-tips.php">💡 Health Tips</a></li>
          <li class="active"><a href="prescriptions.php">💊 Prescriptions</a></li>
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
        <h1>Prescriptions</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <img src="../images/doctor-avatar.png" alt="Doctor Avatar" class="user-avatar">
        </div>
      </header>

      <!-- Tabs -->
      <div class="content-tabs">
        <button class="tab-btn active" data-tab="recent">Recent Prescriptions</button>
        <button class="tab-btn" data-tab="create">Create Prescription</button>
        <button class="tab-btn" data-tab="templates">Templates</button>
      </div>

      <!-- Recent Prescriptions -->
      <div class="tab-content active" id="recent">
        <div class="filter-bar">
          <div class="search-container">
            <input type="text" id="search-prescriptions" placeholder="Search by patient or medication…">
          </div>
          <div class="filter-container">
            <select id="filter-date">
              <option value="">All Dates</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
            </select>
            <select id="filter-status">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Medication</th>
                <th>Dosage</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($res_recent->num_rows === 0): ?>
              <tr><td colspan="7">No prescriptions found.</td></tr>
            <?php else: ?>
              <?php
                $now = new DateTime;
                while ($p = $res_recent->fetch_assoc()):
                  // compute the end date via simple if/elseif
                  $start = new DateTime($p['prescription_date']);
                  if ($p['duration_unit'] === 'days') {
                    $intervalSpec = "P{$p['duration_value']}D";
                  } elseif ($p['duration_unit'] === 'weeks') {
                    $intervalSpec = "P" . ($p['duration_value'] * 7) . "D";
                  } elseif ($p['duration_unit'] === 'months') {
                    $intervalSpec = "P{$p['duration_value']}M";
                  } else {
                    $intervalSpec = "P0D";
                  }
                  $end = (clone $start)->add(new DateInterval($intervalSpec));
                  $status = ($end >= $now) ? 'active' : 'completed';
              ?>
              <tr class="prescription-row"
                  data-patient="<?= htmlspecialchars(strtolower($p['patient_name'])) ?>"
                  data-med="<?= htmlspecialchars(strtolower($p['medication'])) ?>"
                  data-date="<?= $start->format('Y-m-d') ?>"
                  data-status="<?= $status ?>">
                <td><?= $start->format('M j, Y') ?></td>
                <td><?= htmlspecialchars($p['patient_name']) ?></td>
                <td><?= htmlspecialchars($p['medication']) ?></td>
                <td><?= htmlspecialchars($p['dosage']) ?>, <?= htmlspecialchars($p['frequency']) ?></td>
                <td><?= (int)$p['duration_value'] ?> <?= ucfirst($p['duration_unit']) ?></td>
                <td><span class="status <?= $status ?>"><?= ucfirst($status) ?></span></td>
                <td>
                  <a href="prescription-detail.php?id=<?= $p['id'] ?>" class="btn btn-small">View</a>
                  <a href="prescriptions.php?edit=<?= $p['id'] ?>" class="btn btn-small btn-outline">Edit</a>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Create Prescription -->
      <div class="tab-content" id="create">
        <div class="form-card">
          <h3>Create a New Prescription</h3>
          <form action="../php/save_prescription.php" method="post" id="prescription-form">
            <div class="form-row">
              <div class="form-group">
                <label for="patient-select">Patient</label>
                <select id="patient-select" name="patient_id" required>
                  <option value="">Select a patient…</option>
                  <?php while ($pt = mysqli_fetch_assoc($res_patients)): ?>
                    <option value="<?= $pt['id'] ?>"><?= htmlspecialchars($pt['full_name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="consultation-select">Consultation (optional)</label>
                <select id="consultation-select" name="consultation_id">
                  <option value="">Select a consultation…</option>
                  <?php while ($c = $res_consults->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>">
                      <?= date('M j, Y', strtotime($c['consultation_date'])) ?>
                      – <?= htmlspecialchars($c['patient_name']) ?>
                      (<?= htmlspecialchars($c['symptom_name'] ?: 'Consultation') ?>)
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
            </div>

            <div class="prescription-medications">
              <h4>Medications</h4>
              <div class="medication-form">
                <div class="form-group">
                  <label>Medication</label>
                  <input type="text" name="medication[]" required>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Dosage</label>
                    <input type="text" name="dosage[]" required>
                  </div>
                  <div class="form-group">
                    <label>Frequency</label>
                    <select name="frequency[]" required>
                      <option value="once">Once daily</option>
                      <option value="twice">Twice daily</option>
                      <option value="three">Three times daily</option>
                      <option value="as-needed">As needed</option>
                    </select>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Duration</label>
                    <input type="number" name="duration_value[]" min="1" value="7" required>
                    <select name="duration_unit[]">
                      <option value="days">Days</option>
                      <option value="weeks">Weeks</option>
                      <option value="months">Months</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Instructions</label>
                    <textarea name="instructions[]" rows="2"></textarea>
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn-secondary add-medication">+ Add Medication</button>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Save Prescription</button>
              <button type="button" class="btn btn-outline" id="save-as-template">Save as Template</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Templates Tab -->
      <div class="tab-content" id="templates">
        <div class="templates-container">
          <p>No prescription templates available. Use “Save as Template” in the form above to add templates.</p>
        </div>
      </div>
    </main>
  </div>

  <script src="../js/doctor-prescriptions.js"></script>
</body>
</html>
