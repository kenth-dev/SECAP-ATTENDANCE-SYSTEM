<?php
require 'db.php';

$msg = '';

// Delete all students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_students'])) {
  if ($conn->query("DELETE FROM students")) {
    $msg = "All students deleted.";
  } else {
    $msg = "Error deleting all students.";
  }
}

// Delete student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
  $del_id = trim($_POST['delete_student']);
  if ($del_id !== '') {
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $del_id);
    if ($stmt->execute()) $msg = "Student deleted.";
    else $msg = "Error deleting student.";
  }
}

// Add student manually
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $sid = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $course = trim($_POST['course']);
    $year = intval($_POST['year_level'] ?? 0);

    if ($sid !== '' && $name !== '') {
        $stmt = $conn->prepare("INSERT INTO students (student_id, name, course, year_level) VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE name=VALUES(name), course=VALUES(course), year_level=VALUES(year_level)");
        $stmt->bind_param("sssi", $sid, $name, $course, $year);
        if ($stmt->execute()) $msg = "Student saved.";
        else $msg = "Error saving student.";
    } else {
        $msg = "Student ID and name required."; 
    }
}

// Search
$search = trim($_GET['search'] ?? '');
// Pagination
$per_page = 10;
$page = max(1, intval($_GET['page'] ?? 1));

if ($search !== '') {
  $search_words = preg_split('/\s+/', $search);
  $where_clauses = [];
  $types = '';
  $params = [];
  foreach ($search_words as $word) {
    // Build WHERE clause using placeholders and bind '%word%' values
    $where_clauses[] = "(LOWER(student_id) LIKE ? OR LOWER(name) LIKE ? OR LOWER(course) LIKE ?)";
    $pattern = '%' . strtolower($word) . '%';
    $params[] = $pattern;
    $params[] = $pattern;
    $params[] = $pattern;
    $types .= 'sss';
  }
  $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

  // Total count with prepared statement
  $sql_count = "SELECT COUNT(*) AS cnt FROM students $where_sql";
  $stmt = $conn->prepare($sql_count);
  if ($types !== '' && !empty($params)) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $total_res = $stmt->get_result();
  $total_rows = (int)$total_res->fetch_assoc()['cnt'];
  $total_pages = max(1, ceil($total_rows / $per_page));
  $page = min($page, $total_pages);
  $offset = ($page - 1) * $per_page;

  // Fetch students (paginated & filtered) with prepared statement
  $sql_select = "SELECT * FROM students $where_sql ORDER BY name ASC LIMIT ? OFFSET ?";
  $stmt = $conn->prepare($sql_select);
  $types_with_pagination = $types . 'ii';
  $params_with_pagination = array_merge($params, [$per_page, $offset]);
  $stmt->bind_param($types_with_pagination, ...$params_with_pagination);
  $stmt->execute();
  $res = $stmt->get_result();
} else {
  // No search: simple queries without user-controlled WHERE conditions
  $total_res = $conn->query("SELECT COUNT(*) AS cnt FROM students");
  $total_rows = (int)$total_res->fetch_assoc()['cnt'];
  $total_pages = max(1, ceil($total_rows / $per_page));
  $page = min($page, $total_pages);
  $offset = ($page - 1) * $per_page;

  // Fetch students (paginated) without filters
  $res = $conn->query("SELECT * FROM students ORDER BY name ASC LIMIT $per_page OFFSET $offset");
}
$active_page = 'students';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Students — SECAP Attendance</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-wrapper">

  <?php include 'sidebar.php'; ?>

  <main class="main-content">

    <div class="page-header">
      <h1>Student Management</h1>
      <p>Add, import, or remove students from the system</p>
    </div>

    <?php if ($msg): ?>
      <div class="alert-dark alert-info" style="max-width:700px;"><?php echo htmlspecialchars($msg); ?></div>
      <div style="margin-bottom:16px;"></div>
    <?php endif; ?>

    <!-- Add Student -->
    <div class="card-dark" id="student-list">
      <h5>Add Student</h5>
      <form method="POST" id="studentForm">
        <div class="grid-4">
          <div>
            <label class="form-label">Student ID</label>
            <input type="text" name="student_id" class="form-input" placeholder="e.g. 2300247" required>
          </div>
          <div>
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-input" placeholder="e.g. Kenneth Gasmen" required>
          </div>
          <div>
            <label class="form-label">Course</label>
            <input type="text" name="course" class="form-input" placeholder="e.g. BSCS">
          </div>
          <div>
            <label class="form-label">Year Level</label>
            <input type="number" name="year_level" class="form-input" placeholder="e.g. 3">
          </div>
        </div>
      </form>
      <div class="action-row">
        <button class="btn-primary-dark" type="submit" form="studentForm">
          <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><path d="M5 3.5A1.5 1.5 0 0 0 3.5 5v10A1.5 1.5 0 0 0 5 16.5h10A1.5 1.5 0 0 0 16.5 15V7.914a1.5 1.5 0 0 0-.44-1.06l-2.914-2.914A1.5 1.5 0 0 0 12.086 3.5H5Z" stroke="#e2e8f0" stroke-width="1.5"/><rect x="7" y="10" width="6" height="4" rx="1" stroke="#e2e8f0" stroke-width="1.5"/></svg>
          Save Student
        </button>
        <a href="import_students.php" class="btn-secondary-dark">
          <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><path d="M10 3v10m0 0l-3.5-3.5M10 13l3.5-3.5" stroke="#e2e8f0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="15" width="12" height="2" rx="1" fill="#e2e8f0"/></svg>
          Import CSV
        </a>
        <button class="btn-danger-dark" type="button" onclick="document.getElementById('studentForm').reset();">
          <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><rect x="5" y="7" width="10" height="8" rx="2" stroke="#e2e8f0" stroke-width="1.5"/><path d="M3 7h14M8 10v3M12 10v3M7 4h6a1 1 0 0 1 1 1v2H6V5a1 1 0 0 1 1-1Z" stroke="#e2e8f0" stroke-width="1.5"/></svg>
          Delete
        </button>
      </div>
    </div>

    <!-- Student List -->
    <div class="card-dark">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h5 style="margin-bottom:0;">Student List <span class="record-count"><?php echo $total_rows; ?> student<?php echo $total_rows !== 1 ? 's' : ''; ?></span></h5>
        <form method="GET" style="display:flex;gap:10px;align-items:center;">
          <input type="text" name="search" class="form-input" placeholder="Search by name, ID, or course" value="<?php echo htmlspecialchars($search); ?>" style="max-width:220px;">
          <button class="btn-primary-dark" style="padding:8px 18px;">Search</button>
          <?php if ($search !== ''): ?>
            <a href="students.php" class="btn-secondary-dark" style="padding:8px 18px;">Clear</a>
          <?php endif; ?>
        </form>
      </div>
      <div style="overflow-x:auto;">
        <table class="table-dark-custom">
          <thead>
            <tr>
              <th>Student ID</th>
              <th>Name</th>
              <th style="width:300px;">Course</th>
              <th style="text-align:center;">Year</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($r = $res->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['student_id']); ?></td>
              <td><?php echo htmlspecialchars($r['name']); ?></td>
              <td><?php echo htmlspecialchars($r['course']); ?></td>
              <td style="text-align:center;" ><?php echo htmlspecialchars($r['year_level']); ?></td>
              <td>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this student?');">
                  <input type="hidden" name="delete_student" value="<?php echo htmlspecialchars($r['student_id']); ?>">
                  <button class="btn-danger-dark btn-sm-dark">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <?php 
          $search_qs = $search !== '' ? '&search=' . urlencode($search) : '';
        ?>
        <?php if ($page > 1): ?>
          <a href="?page=<?php echo $page - 1; ?><?php echo $search_qs; ?>#student-list" class="page-link">← Prev</a>
        <?php endif; ?>
        <?php
          $start = max(1, $page - 2);
          $end = min($total_pages, $page + 2);
          if ($start > 1) echo '<a href="?page=1' . $search_qs . '#student-list" class="page-link">1</a>';
          if ($start > 2) echo '<span class="page-dots">…</span>';
          for ($i = $start; $i <= $end; $i++): ?>
            <a href="?page=<?php echo $i; ?><?php echo $search_qs; ?>#student-list" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
          <?php endfor;
          if ($end < $total_pages - 1) echo '<span class="page-dots">…</span>';
          if ($end < $total_pages) echo '<a href="?page=' . $total_pages . $search_qs . '#student-list" class="page-link">' . $total_pages . '</a>';
        ?>
        <?php if ($page < $total_pages): ?>
          <a href="?page=<?php echo $page + 1; ?><?php echo $search_qs; ?>#student-list" class="page-link">Next →</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

  </main>
</div>
</body>
</html>
