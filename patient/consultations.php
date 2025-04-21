<?php
require_once __DIR__ . '/../php/config.php';
require_patient();

$user_id = $_SESSION['user_id'];
$today = new DateTime();

// Fetch patient name (for header)
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($patient_name);
$stmt->fetch();
$stmt->close();

// Fetch consultations
$sql = "
    SELECT c.*, 
           u.full_name AS doctor_name, 
           u.specialization, 
           s.symptom_name, 
           s.symptom_severity
    FROM consultations c
    LEFT JOIN users u ON c.doctor_id = u.id
    LEFT JOIN symptoms s ON c.symptom_id = s.id
    WHERE c.user_id = ?
    ORDER BY c.consultation_date DESC, c.consultation_time DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$upcoming = $pending = $past = [];
while ($row = $result->fetch_assoc()) {
    $cdate = new DateTime($row['consultation_date']);
    if ($row['consultation_status'] === 'pending') {
        $pending[] = $row;
    } elseif ($row['consultation_status'] === 'accepted' && $cdate >= $today) {
        $upcoming[] = $row;
    } elseif ($row['consultation_status'] === 'completed') {
        $past[] = $row;
    }
}
$stmt->close();

// Fetch symptoms for the "related symptom" dropdown
$stmt = $conn->prepare("SELECT id, symptom_name, symptom_date FROM symptoms WHERE user_id = ? ORDER BY symptom_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$symptoms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Consultations - HealthBridge</title>
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
          <li><a href="dashboard.php">📊 Dashboard</a></li>
          <li><a href="symptoms.php">🤒 Symptoms</a></li>
          <li><a href="health-tips.php">💡 Health Tips</a></li>
          <li><a href="fitness-goals.php">🏃 Fitness Goals</a></li>
          <li><a href="diet-plans.php">🥗 Diet Plans</a></li>
          <li class="active"><a href="consultations.php">👨‍⚕️ Consultations</a></li>
          <li><a href="prescriptions.php">💊 Prescriptions</a></li>
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
          <span class="user-name"><?= htmlspecialchars($patient_name) ?></span>
          <img src="../images/avatar.png" alt="User Avatar" class="user-avatar">
        </div>
      </header>

      <div class="content-tabs">
        <button class="tab-btn active" data-tab="upcoming">Upcoming</button>
        <button class="tab-btn" data-tab="pending">Pending Requests</button>
        <button class="tab-btn" data-tab="past">Past Consultations</button>
        <button class="tab-btn" data-tab="book-new">Book New Consultation</button>
      </div>

      <!-- Upcoming -->
      <div class="tab-content active" id="upcoming">
        <div class="consultations-container">
          <?php if (empty($upcoming)): ?>
            <p>No upcoming consultations.</p>
          <?php else: foreach($upcoming as $c): ?>
            <div class="consultation-card">
              <div class="consultation-header">
                <h3><?= htmlspecialchars($c['specialization']) ?> Consultation</h3>
                <span class="consultation-type <?= $c['consultation_type'] ?>">
                  <?= ucfirst($c['consultation_type']) ?>
                </span>
              </div>
              <div class="consultation-content">
                <div class="doctor-info">
                  <img src="../images/doctor-avatar.png" alt="Doctor Avatar" class="doctor-avatar">
                  <div class="doctor-details">
                    <h4><?= htmlspecialchars($c['doctor_name']) ?></h4>
                    <p><?= htmlspecialchars($c['specialization']) ?></p>
                  </div>
                </div>
                <div class="consultation-details">
                  <div class="detail-item">📅 <?= date('F j, Y', strtotime($c['consultation_date'])) ?></div>
                  <div class="detail-item">⏰ <?= date('g:i A', strtotime($c['consultation_time'])) ?></div>
                  <div class="detail-item">🔍 Reason: <?= htmlspecialchars($c['symptom_name'] . ', ' . ucfirst($c['symptom_severity'])) ?></div>
                </div>
                <div class="consultation-actions">
                  <button class="btn btn-primary">Join Consultation</button>
                  <button class="btn btn-secondary">Reschedule</button>
                  <button class="btn btn-outline">Cancel</button>
                </div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- Pending -->
      <div class="tab-content" id="pending">
        <div class="consultations-container">
          <?php if (empty($pending)): ?>
            <p>No pending requests.</p>
          <?php else: foreach($pending as $c): ?>
            <div class="consultation-card">
              <div class="consultation-header">
                <h3><?= htmlspecialchars($c['specialization'] ?: 'General') ?> Consultation</h3>
                <span class="consultation-status pending">Pending</span>
              </div>
              <div class="consultation-content">
                <div class="consultation-details">
                  <div class="detail-item">📝 Requested: <?= date('F j, Y', strtotime($c['consultation_date'])) ?></div>
                  <div class="detail-item">👨‍⚕️ Doctor Type: <?= htmlspecialchars(ucwords($c['specialization'] ?: $c['doctor_type'])) ?></div>
                  <div class="detail-item">🔍 Reason: <?= htmlspecialchars($c['symptom_name'] . ', ' . ucfirst($c['symptom_severity'])) ?></div>
                  <div class="detail-item">🏥 Preferred Type: <?= ucfirst($c['consultation_type']) ?></div>
                </div>
                <div class="consultation-status-message">
                  <p>Your request is being processed. A doctor will be assigned shortly.</p>
                </div>
                <div class="consultation-actions">
                  <button class="btn btn-outline">Cancel Request</button>
                </div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- Past -->
      <div class="tab-content" id="past">
        <div class="table-container">
          <?php if (empty($past)): ?>
            <p>No past consultations.</p>
          <?php else: ?>
          <table class="data-table">
            <thead>
              <tr><th>Date</th><th>Doctor</th><th>Specialty</th><th>Reason</th><th>Type</th><th>Diagnosis</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach($past as $c): ?>
              <tr>
                <td><?= date('F j, Y', strtotime($c['consultation_date'])) ?></td>
                <td><?= htmlspecialchars($c['doctor_name']) ?></td>
                <td><?= htmlspecialchars($c['specialization']) ?></td>
                <td><?= htmlspecialchars($c['symptom_name'] . ', ' . ucfirst($c['symptom_severity'])) ?></td>
                <td><?= ucfirst($c['consultation_type']) ?></td>
                <td><?= htmlspecialchars($c['diagnosis']) ?></td>
                <td><button class="btn btn-small">View Details</button></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- Book New -->
      <div class="tab-content" id="book-new">
        <div class="form-card">
          <h3>Book a New Consultation</h3>
          <form action="../php/book_consultation.php" method="post" id="consultation-form">
            <div class="form-row">
              <div class="form-group">
                <label for="doctor-type">Doctor Specialty</label>
                <select id="doctor-type" name="doctor_type" required>
                  <option value="">Select a specialty</option>
                  <?php foreach(['general','cardiology','neurology','dermatology','orthopedics','pediatrics','psychiatry'] as $spec): ?>
                    <option value="<?= $spec ?>"><?= ucfirst($spec) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="consultation-type">Consultation Type</label>
                <select id="consultation-type" name="consultation_type" required>
                  <option value="online">Online</option>
                  <option value="in-person">In-Person</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="preferred-date">Preferred Date</label>
                <input type="date" id="preferred-date" name="preferred_date" required>
              </div>
              <div class="form-group">
                <label for="preferred-time">Preferred Time</label>
                <select id="preferred-time" name="preferred_time" required>
                  <option value="">Select a time</option>
                  <option value="morning">Morning (9 AM - 12 PM)</option>
                  <option value="afternoon">Afternoon (1 PM - 5 PM)</option>
                  <option value="evening">Evening (6 PM - 8 PM)</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="reason">Reason for Consultation</label>
              <textarea id="reason" name="reason" rows="4" required></textarea>
            </div>
            <div class="form-group">
              <label for="symptom-relation">Related to Previous Symptom?</label>
              <select id="symptom-relation" name="symptom_relation">
                <option value="no">No</option>
                <option value="yes">Yes</option>
              </select>
            </div>
            <div class="form-group symptom-select" style="display:none;">
              <label for="related-symptom">Select Related Symptom</label>
              <select id="related-symptom" name="related_symptom">
                <option value="">Select a symptom</option>
                <?php foreach($symptoms as $s): ?>
                  <option value="<?= $s['id'] ?>">
                    <?= htmlspecialchars("{$s['symptom_name']} ({$s['symptom_date']})") ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="additional-notes">Additional Notes</label>
              <textarea id="additional-notes" name="additional_notes" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Book Consultation</button>
          </form>
        </div>
      </div>
    </main>
  </div>
  <script src="../js/consultations-patient.js"></script>
</body>
</html>
