<style>
    #accordionSidebar {
        width: 14rem;
        transition: width 0.15s ease-in-out;
    }

    #accordionSidebar.toggled {
        width: 4.5rem;
        overflow-x: hidden;
    }

    #accordionSidebar.toggled .sidebar-brand-text {
        display: none;
    }

    #accordionSidebar.toggled .nav-item .nav-link span {
        display: none;
    }

    #accordionSidebar.toggled .sidebar-heading {
        display: none;
    }

    #accordionSidebar.toggled .sidebar-divider {
        margin: 0.5rem 0;
    }

    #sidebarToggle {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(255, 255, 255, 0.2);
    transition: all 0.15s ease-in-out;
    font-size: 0;
    margin-left: auto; 
    margin-right: 5rem; 
}

#sidebarToggle:hover {
    background-color: rgba(255, 255, 255, 0.25);
}

#sidebarToggle::before {
    content: '\f104';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 1rem; /* Set font size for the arrow */
    color: rgba(255, 255, 255, 0.5);
    transition: transform 0.15s ease-in-out;
    display: block;
}

#accordionSidebar.toggled #sidebarToggle {
    transform: rotate(180deg);
    margin-left: auto;
    margin-right: auto; /* Center it when collapsed */
}
    @media (min-width: 768px) {
        #accordionSidebar {
            width: 14rem !important;
        }

        #accordionSidebar.toggled {
            width: 4.5rem !important;
        }
    }
</style>

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

    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'transactions_history.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="transactions_history.php">
            <i class="fas fa-fw fa-history"></i>
            <span>Transactions History</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('accordionSidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('toggled');
            
            if (typeof(Storage) !== 'undefined') {
                if (sidebar.classList.contains('toggled')) {
                    localStorage.setItem('sb|sidebar-toggle', 'true');
                } else {
                    localStorage.removeItem('sb|sidebar-toggle');
                }
            }
        });
    }
    
    if (typeof(Storage) !== 'undefined') {
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            sidebar.classList.add('toggled');
        }
    }
});
</script>