<ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Finance System</div>
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
        Financial Management
    </div>


    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'expenses.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="expenses.php">
            <i class="fas fa-fw fa-receipt"></i>
            <span>Expenses</span>
        </a>
    </li>


    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Payroll & Salary
    </div>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'salary_records.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="salary_records.php">
            <i class="fas fa-fw fa-money-bill-wave"></i>
            <span>Salary Records</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Supplies & Inventory
    </div>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'supplies.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="supplies.php">
            <i class="fas fa-fw fa-boxes"></i>
            <span>Supplies Management</span>
        </a>
    </li>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'purchase_orders.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="purchase_orders.php">
            <i class="fas fa-fw fa-file-invoice-dollar"></i>
            <span>Purchase Orders</span>
        </a>
    </li>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'returns.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="returns.php">
            <i class="fas fa-fw fa-undo"></i>
            <span>Product Returns</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Audit & Reports
    </div>

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'audit.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="audit.php">
            <i class="fas fa-fw fa-search-dollar"></i>
            <span>Financial Audit</span>
        </a>
    </li>


    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'transaction_history.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="sales_history.php">
            <i class="fas fa-fw fa-history"></i>
            <span>Sales History</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>