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
$active_page = 'home';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home — SECAP Attendance</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-wrapper">

  <?php include 'sidebar.php'; ?>

  <main class="main-content">
    <div class="center-home">
      <div class="card-dark home-card">
        <div class="home-header">
          <img src="assets/secap.png" alt="SECAP Logo" class="home-logo">
          <div>
            <h1 class="home-title">Attendance Scanner</h1>
            <p class="home-desc">Scan student barcodes</p>
          </div>
        </div>
        <div class="mode-toggle modern-mode-toggle">
          <a href="?mode=time_in" class="<?php echo ($current_mode === 'time_in') ? 'active-in' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:7px;"><path d="M10 2.5l7.5 7.5-1.42 1.42L16 10.34V17.5a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V10.34l-.08.08L2.5 10 10 2.5z" stroke="#fff" stroke-width="1.5" fill="none"/></svg>
            Time In
          </a>
          <a href="?mode=time_out" class="<?php echo ($current_mode === 'time_out') ? 'active-out' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:7px;"><circle cx="10" cy="10" r="7.5" stroke="#fff" stroke-width="1.5" fill="none"/><path d="M7 10h6" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/></svg>
            Time Out
          </a>
        </div>
        <form method="POST" id="scanForm" autocomplete="off" class="home-form">
          <input type="hidden" id="modeInput" name="mode" value="<?php echo htmlspecialchars($current_mode); ?>">
          <input type="text" id="barcode" name="barcode" class="scan-input" placeholder="Scan barcode here..." autofocus autocomplete="off" />
          <div class="action-row">
            <button type="submit" class="btn-primary-dark submit-btn-centered">Submit</button>
          </div>
          <div class="info-note" style="margin:8px 0 0 0;color:#94a3b8;font-size:13px;text-align:center;">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><circle cx="10" cy="10" r="9" stroke="#94a3b8" stroke-width="1.5"/><path d="M10 6v2m0 2v4" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span>If you type the ID manually, use this format:</span><br>
            <span style="display:inline-block;margin-top:4px;font-size:12px;letter-spacing:0.5px;">
              <span style="color:#64748b;">[00</span>
              <span style="color:#e2e8f0;background:#222;padding:2px 6px;border-radius:4px;font-weight:600;">ID NUMBER</span>
              <span style="color:#64748b;">0] </span>
            </span>
            <span style="font-size:13px;color:#94a3b8;">Example: <b>0023037240</b></span>
          </div>
        </form>
        <div id="scanMessage" class="fixed-notification">
          <?php if ($msg): ?>
            <div class="alert-dark alert-info"><?php echo htmlspecialchars($msg); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
<style>
  .submit-btn-centered {
    font-size: 1.1em;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 48px;
    text-align: center;
    padding: 0;
  }
  .action-row {
    display: flex;
    justify-content: center;
    width: 100%;
    margin-top: 12px;
  }
  .center-home {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .home-card {
    max-width: 420px;
    width: 100%;
    min-width: 320px;
    min-height: 360px;
    margin: 0 auto;
    padding: 36px 32px 32px 32px;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 8px 32px 0 rgba(0,0,0,0.18);
    position: relative;
    border-radius: 16px;
  }
  .fixed-notification {
    position: absolute;
    left: 50%;
    top: -100px;
    transform: translateX(-50%);
    min-width: 320px;
    max-width: 420px;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
  }
  .fixed-notification .alert-dark {
    pointer-events: auto;
    min-width: 220px;
    text-align: center;
    font-size: 1em;
    box-shadow: 0 2px 12px rgba(0,0,0,0.18);
  }
  .home-header {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 18px;
  }
  .home-logo {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    box-shadow: 0 2px 10px rgba(0,0,0,0.18);
  }
  .home-title {
    font-size: 1.6em;
    font-weight: 700;
    color: #fff;
    margin: 0 0 2px 0;
  }
  .home-desc {
    font-size: 1em;
    color: #94a3b8;
    margin: 0;
  }
  .modern-mode-toggle {
    display: flex;
    gap: 10px;
    width: 100%;
    margin-bottom: 18px;
  }
  .modern-mode-toggle a {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px 0;
    border-radius: 12px;
    font-size: 1.08em;
    font-weight: 600;
    background: rgba(255,255,255,0.07);
    color: #fff;
    border: 1.5px solid transparent;
    transition: all 0.18s;
    letter-spacing: 0.5px;
  }
  .modern-mode-toggle a.active-in {
    background: linear-gradient(90deg, #059669 0%, #16a34a 100%);
    color: #fff;
    border-color: #16a34a;
    box-shadow: 0 2px 12px rgba(22,163,74,0.18);
  }
  .modern-mode-toggle a.active-out {
    background: linear-gradient(90deg, #b91c1c 0%, #ea580c 100%);
    color: #fff;
    border-color: #b91c1c;
    box-shadow: 0 2px 12px rgba(185,28,28,0.13);
  }
  .modern-mode-toggle a:hover:not(.active-in):not(.active-out) {
    background: rgba(255,255,255,0.13);
    color: #e2e8f0;
  }
  .home-form {
    width: 100%;
    margin-top: 10px;
  }
  @media (max-width: 600px) {
    .home-card { padding: 18px 6vw; }
    .home-header { flex-direction: column; gap: 8px; }
    .home-logo { width: 44px; height: 44px; }
    .fixed-notification { min-width: 180px; max-width: 98vw; left: 50%; bottom: -38px; }
  }
</style>
</div>

<script>

  const barcodeInput = document.getElementById('barcode');
  const form = document.getElementById('scanForm');
  const msgDiv = document.getElementById('scanMessage');
  const modeInput = document.getElementById('modeInput');

  let scanTimer = null;
  let lastInputTime = Date.now();
  let inputSequence = '';
  const SCAN_DELAY = 80; // ms between characters for scanner
  const SCAN_MIN_LENGTH = 5; // minimum length to consider as scan

  window.addEventListener('load', () => barcodeInput.focus());
  document.addEventListener('click', () => barcodeInput.focus());

  barcodeInput.addEventListener('input', (e) => {
    const now = Date.now();
    const diff = now - lastInputTime;
    lastInputTime = now;
    inputSequence += e.data || '';

    clearTimeout(scanTimer);

    scanTimer = setTimeout(() => {
      // If input was fast and long enough, treat as scan
      if (barcodeInput.value.trim() !== '' && inputSequence.length >= SCAN_MIN_LENGTH && diff <= SCAN_DELAY) {
        form.requestSubmit();
      }
      // Reset sequence for next input
      inputSequence = '';
    }, SCAN_DELAY + 30);
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const code = barcodeInput.value.trim();
    if (!code) return;

    try {
      const data = new FormData();
      data.append('barcode', code);
      data.append('mode', modeInput.value);

      const resp = await fetch('scan.php', {
        method: 'POST',
        body: data
      });

      const json = await resp.json();

      const cls =
        (json.status === 'in' || json.status === 'out') ? 'alert-success' :
        json.status === 'not_found' ? 'alert-danger' :
        json.status === 'already' ? 'alert-warning' :
        'alert-danger';

      msgDiv.innerHTML =
        `<div class="alert-dark ${cls}">${esc(json.message)}</div>`;
    } catch {
      msgDiv.innerHTML =
        `<div class="alert-dark alert-danger">Network error</div>`;
    }

    barcodeInput.value = '';
    barcodeInput.focus();
  });

  function esc(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }
</script>

</body>
</html>