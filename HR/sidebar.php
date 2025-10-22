<ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon">
            <i class="fas fa-users-cog"></i>
        </div>
        <div class="sidebar-brand-text mx-3">HR System</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Employee Management
    </div>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="employees.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Employees</span>
        </a>
    </li>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'applicants.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="applicants.php">
            <i class="fas fa-fw fa-user-plus"></i>
            <span>Applicants</span>
        </a>
    </li>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'departments.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="departments.php">
            <i class="fas fa-fw fa-building"></i>
            <span>Departments</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Attendance & Scheduling
    </div>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="attendance.php">
            <i class="fas fa-fw fa-calendar-check"></i>
            <span>Attendance</span>
        </a>
    </li>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'schedules.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="schedules.php">
            <i class="fas fa-fw fa-calendar-alt"></i>
            <span>Weekly Schedule</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Payroll
    </div>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'payroll.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="payroll.php">
            <i class="fas fa-fw fa-money-bill-wave"></i>
            <span>Payroll Management</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>