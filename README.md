# 📋 Attendance System with Barcode Scanner

A PHP-based attendance tracking system that uses barcode scanners to record student time-in and time-out. The system supports student management, CSV import/export, and real-time attendance logging.

## 🎯 Features

- ✅ **Barcode Scanning**: Real-time barcode scanning for quick student check-in/check-out
- 👥 **Student Management**: Add, edit, delete, and import students via CSV
- 📊 **Attendance Log**: View daily attendance records with time-in and time-out
- 📁 **CSV Export**: Export attendance data for any date in CSV format
- 🔄 **Time In/Out Modes**: Switch between time-in and time-out modes
- 🗑️ **Reset Function**: Reset attendance records for specific dates
- 📱 **Responsive UI**: Modern, mobile-friendly interface

## 🗄️ Database Structure

The system uses MySQL with a database named **`attendance_db`** containing two main tables:

### **students** table
Stores student information:
```sql
CREATE TABLE students (
    student_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    course VARCHAR(100),
    year_level INT
);
```

### **attendance** table
Records attendance with time-in and time-out:
```sql
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    scan_time DATETIME NOT NULL,
    time_out DATETIME NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);
```

**How it works:**
- When a student scans in **TIME IN** mode, a new record is created with `scan_time` (current timestamp)
- When the same student scans in **TIME OUT** mode, the system finds their latest record from today and updates the `time_out` field
- The system prevents duplicate time-ins for the same day
- Each scan is linked to the student via `student_id` foreign key

## 📋 Prerequisites

Before setting up the application, ensure you have:

- **XAMPP**, **WAMP**, **MAMP**, or any local PHP development environment
  - PHP 7.4 or higher
  - MySQL 5.7 or higher
- A **barcode scanner** (USB type that emulates keyboard input)
- A web browser (Chrome, Firefox, Edge, etc.)

## 🚀 Installation & Setup

### Step 1: Install Local Server Environment

1. Download and install [XAMPP](https://www.apachefriends.org/) or [WAMP](https://www.wampserver.com/)
2. Start **Apache** and **MySQL** services from the control panel

### Step 2: Set Up the Database

1. Open your web browser and go to: `http://localhost/phpmyadmin`
2. Click on **"New"** in the left sidebar to create a new database
3. Enter database name: **`attendance_db`**
4. Click **"Create"**
5. Select the newly created `attendance_db` database
6. Click on the **"SQL"** tab and execute the following SQL commands:

```sql
-- Create students table
CREATE TABLE students (
    student_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    course VARCHAR(100),
    year_level INT
);

-- Create attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    scan_time DATETIME NOT NULL,
    time_out DATETIME NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);
```

### Step 3: Configure Database Connection

1. Open the `db.php` file in the project folder
2. Verify the database credentials match your setup:

```php
$host = "localhost";
$user = "root";          // Default XAMPP/WAMP username
$pass = "";              // Default is empty; change if you set a password
$db   = "attendance_db"; // Database name
```

3. If you've set a password for your MySQL root user, update the `$pass` variable

### Step 4: Copy Project Files

1. Copy the entire project folder to your web server directory:
   - **XAMPP**: `C:\xampp\htdocs\attendance\`
   - **WAMP**: `C:\wamp64\www\attendance\`
   - **MAMP**: `/Applications/MAMP/htdocs/attendance/`

2. Ensure the folder contains all these files:
   - `index.php` (main scanning page)
   - `db.php` (database configuration)
   - `scan.php` (barcode processing backend)
   - `students.php` (student management)
   - `attendance.php` (attendance log viewer)
   - `import_students.php` (CSV import)
   - `export.php` (CSV export)

### Step 5: Add Students

You have two options to add students:

#### Option A: Manual Entry
1. Go to: `http://localhost/attendance/students.php`
2. Fill in the form with:
   - Student ID
   - Name
   - Course
   - Year Level
3. Click **"Add/Update Student"**

#### Option B: CSV Import
1. Create a CSV file with this format:
```csv
student_id,name,course,year_level
2300247,Jason Bagunu,BSIT,3
2025002,Ana Cruz,BSIT,3
2025003,Maria Santos,BSCS,2
```

2. Go to: `http://localhost/attendance/import_students.php`
3. Click **"Choose File"** and select your CSV file
4. Click **"Upload"** to import all students at once

## 🎮 How to Use

### Recording Attendance

1. Open your browser and navigate to: `http://localhost/attendance/`

2. **TIME IN Mode** (default):
   - Ensure the **"✓ TIME IN"** button is highlighted (green)
   - Have students scan their ID cards/barcodes
   - System will record their arrival time
   - A success message will appear for each scan

3. **TIME OUT Mode**:
   - Click the **"✗ TIME OUT"** button (turns red)
   - Have students scan their ID cards/barcodes
   - System will update their time-out for today
   - If no time-in record exists, an error message appears

4. The barcode input field auto-focuses after each scan for quick consecutive scanning

### Viewing Attendance Records

1. Go to: `http://localhost/attendance/attendance.php`
2. Use the date picker to select any date
3. Click **"Go"** to view attendance for that date
4. The table shows:
   - Student ID
   - Name
   - Course
   - Year Level
   - Time In
   - Time Out

### Exporting Attendance Data

1. From the attendance log page, click **"Export to CSV"**
2. Or go directly to: `http://localhost/attendance/export.php?date=2026-02-06`
3. Change the date parameter to export different dates
4. File downloads automatically as `attendance_YYYY-MM-DD.csv`

### Managing Students

1. Go to: `http://localhost/attendance/students.php`
2. View all registered students in the table
3. Add new students using the form
4. Delete individual students using the delete button
5. Import bulk students via **"Import from CSV"** link

## 🔧 Barcode Scanner Setup

1. **Connect the Scanner**: Plug the USB barcode scanner into your computer
2. **Test the Scanner**: Open Notepad and scan a student ID card to verify it inputs correctly
3. **Barcode Format**: The system automatically processes barcodes by:
   - Removing the first 2 characters
   - Removing the last character
   - Example: If barcode reads `XX2300247Y`, the system extracts `2300247`
4. **Adjust if Needed**: If your barcode format is different, modify the logic in `scan.php`:
```php
// Current logic (line 15-17)
if (strlen($raw) > 3) $id = substr($raw, 2, -1);
else $id = $raw;
```

## 📂 File Structure

```
attendance/
│
├── index.php              # Main scanning interface
├── scan.php               # Backend barcode processing (AJAX)
├── db.php                 # Database connection configuration
├── students.php           # Student management page
├── attendance.php         # Attendance log viewer
├── import_students.php    # CSV import page
├── export.php             # CSV export handler
├── secap.png             # Logo image (optional)
└── README.md             # This file
```

## 🎨 Customization

### Change Logo
Replace `secap.png` in the project folder with your institution's logo

### Modify Styling
Edit the `<style>` sections in each PHP file to match your branding colors

### Adjust Barcode Format
Modify the parsing logic in `scan.php` (lines 15-17) to match your ID card format

## ⚠️ Troubleshooting

### Database Connection Error
- Verify MySQL service is running
- Check credentials in `db.php`
- Ensure `attendance_db` database exists

### Student Not Found
- Verify the student exists in the database
- Check barcode scanning is reading correctly
- Test barcode output in a text editor first

### Barcode Not Scanning
- Ensure scanner is properly connected
- Test scanner in Notepad
- Check barcode format matches system expectations
- Focus should be on the input field (system auto-focuses)

### Already Marked Error (Time In)
- Student has already timed in today
- Check attendance log to verify existing record
- Use TIME OUT mode to record departure

### No Time-In Record (Time Out)
- Student hasn't timed in yet today
- Have student scan in TIME IN mode first

## 📱 Browser Compatibility

Tested and working on:
- ✅ Google Chrome
- ✅ Mozilla Firefox
- ✅ Microsoft Edge
- ✅ Safari

## 🔒 Security Notes

- This is a **local development** application
- For production use, implement:
  - Password protection for admin pages
  - SQL injection prevention (already uses prepared statements)
  - HTTPS/SSL encryption
  - User authentication and authorization
  - Input validation and sanitization

## 📄 License

Free to use and modify for educational purposes.

## 👨‍💻 Developer

Created by Jason Bagunu

---

**Need Help?** Check the troubleshooting section or verify all setup steps have been completed correctly.
