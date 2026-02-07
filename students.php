<?php
require 'db.php';

$msg = '';

// Delete student
// Delete all students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_students'])) {
  if ($conn->query("DELETE FROM students")) {
    $msg = "All students deleted.";
  } else {
    $msg = "Error deleting all students.";
  }
}
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

// Fetch students
$res = $conn->query("SELECT * FROM students ORDER BY name ASC");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Students</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    padding:30px;
    background:linear-gradient(135deg,#0d2c54,#143b73);
    color:#fff;
    font-family:"Segoe UI",Arial,sans-serif;
}

.container{
    max-width:900px;
}

.logo-holder{
    text-align:center;
    margin-bottom:20px;
}

.logo-holder img{
    width:120px;
    border-radius:50%;
    box-shadow:0 6px 18px rgba(0,0,0,.4);
}

h1{
    text-align:center;
    font-weight:700;
    margin-bottom:25px;
}

.card{
    background:#fff;
    border:none;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
    color:#222;
}

.card h5{
    font-weight:700;
    margin-bottom:15px;
}

.form-control{
    background:#f1f4f9;
    border:2px solid #d1d9e6;
    border-radius:12px;
    padding:10px;
}

.form-control:focus{
    border-color:#143b73;
    box-shadow:none;
}

.btn{
    border-radius:12px;
    font-weight:600;
}

.btn-warning{
    background:#143b73;
    border:none;
    color:#fff;
}

.btn-warning:hover{
    background:#0d2c54;
}

.alert{
    border-radius:12px;
    font-weight:600;
}

.table{
    border-radius:14px;
    overflow:hidden;
}

.table thead{
    background:#143b73;
}

.table thead th{
    color:#fff;
    border:none;
}

.table tbody tr{
    background:#f8fafc;
    color:#222;
}

.table tbody tr:nth-child(even){
    background:#eef2f7;
}

.table td{
    border:none;
}

.btn-danger{
    border-radius:10px;
}

.btn-light{
    border-radius:12px;
}

.actions{
    margin-top:15px;
}
</style>
</head>

<body>

<div class="container">

  <!-- LOGO -->
  <div class="logo-holder">
    <img src="secap.png">
  </div>

  <h1>Student Management</h1>

  <?php if ($msg): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <div class="card p-4 mb-4">

    <h5>Add Student</h5>

    <form method="POST" id="studentForm">
      <div class="row">

        <div class="col-md-3 mb-2">
          <input type="text" name="student_id" class="form-control" placeholder="Student ID" required>
        </div>

        <div class="col-md-4 mb-2">
          <input type="text" name="name" class="form-control" placeholder="Full name" required>
        </div>

        <div class="col-md-3 mb-2">
          <input type="text" name="course" class="form-control" placeholder="Course">
        </div>

        <div class="col-md-2 mb-2">
          <input type="number" name="year_level" class="form-control" placeholder="Year">
        </div>

      </div>
    </form>

    <div class="d-flex gap-2 flex-wrap actions">

      <button class="btn btn-warning" type="submit" form="studentForm">Save</button>

      <a href="import_students.php" class="btn btn-secondary">Import CSV</a>

      <a href="index.php" class="btn btn-light">Back to Scanner</a>

      <form method="POST" style="display:inline;" onsubmit="return confirm('Delete ALL students? This cannot be undone!');">
        <input type="hidden" name="delete_all_students" value="1">
        <button class="btn btn-danger">Delete All</button>
      </form>

    </div>

  </div>

  <div class="card p-4">

    <h5>Student List</h5>

    <div class="table-responsive">

      <table class="table table-sm">

        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Course</th>
            <th>Year</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>

        <?php while ($r = $res->fetch_assoc()): ?>

          <tr>
            <td><?php echo htmlspecialchars($r['student_id']); ?></td>
            <td><?php echo htmlspecialchars($r['name']); ?></td>
            <td><?php echo htmlspecialchars($r['course']); ?></td>
            <td><?php echo htmlspecialchars($r['year_level']); ?></td>
            <td>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this student?');">
                <input type="hidden" name="delete_student" value="<?php echo htmlspecialchars($r['student_id']); ?>">
                <button class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>

        <?php endwhile; ?>

        </tbody>

      </table>

    </div>

  </div>

</div>

</body>
</html>
