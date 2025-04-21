<?php
require_once __DIR__ . '/../php/config.php';
require_patient();

$patient_id = $_SESSION['user_id'];

// Fetch patient name
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$stmt->bind_result($patient_name);
$stmt->fetch();
$stmt->close();

// Fetch goals by status
$statuses = [
  'active'    => 'Current Goals',
  'completed' => 'Completed Goals',
];
$goals_results = [];

foreach ($statuses as $status_key => $status_title) {
    $stmt = $conn->prepare("
      SELECT id, goal_title, goal_description, start_date, end_date, status
        FROM fitness_goals
       WHERE patient_id = ?
         AND status      = ?
       ORDER BY created_at DESC
    ");
    $stmt->bind_param('is', $patient_id, $status_key);
    $stmt->execute();
    $result = $stmt->get_result();
    $goals_results[$status_key] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch full history (for the “Goal History” tab)
$stmt = $conn->prepare("
  SELECT
    id,
    goal_title,
    start_date,
    end_date,
    status,
    CONCAT(
      ROUND(
        (
          DATEDIFF(COALESCE(end_date, CURDATE()), start_date)
          / DATEDIFF(COALESCE(end_date, CURDATE()), start_date)
        ) * 100, 0
      ), '%'
    ) AS completion
    FROM fitness_goals
   WHERE patient_id = ?
   ORDER BY start_date DESC
");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Fitness Goals – HealthBridge</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
  <div class="dashboard-container">
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
          <li class="active"><a href="fitness-goals.php">🏃 Fitness Goals</a></li>
          <li><a href="diet-plans.php">🥗 Diet Plans</a></li>
          <li><a href="consultations.php">👨‍⚕️ Consultations</a></li>
          <li><a href="prescriptions.php">💊 Prescriptions</a></li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="../php/logout.php" class="logout-btn">🚪 Logout</a>
      </div>
    </aside>
    <main class="main-content">
      <header class="content-header">
        <h1>Fitness Goals</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($patient_name) ?></span>
          <img src="../images/avatar.png" alt="User Avatar" class="user-avatar">
        </div>
      </header>

      <div class="content-tabs">
        <button class="tab-btn active" data-tab="active-goals">Current Goals</button>
        <button class="tab-btn" data-tab="completed-goals">Completed Goals</button>
        <button class="tab-btn" data-tab="goal-history">Goal History</button>
      </div>

      <?php foreach ($statuses as $status_key => $status_title): 
        $tab_id       = $status_key . '-goals';
        $is_active    = $status_key === 'active';
        $active_class = $is_active ? ' active' : '';
      ?>
      <div class="tab-content<?= $active_class ?>" id="<?= $tab_id ?>">
        <div class="goals-container">
          <?php foreach ($goals_results[$status_key] as $goal): ?>
          <div class="goal-card">
            <div class="goal-header">
              <h3><?= htmlspecialchars($goal['goal_title']) ?></h3>
              <span class="goal-status <?= $goal['status'] ?>">
                <?= ucfirst($goal['status']) ?>
              </span>
            </div>
            <div class="goal-content">
              <p><strong>Description:</strong><br>
                <?= nl2br(htmlspecialchars($goal['goal_description'])) ?>
              </p>
              <div class="goal-details">
                <div class="goal-detail">
                  <span class="detail-label">Start Date:</span>
                  <?= htmlspecialchars($goal['start_date']) ?>
                </div>
                <div class="goal-detail">
                  <span class="detail-label">
                    <?= $is_active ? 'Target Date' : 'Completion Date' ?>:
                  </span>
                  <?= htmlspecialchars($goal['end_date'] ?: 'Ongoing') ?>
                </div>
              </div>
              <?php if ($is_active): ?>
              <div class="goal-progress">
                <h4>Progress:</h4>
                <div class="progress-bar-container">
                  <div class="progress-bar" style="width:0%;">0%</div>
                </div>
              </div>
              <?php endif; ?>
            </div>

            <!-- Updated footer: Mark Complete on active, Request Modification on completed -->
            <div class="goal-footer">
              <?php if ($is_active): ?>
                <a 
                  href="update_goal.php?id=<?= (int)$goal['id'] ?>" 
                  class="btn btn-secondary"
                  onclick="return confirm('Mark “<?= htmlspecialchars($goal['goal_title']) ?>” as completed?');"
                >
                  Mark Complete
                </a>
              <?php else: ?>
                <a 
                  href="update_goal.php?id=<?= (int)$goal['id'] ?>&action=reopen" 
                  class="btn btn-outline"
                  onclick="return confirm('Re‑open “<?= htmlspecialchars($goal['goal_title']) ?>”?');"
                >
                  Request Modification
                </a>
              <?php endif; ?>
            </div>

          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <!-- Goal History panel -->
      <div class="tab-content" id="goal-history">
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Goal Title</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Completion</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($history as $h): ?>
              <tr>
                <td><?= htmlspecialchars($h['goal_title']) ?></td>
                <td><?= htmlspecialchars($h['start_date']) ?></td>
                <td><?= htmlspecialchars($h['end_date'] ?: 'Ongoing') ?></td>
                <td><?= ucfirst(htmlspecialchars($h['status'])) ?></td>
                <td><?= htmlspecialchars($h['completion']) ?></td>
                <td><button class="btn btn-small">View Details</button></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script src="../js/fitness-goals.js"></script>
</body>
</html>
