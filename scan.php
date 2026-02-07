<?php
require 'db.php';

header('Content-Type: application/json; charset=utf-8');

$input = $_POST['barcode'] ?? null;
$mode = $_POST['mode'] ?? 'time_in'; // Get mode from form (time_in or time_out)

if ($input === null) {
    echo json_encode(['status' => 'error', 'message' => 'No barcode provided']);
    exit;
}

$raw = trim($input);
// Remove first two and last digit like existing logic
if (strlen($raw) > 3) $id = substr($raw, 2, -1);
else $id = $raw;

if ($id === '') {
    echo json_encode(['status' => 'error', 'message' => 'Empty barcode']);
    exit;
}

$stmt = $conn->prepare("SELECT name FROM students WHERE student_id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['status' => 'not_found', 'message' => 'Student not on the list', 'student_name' => null]);
    exit;
}

$row = $res->fetch_assoc();
$name = $row['name'];

// Ensure `time_out` column exists (add if missing)
$col = $conn->query("SHOW COLUMNS FROM attendance LIKE 'time_out'");
if ($col && $col->num_rows === 0) {
    $conn->query("ALTER TABLE attendance ADD COLUMN time_out DATETIME NULL");
}

// ===== HANDLE TIME OUT MODE =====
if ($mode === 'time_out') {
    // Find the latest attendance record for today without time_out
    $stmtCheck = $conn->prepare("SELECT id, scan_time FROM attendance WHERE student_id = ? AND DATE(scan_time) = CURDATE() AND (time_out IS NULL OR time_out = '0000-00-00 00:00:00') ORDER BY id DESC LIMIT 1");
    $stmtCheck->bind_param("s", $id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    
    if ($resCheck && $resCheck->num_rows > 0) {
        $rowExist = $resCheck->fetch_assoc();
        $upd = $conn->prepare("UPDATE attendance SET time_out = NOW() WHERE id = ?");
        $upd->bind_param("i", $rowExist['id']);
        if ($upd->execute()) {
            echo json_encode(['status' => 'out', 'message' => 'TIME OUT recorded for ' . $name . ' at ' . date('Y-m-d H:i:s'), 'student_name' => $name]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error saving TIME OUT', 'student_name' => $name]);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No active TIME IN record found for ' . $name . ' today', 'student_name' => $name]);
        exit;
    }
}

// ===== HANDLE TIME IN MODE =====
// Check if already marked today (for time_in mode)
$stmt2 = $conn->prepare("SELECT id, scan_time FROM attendance WHERE student_id = ? AND DATE(scan_time) = CURDATE() AND (time_out IS NULL OR time_out = '0000-00-00 00:00:00') LIMIT 1");
$stmt2->bind_param("s", $id);
$stmt2->execute();
$res2 = $stmt2->get_result();

if ($res2->num_rows > 0) {
    $existing = $res2->fetch_assoc();
    echo json_encode(['status' => 'already', 'message' => 'Already marked TIME IN for ' . $name . ' at ' . $existing['scan_time'], 'student_name' => $name]);
    exit;
}

// Insert new time_in record
$stmt3 = $conn->prepare("INSERT INTO attendance (student_id, scan_time) VALUES (?, NOW())");
$stmt3->bind_param("s", $id);
if ($stmt3->execute()) {
    echo json_encode(['status' => 'in', 'message' => 'TIME IN recorded for ' . $name . ' at ' . date('Y-m-d H:i:s'), 'student_name' => $name]);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error saving attendance', 'student_name' => null]);
    exit;
}

?>
