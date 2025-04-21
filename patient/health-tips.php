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

// Fetch health tips visible to this patient
$sql = "
  SELECT 
    ht.tip_title, 
    ht.tip_category, 
    ht.tip_content, 
    ht.tip_date, 
    ht.visibility,
    u.full_name AS doctor_name
  FROM health_tips ht
  JOIN users u ON ht.doctor_id = u.id
  WHERE ht.visibility = 'public'
     OR (ht.visibility = 'patient' AND ht.patient_id = ?)
  ORDER BY ht.tip_date DESC
";
$tips = [];
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $tips[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Health Tips - HealthBridge</title>
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
          <li class="active"><a href="health-tips.php"><span class="icon">💡</span> Health Tips</a></li>
          <li><a href="fitness-goals.php"><span class="icon">🏃</span> Fitness Goals</a></li>
          <li><a href="diet-plans.php"><span class="icon">🥗</span> Diet Plans</a></li>
          <li><a href="consultations.php"><span class="icon">👨‍⚕️</span> Consultations</a></li>
          <li><a href="prescriptions.php"><span class="icon">💊</span> Prescriptions</a></li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="../php/logout.php" class="logout-btn"><span class="icon">🚪</span> Logout</a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="content-header">
        <h1>Health Tips</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($full_name) ?></span>
          <img src="../images/avatar.png" alt="User Avatar" class="user-avatar">
        </div>
      </header>

      <div class="filter-bar">
        <div class="search-container">
          <input type="text" id="search-tips" placeholder="Search health tips…">
        </div>
        <div class="filter-container">
          <select id="filter-category">
            <option value="">All Categories</option>
            <option value="general">General Health</option>
            <option value="diet">Diet & Nutrition</option>
            <option value="exercise">Exercise & Fitness</option>
            <option value="mental">Mental Health</option>
            <option value="sleep">Sleep</option>
            <option value="medication">Medication</option>
            <option value="neurological">Neurological</option>
            <option value="digestive">Digestive</option>
          </select>
          <select id="filter-doctor">
            <option value="">All Doctors</option>
            <?php
            // build unique list of doctors
            $doctors = array_unique(array_column($tips, 'doctor_name'));
            foreach ($doctors as $docName): ?>
              <option value="<?= strtolower(str_replace(' ', '-', $docName)) ?>">
                <?= htmlspecialchars($docName) ?>
              </option>
            <?php endforeach; ?>
        </select>
        </div>
      </div>

      <div class="health-tips-grid">
        <?php foreach ($tips as $tip): ?>
          <?php 
            $cat = htmlspecialchars($tip['tip_category']);
            $vis = $tip['visibility'] === 'public' ? 'public' : 'patient';
            $docSlug = strtolower(str_replace(' ', '-', $tip['doctor_name']));
          ?>
          <div class="health-tip-card" 
               data-category="<?= $cat ?>" 
               data-doctor="<?= $docSlug ?>">
            <div class="tip-header">
              <h3><?= htmlspecialchars($tip['tip_title']) ?></h3>
              <span class="tip-category"><?= ucfirst($cat) ?></span>
              <span class="tip-visibility <?= $vis ?>">
                <?= $vis === 'public' ? 'Public' : 'For You' ?>
              </span>
            </div>
            <div class="tip-content">
              <p><?= nl2br(htmlspecialchars($tip['tip_content'])) ?></p>
            </div>
            <div class="tip-footer">
              <div class="doctor-info">
                <img src="../images/doctor-avatar.png" alt="Doctor Avatar" class="doctor-avatar">
                <span><?= htmlspecialchars($tip['doctor_name']) ?></span>
              </div>
              <button class="btn btn-small btn-secondary">Book Consultation</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

  <script src="../js/health-tips.js"></script>
</body>
</html>
