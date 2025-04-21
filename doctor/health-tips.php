<?php
require_once __DIR__ . '/../php/config.php';
require_doctor();
$doctor_id = $_SESSION['user_id'];

// --- My Health Tips ---
$sql_my_tips = "
  SELECT ht.*, u.full_name AS patient_name
    FROM health_tips ht
    LEFT JOIN users u ON u.id = ht.patient_id
   WHERE ht.doctor_id = ?
   ORDER BY ht.tip_date DESC
";
$stmt = $conn->prepare($sql_my_tips);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$res_my_tips = $stmt->get_result();
$stmt->close();

// --- Patients for “patient‑specific” dropdown ---
$sql_patients = "
  SELECT id, full_name
    FROM users
   WHERE user_type = 'patient'
   ORDER BY full_name
";
$res_patients = mysqli_query($conn, $sql_patients);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Health Tips – HealthBridge</title>
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
          <li class="active"><a href="health-tips.php">💡 Health Tips</a></li>
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
        <h1>Health Tips</h1>
        <div class="user-info">
          <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          <img src="../images/doctor-avatar.png" alt="Avatar" class="user-avatar">
        </div>
      </header>

      <!-- Tabs -->
      <div class="content-tabs">
        <button class="tab-btn active" data-tab="my-tips">My Health Tips</button>
        <button class="tab-btn" data-tab="create-tip">Create New Tip</button>
        <button class="tab-btn" data-tab="tip-templates">Templates</button>
      </div>

      <!-- My Health Tips -->
      <div class="tab-content active" id="my-tips">
        <div class="filter-bar">
          <div class="search-container">
            <input type="text" id="search-tips" placeholder="Search health tips…">
          </div>
          <div class="filter-container">
            <select id="filter-category">
              <option value="">All Categories</option>
              <option value="general">General Health</option>
              <option value="diet">Diet &amp; Nutrition</option>
              <option value="exercise">Exercise &amp; Fitness</option>
              <option value="mental">Mental Health</option>
              <option value="sleep">Sleep</option>
              <option value="medication">Medication</option>
              <option value="neurological">Neurological</option>
              <option value="digestive">Digestive</option>
            </select>
            <select id="filter-visibility">
              <option value="">All Visibility</option>
              <option value="public">Public</option>
              <option value="patient">Patient-Specific</option>
            </select>
          </div>
        </div>

        <div class="health-tips-grid">
          <?php if ($res_my_tips->num_rows === 0): ?>
            <p>No health tips created yet.</p>
          <?php else: ?>
            <?php while ($tip = $res_my_tips->fetch_assoc()): 
              $cat      = htmlspecialchars($tip['tip_category']);
              $vis      = $tip['visibility'];
              $patient  = htmlspecialchars($tip['patient_name'] ?? '');
            ?>
              <div class="health-tip-card"
                   data-category="<?= $cat ?>"
                   data-visibility="<?= $vis ?>">
                <div class="tip-header">
                  <h3><?= htmlspecialchars($tip['tip_title']) ?></h3>
                  <span class="tip-category"><?= ucfirst($cat) ?></span>
                  <span class="tip-visibility <?= $vis ?>">
                    <?= $vis === 'public' ? 'Public' : 'Patient-Specific' ?>
                  </span>
                </div>
                <div class="tip-content">
                  <p><?= nl2br(htmlspecialchars($tip['tip_content'])) ?></p>
                </div>
                <div class="tip-footer">
                  <?php if ($vis === 'patient'): ?>
                    <div class="tip-stats">Patient: <?= $patient ?></div>
                  <?php else: ?>
                    <div class="tip-stats">Created: <?= date('M j, Y', strtotime($tip['tip_date'])) ?></div>
                  <?php endif; ?>
                  <div class="tip-actions">
                    <a href="../php/save_health_tip.php?action=edit&id=<?= $tip['id'] ?>"
                       class="btn btn-small btn-secondary">Edit</a>
                    <a href="../php/delete_health_tip.php?id=<?= $tip['id'] ?>"
                       class="btn btn-small btn-outline">Delete</a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Create New Tip -->
      <div class="tab-content" id="create-tip">
        <div class="form-card">
          <h3>Create a New Health Tip</h3>
          <form action="../php/save_health_tip.php" method="post" id="health-tip-form">
            <div class="form-row">
              <div class="form-group">
                <label for="tip-title">Title</label>
                <input type="text" id="tip-title" name="tip_title" required>
              </div>
              <div class="form-group">
                <label for="tip-category">Category</label>
                <select id="tip-category" name="tip_category" required>
                  <option value="">Select…</option>
                  <option value="general">General Health</option>
                  <option value="diet">Diet &amp; Nutrition</option>
                  <option value="exercise">Exercise &amp; Fitness</option>
                  <option value="mental">Mental Health</option>
                  <option value="sleep">Sleep</option>
                  <option value="medication">Medication</option>
                  <option value="neurological">Neurological</option>
                  <option value="digestive">Digestive</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="tip-content">Content</label>
              <textarea id="tip-content" name="tip_content" rows="6" required></textarea>
            </div>

            <div class="form-group">
              <label>Visibility</label>
              <div class="radio-group">
                <label><input type="radio" name="visibility" value="public" checked> Public</label>
                <label><input type="radio" name="visibility" value="patient"> Patient-Specific</label>
              </div>
            </div>

            <div class="form-group patient-select" style="display:none;">
              <label for="patient-id">Patient</label>
              <select id="patient-id" name="patient_id">
                <option value="">Select a patient…</option>
                <?php while ($pt = mysqli_fetch_assoc($res_patients)): ?>
                  <option value="<?= $pt['id'] ?>"><?= htmlspecialchars($pt['full_name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Save Health Tip</button>
              <button type="button" class="btn btn-outline" id="save-as-template">Save as Template</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Templates -->
      <div class="tab-content" id="tip-templates">
        <div class="templates-container">
          <div class="templates-header">
            <h3>Health Tip Templates</h3>
            <div class="template-actions">
              <select id="template-category-filter">
                <option value="">All Categories</option>
                <option value="general">General Health</option>
                <option value="diet">Diet &amp; Nutrition</option>
                <option value="exercise">Exercise &amp; Fitness</option>
                <option value="mental">Mental Health</option>
                <option value="sleep">Sleep</option>
                <option value="medication">Medication</option>
                <option value="neurological">Neurological</option>
                <option value="digestive">Digestive</option>
              </select>
              <button class="btn btn-secondary" id="create-template">Create New Template</button>
            </div>
          </div>
          <div class="templates-grid">
            <p>No templates available. Use “Save as Template” in the form above to add templates.</p>
          </div>
        </div>
      </div>

    </main>
  </div>

  <script src="../js/doctor-health-tips.js"></script>
</body>
</html>
