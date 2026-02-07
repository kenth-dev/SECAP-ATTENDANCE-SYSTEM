<?php
require 'db.php';

$date_filter = $_GET['date'] ?? date('Y-m-d'); // default today
$reset_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_today'])) {
  $stmt = $conn->prepare("DELETE FROM attendance WHERE DATE(scan_time) = ?");
  $stmt->bind_param("s", $date_filter);
  if ($stmt->execute()) {
    $reset_msg = "Attendance for $date_filter has been reset.";
  } else {
    $reset_msg = "Error resetting attendance.";
  }
}

$stmt = $conn->prepare("SELECT a.id, a.student_id, s.name, s.course, s.year_level, a.scan_time AS time_in, a.time_out 
                        FROM attendance a 
                        LEFT JOIN students s ON a.student_id = s.student_id
                        WHERE DATE(a.scan_time) = ?
                        ORDER BY a.scan_time DESC");
$stmt->bind_param("s", $date_filter);
$stmt->execute();
$res = $stmt->get_result();
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Attendance Log</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    padding:30px;
    background:linear-gradient(135deg,#0d2c54,#143b73);
    font-family:"Segoe UI",Arial,sans-serif;
    color:#fff;
}

.container{
    max-width:1100px;
}

.logo-holder{
    text-align:center;
    margin-bottom:20px;
}

.logo-holder img{
    width:110px;
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

.controls{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    align-items:center;
}

.controls input[type="date"]{
    padding:8px 12px;
    border-radius:10px;
    border:2px solid #d1d9e6;
    background:#f1f4f9;
}

.controls input[type="date"]:focus{
    outline:none;
    border-color:#143b73;
}

.btn{
    border-radius:12px;
    font-weight:600;
}

.btn-secondary{
    background:#6c757d;
}

.btn-success{
    background:#198754;
}

.btn-light{
    background:#f8f9fa;
}

.btn-danger{
    background:#dc3545;
}

.alert{
    border-radius:12px;
    font-weight:600;
}

.table{
    border-radius:16px;
    overflow:hidden;
    margin-top:15px;
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

.wrapper{
    background:#fff;
    padding:25px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}
.table thead{
    background:black;
}

.table thead th{
    color:black;
}

</style>
</head>

<body>

<div class="container">

  <!-- LOGO -->
  <div class="logo-holder">
    <img src="secap.png">
  </div>

  <h1>Attendance Log</h1>

  <div class="wrapper">

    <h5 class="mb-3">Attendance on <?php echo htmlspecialchars($date_filter); ?></h5>

    <div class="controls mb-3">

      <form method="GET" class="d-flex gap-2 align-items-center">

        <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">

        <button class="btn btn-secondary">Filter</button>

        <a href="export.php?date=<?php echo htmlspecialchars($date_filter); ?>" class="btn btn-success">Export CSV</a>

        <a href="index.php" class="btn btn-light">Back</a>

      </form>

      <form method="POST" onsubmit="return confirm('Reset attendance for <?php echo htmlspecialchars($date_filter); ?>? This cannot be undone!');">

        <input type="hidden" name="reset_today" value="1">

        <button class="btn btn-danger">Reset Attendance</button>

      </form>

    </div>

    <?php if ($reset_msg): ?>
      <div class="alert alert-warning"><?php echo htmlspecialchars($reset_msg); ?></div>
    <?php endif; ?>

    <div class="table-responsive">

      <table class="table table-sm">

        <thead>
          <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Name</th>
            <th>Course</th>
            <th>Year</th>
            <th>Date</th>
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
            <td><?php echo htmlspecialchars($r['year_level']); ?></td>
            <td><?php echo htmlspecialchars($date); ?></td>
            <td><?php echo htmlspecialchars($time_in); ?></td>
            <td><?php echo htmlspecialchars($time_out); ?></td>
          </tr>

        <?php endwhile; ?>

        </tbody>

      </table>

    </div>

  </div>

</div>

</body>
</html>
