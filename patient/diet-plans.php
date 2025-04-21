<?php
require_once __DIR__ . '/../php/config.php';
require_patient();

$patient_id = $_SESSION['user_id'];

// Fetch patient name
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

// Fetch active diet plans
$stmt = $conn->prepare("
    SELECT id, plan_title, plan_description, start_date, end_date, doctor_id, meal_suggestions, status
    FROM diet_plans
    WHERE patient_id = ?
      AND status = 'active'
    ORDER BY start_date DESC
");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$res = $stmt->get_result();
$plans = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Diet Plans – HealthBridge</title>
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
          <li><a href="symptoms.php">🤒 Symptoms</a></li>
          <li><a href="health-tips.php">💡 Health Tips</a></li>
          <li><a href="fitness-goals.php">🏃 Fitness Goals</a></li>
          <li class="active"><a href="diet-plans.php">🥗 Diet Plans</a></li>
          <li><a href="consultations.php">👨‍⚕️ Consultations</a></li>
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
        <h1>Diet Plans</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($full_name) ?></span>
          <img src="../images/avatar.png" alt="User Avatar" class="user-avatar">
        </div>
      </header>

      <div class="content-tabs">
        <button class="tab-btn active" data-tab="current-plans">Current Plans</button>
        <button class="tab-btn" data-tab="meal-suggestions">Meal Suggestions</button>
        <button class="tab-btn" data-tab="dietary-restrictions">Dietary Restrictions</button>
      </div>

      <!-- Current Plans Tab -->
      <div class="tab-content active" id="current-plans">
        <div class="diet-plans-container">
          <?php if (empty($plans)): ?>
            <p>No active diet plans found.</p>
          <?php else: foreach ($plans as $plan): ?>
            <div class="diet-plan-card">
              <div class="plan-header">
                <h3><?= htmlspecialchars($plan['plan_title']) ?></h3>
                <span class="plan-status <?= htmlspecialchars($plan['status']) ?>">
                  <?= ucfirst($plan['status']) ?>
                </span>
              </div>
              <div class="plan-content">
                <p>
                  <strong>Description:</strong>
                  <?= nl2br(htmlspecialchars($plan['plan_description'])) ?>
                </p>
                <div class="plan-details">
                  <div class="plan-detail">
                    <span class="detail-label">Start Date:</span>
                    <span class="detail-value">
                      <?= date('F j, Y', strtotime($plan['start_date'])) ?>
                    </span>
                  </div>
                  <?php if ($plan['end_date']): ?>
                    <div class="plan-detail">
                      <span class="detail-label">End Date:</span>
                      <span class="detail-value">
                        <?= date('F j, Y', strtotime($plan['end_date'])) ?>
                      </span>
                    </div>
                  <?php endif; ?>
                  <div class="plan-detail">
                    <span class="detail-label">Recommended by:</span>
                    <span class="detail-value">
                      <?php
                        // fetch doctor name
                        $d = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
                        $d->bind_param('i', $plan['doctor_id']);
                        $d->execute();
                        $d->bind_result($doctor_name);
                        $d->fetch();
                        $d->close();
                        echo htmlspecialchars($doctor_name);
                      ?>
                    </span>
                  </div>
                </div>
                <div class="plan-guidelines">
                  <h4>Key Guidelines:</h4>
                  <ul>
                    <?php foreach (explode("\n", $plan['meal_suggestions']) as $item): ?>
                      <li><?= htmlspecialchars(trim($item)) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
              <div class="plan-footer">
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- Meal Suggestions Tab -->
      <div class="tab-content" id="meal-suggestions">
        <p>Meal suggestions will be displayed here.</p>
      </div>

      <!-- Dietary Restrictions Tab -->
      <div class="tab-content" id="dietary-restrictions">
        <p>Dietary restrictions will be displayed here.</p>
      </div>
    </main>
  </div>

  <script src="../js/diet-plans.js"></script>
</body>
</html>
