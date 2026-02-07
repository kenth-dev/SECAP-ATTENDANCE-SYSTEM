<?php
// sidebar.php — Reusable sidebar navigation component
// Usage: Set $active_page before including this file.
// Example: $active_page = 'home'; include 'sidebar.php';
?>

<aside class="sidebar">

  <div class="sidebar-brand">
    <img src="assets/secap.png" alt="SECAP">
    <span>
      <h2 style="font-family: 'Montserrat', Arial, sans-serif; letter-spacing: 2px; font-weight: 800; margin-bottom: 0;">
        <strong>SECAP</strong>
      </h2>
      <small style="font-family: 'Montserrat', Arial, sans-serif; color: #94a3b8;">Attendance System</small>
    </span>
  </div>

  <ul class="sidebar-nav">

    <li>
      <a href="index.php" class="<?php echo ($active_page === 'home') ? 'active' : ''; ?>">
        <span class="nav-icon" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 8.5L10 3L17 8.5" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 10V16.5C5 16.7761 5.22386 17 5.5 17H8.5C8.77614 17 9 16.7761 9 16.5V13.5C9 13.2239 9.22386 13 9.5 13H10.5C10.7761 13 11 13.2239 11 13.5V16.5C11 16.7761 11.2239 17 11.5 17H14.5C14.7761 17 15 16.7761 15 16.5V10" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <span>Home</span>
      </a>
    </li>

    <li>
      <a href="students.php" class="<?php echo ($active_page === 'students') ? 'active' : ''; ?>">
        <span class="nav-icon" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="7" r="3.5" stroke="#94a3b8" stroke-width="2"/><path d="M3.5 16C3.5 13.5147 6.23858 11.5 10 11.5C13.7614 11.5 16.5 13.5147 16.5 16" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/></svg>
        </span>
        <span>Students</span>
      </a>
    </li>

    <li>
      <a href="attendance.php" class="<?php echo ($active_page === 'attendance') ? 'active' : ''; ?>">
        <span class="nav-icon" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="4" y="4" width="12" height="12" rx="2" stroke="#94a3b8" stroke-width="2"/><path d="M7 2V4M13 2V4" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/><path d="M7 8H13M7 12H13" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/></svg>
        </span>
        <span>Attendance</span>
      </a>
    </li>

  </ul>

  <div class="sidebar-footer">
    &copy; <?php echo date('Y'); ?> SECAP
  </div>

</aside>
