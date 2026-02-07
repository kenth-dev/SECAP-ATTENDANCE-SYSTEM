# SECAP Attendance System

A modern, barcode-based attendance tracking solution developed for the SECAP department of the University of Saint Louis Tuguegarao. This system is designed to streamline student check-in and check-out during the university's 61st Foundation Week, supporting robust student management and providing real-time attendance logs with CSV import/export capabilities.

---

## Table of Contents
- [Features](#features)
- [System Overview](#system-overview)
- [Database Schema](#database-schema)
- [Installation Guide](#installation-guide)
- [Configuration](#configuration)
- [Usage Instructions](#usage-instructions)
- [Barcode Scanner Setup](#barcode-scanner-setup)
- [Customization](#customization)
- [Troubleshooting](#troubleshooting)
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
    name VARCHAR(100) NOT NULL,
    course VARCHAR(50),
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

## Contact
Created by Kenneth Gasmen and Jason Bagunu

For support, refer to the Troubleshooting section or ensure all setup steps are followed.
