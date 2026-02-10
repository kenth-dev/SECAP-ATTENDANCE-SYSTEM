# SECAP Attendance System

A modern, barcode-based attendance tracking solution developed for the SECAP department of the University of Saint Louis Tuguegarao. This system is designed to streamline student check-in and check-out during the university's 61st Foundation Week, supporting robust student management and providing real-time attendance logs with CSV import/export capabilities.

---

## Table of Contents
- [Features](#features)
- [System Overview](#system-overview)
- [Database Schema](#database-schema)
- [Installation Guide](#installation-guide)
- [Usage Instructions](#usage-instructions)
- [Troubleshooting](#troubleshooting)
- [Centralized Database](#centralize-database)
- [Contact](#contact)

---

## Features
- **Fast Barcode Scanning**: Instant student time-in/out with USB barcode scanner
- **Student Management**: Add, edit, delete, and bulk import students via CSV
- **Comprehensive Attendance Logs**: Daily records with time-in and time-out, searchable and exportable
- **CSV Export**: Download attendance data for any date
- **Mode Switching**: Toggle between Time In and Time Out
- **Attendance Reset**: Clear records for specific dates

---

## System Overview
SECAP Attendance System is designed for local deployment (XAMPP, WAMP, MAMP, etc.) and leverages a USB barcode scanner for seamless attendance tracking. The system ensures data integrity, prevents duplicate entries, and links every scan to a registered student.

---

## Database Schema

The system uses a MySQL database named `attendance_db` with the following structure:

```sql
-- Create the database
CREATE DATABASE IF NOT EXISTS attendance_db;
USE attendance_db;

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(250) NOT NULL,
    course VARCHAR(100),
    year_level INT
);

-- Create attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    scan_time DATETIME NOT NULL,
    time_out DATETIME NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);
```

---

## Installation Guide

### 1. Prerequisites
- Local server environment (XAMPP, WAMP, MAMP)
- PHP 7.4+
- MySQL 5.7+
- USB barcode scanner (keyboard emulation)
- Modern web browser

### 2. Database Setup
1. Launch phpMyAdmin (`http://localhost/phpmyadmin`)
2. Create a new database: `attendance_db`
3. Execute the provided SQL to create `students` and `attendance` tables

### 3. Project Deployment
1. Copy all project files to your web server directory:
   - XAMPP: `C:/xampp/htdocs/SECAP-ATTENDANCE-SYSTEM/`
   - WAMP: `C:/wamp64/www/SECAP-ATTENDANCE-SYSTEM/`
   - MAMP: `/Applications/MAMP/htdocs/SECAP-ATTENDANCE-SYSTEM/`
2. Ensure the following files are present:
   - `index.php`, `db.php`, `scan.php`, `students.php`, `attendance.php`, `import_students.php`, `export.php`, `assets/secap.png`

### 4. Database Configuration
Edit `db.php` to match your MySQL credentials:
```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "attendance_db";
```

---

## Usage Instructions

### Student Management
- **Manual Entry**: Use `students.php` to add or update students individually.
- **Bulk Import**: Use `import_students.php` to upload a CSV file with student data.

### Recording Attendance
- **Time In**: Default mode. Scan student barcode to record arrival.
- **Time Out**: Switch mode, then scan to record departure.
- **Auto-Focus**: Input field is auto-focused for rapid scanning.
- **Duplicate Prevention**: System blocks multiple time-ins per day.

### Viewing & Exporting Attendance
- Use `attendance.php` to view, search, and filter daily records.
- Export attendance for any date as CSV via the export button or `export.php?date=YYYY-MM-DD`.

### Resetting Attendance
- Use the reset function in `attendance.php` to clear records for a specific date.

---

## Troubleshooting
- **Database Connection Error**: Check MySQL service, credentials in `db.php`, and database existence.
- **Student Not Found**: Ensure student is registered and barcode is read correctly.
- **Barcode Not Scanning**: Confirm scanner is connected and working in a text editor.
- **Duplicate/Already Marked**: Student has already timed in for the day.
- **No Time-In Record**: Student must time in before timing out.

---
## Centralize Database
Optional Centralized Database Setup (MySQL over LAN)

This system uses a centralized MySQL database so multiple laptops share one attendance database in real time.

<h3>Architecture</h3>

One laptop acts as the database host.

MySQL runs only on the host laptop.

All client laptops connect to the host via LAN or Wi-Fi.

phpMyAdmin is used only for management, not as the database itself.

<h3>Requirements</h3>

Same Wi-Fi or LAN network.

XAMPP installed on all laptops.

MySQL running on the host laptop.

Network profile set to Private on the host.

Host Laptop Setup

Start MySQL in XAMPP.

Configure MySQL to accept remote connections.
Edit xampp/mysql/bin/my.ini:

```ini
bind-address = 0.0.0.0
```

Restart MySQL after changes.

Create a remote MySQL user.
Run in phpMyAdmin on the host:

```sql
CREATE USER 'secap'@'%' IDENTIFIED BY 'YES';
GRANT ALL PRIVILEGES ON attendance_db.* TO 'secap'@'%';
FLUSH PRIVILEGES;
```

Allow MySQL through Windows Firewall.

TCP port: 3306

Profile: Private only

Get the host IPv4 address.

```nginx
ipconfig
```


Use this IP in all client configurations.

<h2>Client Laptop Setup</h2>

Do not run MySQL.

Update db.php to point to the host IP.

```php
<?php
$host = "host-laptop-IP"; // host laptop IP
$user = "secap";
$pass = "YES";
$db   = "attendance_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

<h3>Test connectivity.</h3>

```nginx
ping (host-laptop-IP
```

Ping must succeed before MySQL connections will work.

<h3>Common Errors</h3>

HY000/2002
Network issue. Host unreachable or MySQL not running.

HY000/1045
MySQL user not allowed from client IP or wrong credentials.


<h3>Notes</h3>

Do not use localhost on client laptops.

Do not include @% in PHP usernames.

Do not expose port 3306 to public networks.

Back up the database regularly.

---

## Contact
Created by Kenneth Gasmen and Jason Bagunu

For support, refer to the Troubleshooting section or ensure all setup steps are followed.
