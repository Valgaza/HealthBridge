<?php
require_once __DIR__ . '/../php/config.php';
require_patient();

$user_id = $_SESSION['user_id'];

// Fetch user full name
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

// Fetch symptom history
$symptoms = [];
$sql = "SELECT id, symptom_name, symptom_date, symptom_time, symptom_severity, doctor_type 
        FROM symptoms 
        WHERE user_id = ? 
        ORDER BY symptom_date DESC, symptom_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $symptoms[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Symptoms - HealthBridge</title>
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
          <li class="active"><a href="symptoms.php"><span class="icon">🤒</span> Symptoms</a></li>
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
        <h1>Symptoms</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($full_name) ?></span>
          <img src="../images/avatar.png" alt="User Avatar" class="user-avatar">
        </div>
      </header>

      <div class="content-tabs">
        <button class="tab-btn active" data-tab="log-symptom">Log New Symptom</button>
        <button class="tab-btn" data-tab="symptom-history">Symptom History</button>
      </div>

      <!-- Log New Symptom -->
      <div class="tab-content active" id="log-symptom">
        <div class="form-card">
          <h3>Log a New Symptom</h3>
          <form action="../php/log_symptom.php" method="post" id="symptom-form">
            <div class="form-row">
              <div class="form-group">
                <label for="symptom-name">Symptom</label>
                <input type="text" id="symptom-name" name="symptom_name" required placeholder="e.g., Headache, Fever, Cough">
              </div>
              <div class="form-group">
                <label for="symptom-date">Date</label>
                <input type="date" id="symptom-date" name="symptom_date" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="symptom-time">Time</label>
                <input type="time" id="symptom-time" name="symptom_time" required>
              </div>
              <div class="form-group">
                <label for="symptom-severity">Severity</label>
                <select id="symptom-severity" name="symptom_severity" required>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="doctor-type">Doctor Type</label>
              <select id="doctor-type" name="doctor_type">
                <option value="">Not sure (recommend me)</option>
                <option value="general">General Physician</option>
                <option value="cardiology">Cardiologist</option>
                <option value="neurology">Neurologist</option>
                <option value="dermatology">Dermatologist</option>
                <option value="orthopedics">Orthopedic</option>
                <option value="pediatrics">Pediatrician</option>
                <option value="psychiatry">Psychiatrist</option>
              </select>
            </div>
            <div class="form-group">
              <label for="symptom-notes">Notes</label>
              <textarea id="symptom-notes" name="symptom_notes" rows="4" placeholder="Describe your symptoms in detail..."></textarea>
            </div>
            <div class="form-actions">
              <button type="submit" name="action" value="log" class="btn btn-secondary">Log Symptom Only</button>
              <button type="submit" name="action" value="consult" class="btn btn-primary">Log & Book Consultation</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Symptom History -->
      <div class="tab-content" id="symptom-history">
        <div class="table-container">
          <div class="table-actions">
            <div class="search-container">
              <input type="text" id="search-symptoms" placeholder="Search symptoms…">
            </div>
            <div class="filter-container">
              <select id="filter-severity">
                <option value="">All Severities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
              <select id="filter-date">
                <option value="">All Dates</option>
                <option value="week">Last Week</option>
                <option value="month">Last Month</option>
                <option value="year">Last Year</option>
              </select>
            </div>
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>Symptom</th>
                <th>Date</th>
                <th>Time</th>
                <th>Severity</th>
                <th>Doctor Type</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($symptoms as $s): ?>
                <tr>
                  <td><?= htmlspecialchars($s['symptom_name']) ?></td>
                  <td><?= date('M j, Y', strtotime($s['symptom_date'])) ?></td>
                  <td><?= date('g:i A', strtotime($s['symptom_time'])) ?></td>
                  <td>
                    <span class="severity <?= $s['symptom_severity'] ?>">
                      <?= ucfirst($s['symptom_severity']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($s['doctor_type'] ?: '—') ?></td>
                  <td>
                    <button class="btn btn-small">View Details</button>
                    <button class="btn btn-small btn-secondary">Book Consultation</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script src="../js/symptoms.js"></script>
</body>
</html>
