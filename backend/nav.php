<?php
ob_start();

session_start();
if (!isset($_SESSION["name"])) {
    header("Location: login.php");
    exit();
}

$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

$sql = "SELECT id, name FROM branches";
$result = $mysqli->query($sql);

if ($result === false) {
    die("Error retrieving branches: " . $mysqli->error);
}

$branches = [];
while ($row = $result->fetch_assoc()) {
    $branches[] = $row;
}

$assigned_branch = $_SESSION['branch_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
?>

<style>
    .logo {
        width: 70px;
    }
    
    #accordionSidebar {
        width: 14rem !important;
    }
    
    @media (max-width: 768px) {
        #accordionSidebar {
            width: 14rem !important;
        }
    }
</style>

<ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center">
        <div class="sidebar-brand-icon rotate-n-15">
            <img src="/uploads/logo.png" class="logo" alt="Logo">
        </div>
        <div class="sidebar-brand-text mx-3">Love, Tea</div>
    </a>

    <hr class="sidebar-divider my-0">

    <?php if ($user_role == "admin"): ?>    

        <li class="nav-item <?php echo (isset($screen) && $screen == 'employee') ? 'active' : ''; ?>">
            <a class="nav-link" href="employee.php">
                <i class="fas fa-fw fa-user"></i>
                <span>Role Assignment</span>
            </a>
        </li>

        <hr class="sidebar-divider d-none d-md-block">

    <?php endif; ?>

    <?php if ($user_role === 'inventory'): ?>
        <li class="nav-item">
            <a class="nav-link" href="branch_index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Inventory Dashboard</span>
            </a>
        </li>
        <?php foreach ($branches as $branch): ?>
            <?php if ($branch['id'] == $assigned_branch): ?>
                <li class="nav-item">
                    <a class="nav-link" href="restock_inventory.php?id=<?php echo htmlspecialchars($branch['id']); ?>&b_id=<?php echo htmlspecialchars($branch['id']); ?>">
                        <i class="fas fa-fw fa-table"></i>
                        <span><?php echo htmlspecialchars($branch['name']); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_branch.php?id=<?php echo htmlspecialchars($branch['id']); ?>">
                        <i class="fas fa-fw fa-chart-line"></i>
                        <span>Branch Sales</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="main_inventory.php">
                        <i class="fas fa-fw fa-mug-hot"></i>
                        <span>Ingredients</span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($user_role === 'sales'): ?>
        <li class="nav-item">
            <a class="nav-link" href="sales_index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Sales Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="pos.php">
                <i class="fas fa-fw fa-cash-register"></i>
                <span>Point Of Sales</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="sales.php?id=<?php echo htmlspecialchars($assigned_branch); ?>">
                <i class="fas fa-fw fa-receipt"></i>
                <span>Sales History</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="view_branch.php?id=<?php echo htmlspecialchars($assigned_branch); ?>">
                <i class="fas fa-fw fa-chart-line"></i>
                <span>Branch Sales</span>
            </a>
        </li>
    <?php endif; ?>

    <?php if ($user_role === 'production'): ?>
        <li class="nav-item <?php echo (isset($screen) && $screen == 'products') ? 'active' : ''; ?>">
            <a class="nav-link" href="products.php">
                <i class="fas fa-fw fa-cash-register"></i>
                <span>Products Management</span>
            </a>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

<div id="content-wrapper" class="d-flex flex-column">

    <div id="content">

        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">

                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        <i class="fas fa-user fa-fw text-success"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>

            </ul>

        </nav>

        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" href="../logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>

<?php ob_end_flush(); ?>