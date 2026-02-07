<?php
session_start();
require 'db.php';

// Initialize mode (default to time_in)
if (!isset($_SESSION['attendance_mode'])) {
    $_SESSION['attendance_mode'] = 'time_in';
}

// Handle mode switching
if (isset($_GET['mode']) && in_array($_GET['mode'], ['time_in', 'time_out'])) {
    $_SESSION['attendance_mode'] = $_GET['mode'];
}

$msg = '';
$current_mode = $_SESSION['attendance_mode'];
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Attendance Scanner</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    <style>
      body {
          padding: 30px;
          background: linear-gradient(135deg, #0d2c54, #143b73);
          font-family: "Segoe UI", Arial, sans-serif;
          color: #fff;
      }

      .container {
          max-width: 600px;
      }

      .logo-holder {
          padding-top: 15px;
          text-align: center;
          margin-bottom: 25px;
      }

      .logo-holder img {
          width: 140px;
          border-radius: 50%;
          box-shadow: 0 6px 18px rgba(0,0,0,0.4);
      }

      h1 {
          text-align: center;
          font-weight: 700;
          letter-spacing: 1px;
      }

      .card {
          background: #ffffff;
          border: none;
          border-radius: 18px;
          box-shadow: 0 10px 30px rgba(0,0,0,0.35);
          color: #222;
      }

      .form-label {
          font-weight: 600;
      }

      input {
          background: #f1f4f9;
          border: 2px solid #d1d9e6;
          border-radius: 12px;
          padding: 14px;
          font-size: 22px;
      }

      input:focus {
          border-color: #143b73;
          box-shadow: none;
      }

      .btn-group a {
          border-radius: 14px !important;
          font-size: 20px;
      }

      .btn-warning {
          background: #143b73;
          border: none;
          color: #fff;
          font-weight: 600;
          padding: 10px 22px;
          border-radius: 12px;
      }

      .btn-warning:hover {
          background: #0d2c54;
      }

      .btn-secondary, .btn-success {
          border-radius: 12px;
      }

        .alert {
          border-radius: 12px;
          font-weight: 600;
        }


  </style>
</head>
<body>
<div class="container">
  <div class="logo-holder">
  <img src="secap.png" alt="SECAP Logo">
</div>

<h1 class="mb-3">Attendance System</h1>

  <!-- Mode Selection Buttons -->
  <div class="mb-4">
    <div class="btn-group w-100" role="group">
      <a href="?mode=time_in" class="btn btn-lg <?php echo ($current_mode === 'time_in') ? 'btn-success' : 'btn-outline-success'; ?>" style="font-weight: 600; flex: 1;">
        ✓ TIME IN
      </a>
      <a href="?mode=time_out" class="btn btn-lg <?php echo ($current_mode === 'time_out') ? 'btn-danger' : 'btn-outline-danger'; ?>" style="font-weight: 600; flex: 1;">
        ✗ TIME OUT
      </a>
    </div>
    <div style="text-align: center; margin-top: 10px; font-size: 18px; font-weight: bold; color: <?php echo ($current_mode === 'time_in') ? '#40c057' : '#ff6b6b'; ?>;">
      <span><?php echo ($current_mode === 'time_in') ? 'TIME IN' : 'TIME OUT'; ?></span>
    </div>
  </div>

  <div class="card p-3 mb-3">
    <form method="POST" id="scanForm" autocomplete="off">
      <input type="hidden" id="modeInput" name="mode" value="<?php echo htmlspecialchars($current_mode); ?>">
      <div class="mb-2">
        <label for="barcode" class="form-label">Scan Student ID</label>
        <input type="text" id="barcode" name="barcode" class="form-control form-control-lg" placeholder="Scan here..." autofocus autocomplete="off" />
      </div>
      <div>
        <button type="submit" class="btn btn-warning">Submit</button>
        <a href="students.php" class="btn btn-secondary">Students</a>
        <a href="attendance.php" class="btn btn-secondary">Attendance Log</a>
        <a href="export.php" class="btn btn-success">Export CSV</a>
      </div>
    </form>
    <div class="mt-3">
      <div id="scanMessage">
        <?php if ($msg): ?>
          <div class="alert alert-light"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  const barcodeInput = document.getElementById('barcode');
  const form = document.getElementById('scanForm');
  const msgDiv = document.getElementById('scanMessage');
  const modeInput = document.getElementById('modeInput');

  window.onload = () => { barcodeInput.focus(); };
  window.addEventListener('click', () => barcodeInput.focus());

  // Use AJAX to submit scans and show immediate messages without reloading
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const code = barcodeInput.value.trim();
    if (!code) return;

    try {
      const data = new FormData();
      data.append('barcode', code);
      data.append('mode', modeInput.value);
      const resp = await fetch('scan.php', { method: 'POST', body: data });
      const json = await resp.json();

      const alertClass = json.status === 'in' ? 'alert-success' : (json.status === 'out' ? 'alert-info' : (json.status === 'not_found' ? 'alert-danger' : (json.status === 'already' ? 'alert-warning' : 'alert-danger')));
      msgDiv.innerHTML = `<div class="alert ${alertClass}">${escapeHtml(json.message)}</div>`;

      // big-name overlay removed: only show message in the scanMessage area
    } catch (err) {
      msgDiv.innerHTML = `<div class="alert alert-danger">Network error</div>`;
    }

    barcodeInput.value = '';
    barcodeInput.focus();
  });

  function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
</body>
</html>
