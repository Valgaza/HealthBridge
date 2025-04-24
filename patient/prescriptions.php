<?php
require_once __DIR__ . '/../php/config.php';
require_patient();

$user_id = $_SESSION['user_id'];

// fetch user name
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

// fetch all prescriptions for this patient
$sql = "
  SELECT 
    p.*, 
    u.full_name AS doctor_name
  FROM prescriptions p
  JOIN users u ON p.doctor_id = u.id
  WHERE p.patient_id = ?
  ORDER BY p.prescription_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$active = $history = [];
$today = new DateTime();

while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['prescription_date']);
    // build interval spec
    switch ($row['duration_unit']) {
        case 'days':
            $intervalSpec = 'P' . $row['duration_value'] . 'D';
            break;
        case 'weeks':
            $intervalSpec = 'P' . ($row['duration_value'] * 7) . 'D';
            break;
        case 'months':
            $intervalSpec = 'P' . $row['duration_value'] . 'M';
            break;
        default:
            $intervalSpec = 'P0D';
    }
    $end = (clone $start)->add(new DateInterval($intervalSpec));
    if ($end >= $today) {
        $row['end_date'] = $end->format('Y-m-d');
        $active[] = $row;
    } else {
        $row['end_date'] = $end->format('Y-m-d');
        $history[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Prescriptions - HealthBridge</title>
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
        <li><a href="dashboard.php"><span class="icon">📊</span> Dashboard</a></li>
        <li><a href="symptoms.php"><span class="icon">🤒</span> Symptoms</a></li>
        <li><a href="health-tips.php"><span class="icon">💡</span> Health Tips</a></li>
        <li><a href="fitness-goals.php"><span class="icon">🏃</span> Fitness Goals</a></li>
        <li><a href="diet-plans.php"><span class="icon">🥗</span> Diet Plans</a></li>
        <li><a href="consultations.php"><span class="icon">👨‍⚕️</span> Consultations</a></li>
        <li class="active"><a href="prescriptions.php"><span class="icon">💊</span> Prescriptions</a></li>
      </ul>
    </nav>
    <div class="sidebar-footer">
      <a href="../php/logout.php" class="logout-btn"><span class="icon">🚪</span> Logout</a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <header class="content-header">
      <h1>Prescriptions</h1>
      <div class="user-info">
        <span class="user-name"><?= htmlspecialchars($full_name) ?></span>
        <img src="../images/avatar.png" alt="User Avatar" class="user-avatar">
      </div>
    </header>

    <div class="content-tabs">
      <button class="tab-btn active" data-tab="current">Current Medications</button>
      <button class="tab-btn" data-tab="history">Prescription History</button>
      <button class="tab-btn" data-tab="reminders">Medication Reminders</button>
    </div>

    <!-- Current -->
    <div class="tab-content active" id="current">
      <div class="medications-container">
        <?php if (empty($active)): ?>
          <p>No active prescriptions.</p>
        <?php else: ?>
          <?php foreach ($active as $rx): ?>
            <div class="medication-card">
              <div class="medication-header">
                <h3><?= htmlspecialchars($rx['medication']) ?></h3>
                <span class="medication-status active">Active</span>
              </div>
              <div class="medication-content">
                <div class="medication-details">
                  <div class="detail-item">
                    <span class="detail-label">Prescribed by:</span>
                    <span class="detail-value"><?= htmlspecialchars($rx['doctor_name']) ?></span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Date Prescribed:</span>
                    <span class="detail-value"><?= date('M j, Y', strtotime($rx['prescription_date'])) ?></span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value"><?= (int)$rx['duration_value'] . ' ' . ucfirst($rx['duration_unit']) ?></span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">End Date:</span>
                    <span class="detail-value"><?= date('M j, Y', strtotime($rx['end_date'])) ?></span>
                  </div>
                </div>
                <div class="medication-instructions">
                  <h4>Instructions:</h4>
                  <p><?= nl2br(htmlspecialchars($rx['instructions'])) ?></p>
                </div>
              </div>
              <div class="medication-footer">
                <button class="btn btn-secondary">Mark Dose as Taken</button>
                <button class="btn btn-outline">View Full Details</button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- History -->
    <div class="tab-content" id="history">
      <div class="table-container">
        <div class="table-actions">
          <div class="search-container">
            <input type="text" id="search-prescriptions" placeholder="Search prescriptions…">
          </div>
          <div class="filter-container">
            <select id="filter-date">
              <option value="">All Dates</option>
              <option value="month">Last Month</option>
              <option value="3months">Last 3 Months</option>
              <option value="year">Last Year</option>
            </select>
          </div>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Medication</th>
              <th>Dosage</th>
              <th>Prescribed By</th>
              <th>Date</th>
              <th>Duration</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($history)): ?>
              <tr><td colspan="7">No past prescriptions.</td></tr>
            <?php else: ?>
              <?php foreach ($history as $rx): ?>
                <tr>
                  <td><?= htmlspecialchars($rx['medication']) ?></td>
                  <td><?= htmlspecialchars($rx['dosage']) . ', ' . htmlspecialchars($rx['frequency']) ?></td>
                  <td><?= htmlspecialchars($rx['doctor_name']) ?></td>
                  <td><?= date('M j, Y', strtotime($rx['prescription_date'])) ?></td>
                  <td><?= (int)$rx['duration_value'] . ' ' . ucfirst($rx['duration_unit']) ?></td>
                  <td><span class="status completed">Completed</span></td>
                  <td><button class="btn btn-small">View Details</button></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Reminders (static for now) -->
    <div class="tab-content" id="reminders">
      <div class="reminders-container">
        <div class="reminder-settings-card">
          <div class="reminder-header"><h3>Medication Reminder Settings</h3></div>
          <div class="reminder-content">
            <form id="reminder-settings-form">
              <div class="form-group">
                <label for="reminder-method">Reminder Method</label>
                <select id="reminder-method" name="reminder_method">
                  <option value="app">App Notification</option>
                  <option value="email">Email</option>
                  <option value="both">Both App and Email</option>
                </select>
              </div>
              <div class="form-group">
                <label for="reminder-time">Reminder Time</label>
                <select id="reminder-time" name="reminder_time">
                  <option value="exact">At exact medication time</option>
                  <option value="15min">15 minutes before</option>
                  <option value="30min">30 minutes before</option>
                  <option value="1hour">1 hour before</option>
                </select>
              </div>
              <div class="form-group">
                <label for="missed-reminder">Missed Dose Reminder</label>
                <select id="missed-reminder" name="missed_reminder">
                  <option value="15min">After 15 minutes</option>
                  <option value="30min">After 30 minutes</option>
                  <option value="1hour">After 1 hour</option>
                  <option value="none">No reminder</option>
                </select>
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" id="daily-summary" name="daily_summary" checked>
                  Send daily medication summary
                </label>
              </div>
              <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
          </div>
        </div>

        <div class="today-reminders-card">
          <div class="reminder-header">
            <h3>Today's Medication Schedule</h3>
            <span class="current-date"><?= date('F j, Y') ?></span>
          </div>
          <div class="reminder-content">
            <p>Reminder timeline coming soon...</p>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<script src="../js/prescriptions.js"></script>
</body>
</html>
