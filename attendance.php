<?php
require 'db.php';

$date_filter = $_GET['date'] ?? date('Y-m-d');
$reset_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_today'])) {
  $stmt = $conn->prepare("DELETE FROM attendance WHERE DATE(scan_time) = ?");
  $stmt->bind_param("s", $date_filter);
  if ($stmt->execute()) {
    // Reset AUTO_INCREMENT to 1
    $conn->query("ALTER TABLE attendance AUTO_INCREMENT = 1");
    $reset_msg = "Attendance for $date_filter has been reset and numbering restarted.";
  } else {
    $reset_msg = "Error resetting attendance.";
  }
}

// Pagination
$per_page = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$count_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM attendance WHERE DATE(scan_time) = ?");
$count_stmt->bind_param("s", $date_filter);
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['cnt'];
$total_pages = max(1, ceil($total_rows / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$search = trim($_GET['search'] ?? '');
// Build search WHERE clause
$where_clauses = ["DATE(a.scan_time) = ?"];
$params = [$date_filter];
$param_types = "s";
if ($search !== '') {
  $search_words = preg_split('/\s+/', strtolower($search));
  foreach ($search_words as $word) {
    $where_clauses[] = "(LOWER(a.student_id) LIKE ? OR LOWER(s.name) LIKE ? OR LOWER(s.course) LIKE ?)";
    $params[] = "%$word%";
    $params[] = "%$word%";
    $params[] = "%$word%";
    $param_types .= "sss";
  }
}
$where_sql = implode(' AND ', $where_clauses);
$count_sql = "SELECT COUNT(*) AS cnt FROM attendance a LEFT JOIN students s ON a.student_id = s.student_id WHERE $where_sql";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($param_types, ...$params);
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['cnt'];
$total_pages = max(1, ceil($total_rows / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$sql = "SELECT a.id, a.student_id, s.name, s.course, s.year_level, a.scan_time AS time_in, a.time_out FROM attendance a LEFT JOIN students s ON a.student_id = s.student_id WHERE $where_sql ORDER BY a.scan_time DESC LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$active_page = 'attendance';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Attendance — SECAP Attendance</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-wrapper">

  <?php include 'sidebar.php'; ?>

  <main class="main-content">

    <div class="page-header">
      <h1>Attendance Log</h1>
      <p>View and manage attendance records for <?php echo htmlspecialchars($date_filter); ?></p>
    </div>

    <?php if ($reset_msg): ?>
      <div class="alert-dark alert-warning" style="margin-bottom:16px;"><?php echo htmlspecialchars($reset_msg); ?></div>
    <?php endif; ?>

    <!-- Controls -->
    <div class="card-dark card-controls-bar">
      <div class="controls-row controls-row-bar">
        <form method="GET" class="controls-form controls-form-bar">
          <input type="date" name="date" class="form-input" value="<?php echo htmlspecialchars($date_filter); ?>">
          <button class="btn-primary-dark filter-date-btn" style="margin-left:8px;">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><circle cx="9" cy="9" r="6" stroke="#e2e8f0" stroke-width="1.5"/><path d="M15 15l-3-3" stroke="#e2e8f0" stroke-width="1.5" stroke-linecap="round"/></svg>Filter Date
          </button>
        </form>
        <div class="controls-actions-right">
          <a href="export.php?date=<?php echo htmlspecialchars($date_filter); ?>" class="btn-success-dark">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><path d="M10 3v10m0 0l-3.5-3.5M10 13l3.5-3.5" stroke="#e2e8f0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="15" width="12" height="2" rx="1" fill="#e2e8f0"/></svg>
            Export CSV
          </a>
          <form method="POST" onsubmit="return confirm('Reset attendance for <?php echo htmlspecialchars($date_filter); ?>? This cannot be undone!');" style="margin-bottom:0; display:inline;">
            <input type="hidden" name="reset_today" value="1">
            <button class="btn-danger-dark">
              <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><rect x="5" y="7" width="10" height="8" rx="2" stroke="#e2e8f0" stroke-width="1.5"/><path d="M3 7h14M8 10v3M12 10v3M7 4h6a1 1 0 0 1 1 1v2H6V5a1 1 0 0 1 1-1Z" stroke="#e2e8f0" stroke-width="1.5"/></svg>
              Reset Attendance
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Attendance Table -->
    <div class="card-dark">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h5 style="margin-bottom:0;">Records <span class="record-count"><?php echo $total_rows; ?> record<?php echo $total_rows !== 1 ? 's' : ''; ?></span></h5>
        <form method="GET" style="display:flex;gap:10px;align-items:center;">
          <input type="hidden" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
          <input type="text" name="search" class="form-input" placeholder="Search by name, ID, or course" value="<?php echo htmlspecialchars($search); ?>" style="max-width:220px;">
          <button class="btn-primary-dark" style="padding:8px 18px;">Search</button>
          <?php if ($search !== ''): ?>
            <a href="attendance.php?date=<?php echo urlencode($date_filter); ?>" class="btn-secondary-dark" style="padding:8px 18px;">Clear</a>
          <?php endif; ?>
        </form>
      </div>
      <div style="overflow-x:auto;">
        <table class="table-dark-custom">
          <thead>
            <tr>
              <th>#</th>
              <th>Student ID</th>
              <th>Name</th>
              <th>Course</th>
              <th style="text-align:center;">Year</th>
              <th style="text-align:center;">Date</th>
              <th>Time In</th>
              <th>Time Out</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($r = $res->fetch_assoc()): ?>
            <?php
              $date = date('Y-m-d', strtotime($r['time_in']));
              $time_in = date('H:i:s', strtotime($r['time_in']));
              $time_out = $r['time_out'] ? date('H:i:s', strtotime($r['time_out'])) : '';
            ?>
            <tr>
              <td><?php echo htmlspecialchars($r['id']); ?></td>
              <td><?php echo htmlspecialchars($r['student_id']); ?></td>
              <td><?php echo htmlspecialchars($r['name']); ?></td>
              <td><?php echo htmlspecialchars($r['course']); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($r['year_level']); ?></td>
              <td style="text-align:center; width:120px;"><?php echo htmlspecialchars($date); ?></td>
              <td><span class="badge-status badge-in"><?php echo htmlspecialchars($time_in); ?></span></td>
              <td>
                <?php if ($time_out): ?>
                  <span class="badge-status badge-out"><?php echo htmlspecialchars($time_out); ?></span>
                <?php else: ?>
                  <span style="color:#475569;">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?date=<?php echo urlencode($date_filter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" class="page-link" aria-label="Previous">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M13 16l-5-6 5-6" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Prev
          </a>
        <?php endif; ?>
        <?php
          $start = max(1, $page - 2);
          $end = min($total_pages, $page + 2);
          if ($start > 1) echo '<a href="?date=' . urlencode($date_filter) . '&search=' . urlencode($search) . '&page=1" class="page-link">1</a>';
          if ($start > 2) echo '<span class="page-dots">…</span>';
          for ($i = $start; $i <= $end; $i++): ?>
            <a href="?date=<?php echo urlencode($date_filter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
          <?php endfor;
          if ($end < $total_pages - 1) echo '<span class="page-dots">…</span>';
          if ($end < $total_pages) echo '<a href="?date=' . urlencode($date_filter) . '&search=' . urlencode($search) . '&page=' . $total_pages . '" class="page-link">' . $total_pages . '</a>';
        ?>
        <?php if ($page < $total_pages): ?>
          <a href="?date=<?php echo urlencode($date_filter); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" class="page-link" aria-label="Next">
            Next
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M7 4l5 6-5 6" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

  </main>
</div>
</body>
</html>
