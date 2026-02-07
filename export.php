<?php
require 'db.php';

$date = $_GET['date'] ?? date('Y-m-d');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_' . $date . '.csv');

$out = fopen('php://output', 'w');
$fput = ['Student ID', 'Name', 'Course', 'Year', 'Time In', 'Time Out'];
fputcsv($out, $fput);

$stmt = $conn->prepare("SELECT a.student_id, s.name, s.course, s.year_level, a.scan_time AS time_in, a.time_out
                        FROM attendance a
                        LEFT JOIN students s ON a.student_id = s.student_id
                        WHERE DATE(a.scan_time) = ?
                        ORDER BY a.scan_time ASC");
$stmt->bind_param("s", $date);
$stmt->execute();
$res = $stmt->get_result();

while ($r = $res->fetch_assoc()) {
    fputcsv($out, [$r['student_id'], $r['name'], $r['course'], $r['year_level'], $r['time_in'], $r['time_out']]);
}
fclose($out);
exit;
