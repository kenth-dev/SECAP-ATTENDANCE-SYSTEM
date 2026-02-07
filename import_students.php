<?php
require 'db.php';

$msg = '';
$imported_names = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvfile'])) {
    $file = $_FILES['csvfile']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        $row = 0;
        $conn->begin_transaction();
        try {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row++;
        if ($row == 1) {
          $header = array_map('strtolower', $data);
          if (in_array('student_id', $header)) continue;
        }
        $sid = trim($data[0] ?? '');
        $name = trim($data[1] ?? '');
        $course = trim($data[2] ?? '');
        $year = intval($data[3] ?? 0);

        if ($sid === '' || $name === '') continue;

        $stmt = $conn->prepare("INSERT INTO students (student_id, name, course, year_level) VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE name=VALUES(name), course=VALUES(course), year_level=VALUES(year_level)");
        $stmt->bind_param("sssi", $sid, $name, $course, $year);
        $stmt->execute();
        $imported_names[] = $name;
      }
      $conn->commit();
      $msg = "<span style='color:#22c55e;font-weight:600;'>Import finished.</span>";
        } catch (Exception $e) {
            $conn->rollback();
            $msg = "Import failed: " . $e->getMessage();
        }
        fclose($handle);
    } else {
        $msg = "Cannot open file.";
    }
}
$active_page = 'students';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Import Students — SECAP Attendance</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-wrapper">

  <?php include 'sidebar.php'; ?>

  <main class="main-content">

    <div class="page-header">
      <h1>Import Students via CSV</h1>
      <p>Upload a CSV file to bulk-import students</p>
    </div>

    <?php if ($msg): ?>
      <div class="alert-dark <?php echo (strpos($msg, 'Import finished.') !== false) ? 'alert-success' : 'alert-info'; ?>" style="margin-bottom:16px;">
        <?php echo strip_tags($msg); ?>
      </div>
    <?php endif; ?>

    <!-- ...existing code... -->

    <div class="card-dark" style="max-width:560px;">
      <h5>Upload CSV</h5>
      <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom:14px;">
          <label class="form-label">Select CSV file</label>
          <input type="file" name="csvfile" accept=".csv" required class="form-input">
        </div>
        <div class="action-row">
          <button class="btn-primary-dark">📤 Upload</button>
          <a href="students.php" class="btn-secondary-dark">← Back to Students</a>
        </div>
      </form>
    </div>

    <div class="card-dark" style="max-width:560px; text-align:center;">
      <h5>CSV Format Sample</h5>
      <div style="overflow-x:auto; display:flex; justify-content:center;">
        <table class="table-dark-custom" style="width:auto; margin-bottom:0;">
          <thead>
            <tr>
              <th>student_id</th>
              <th>name</th>
              <th>course</th>
              <th>year_level</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>2303724</td>
              <td>Kenneth Gasmen</td>
              <td>BSCS</td>
              <td>3</td>
            </tr>
            <tr>
              <td>2300247</td>
              <td>Jason Bagunu</td>
              <td>BSIT</td>
              <td>3</td>
            </tr>
            <tr>
              <td>2025002</td>
              <td>Ana Cruz</td>
              <td>BSIT</td>
              <td>3</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <?php if (!empty($imported_names)): ?>
      <div class="alert-dark alert-success" style="margin-bottom:16px;">
        <strong>Imported students:</strong>
        <ul style="margin:8px 0 0 18px; padding:0;">
          <?php foreach ($imported_names as $n): ?>
            <li><?php echo htmlspecialchars($n); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

  </main>
</div>
</body>
</html>
