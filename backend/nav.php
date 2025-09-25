<?php
ob_start();

session_start();
// Check if the username session variable is set
if (!isset($_SESSION["name"])) {
    // If not set, redirect to login page
    header("Location: login.php");
    exit();  // Ensure script stops after redirection
}

$mysqli = require $_SERVER['DOCUMENT_ROOT'] . "/database.php";

// Query to get all branches
$sql = "SELECT id, name FROM branches";
$result = $mysqli->query($sql);

// Check for query errors
if ($result === false) {
    die("Error retrieving branches: " . $mysqli->error);
}
?>

<style>
    .logo{
        width: 70px;
        
    }
</style>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">

<!-- Sidebar - Brand -->
<a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
    <div class="sidebar-brand-icon rotate-n-15">
        <img src="/uploads/logo.png" class="logo">
    </div>
    <div class="sidebar-brand-text mx-3">Love, Tea</div>
</a>

<!-- Divider -->
<hr class="sidebar-divider my-0">

<?php if ($_SESSION["role"] == "admin"){?>
<!-- Nav Item - Dashboard -->
<li class="nav-item <?php if ($screen == 'dashboard'){echo 'active';}?> ">
    <a class="nav-link" href="index.php">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Dashboard</span></a>
</li>



<!-- Nav Item - Charts -->

<!-- DISABLED MUNA
<li class="nav-item">
    <a class="nav-link" href="charts.html">
        <i class="fas fa-fw fa-chart-area"></i>
        <span>Charts</span></a>
</li>
-->

<!-- Nav Item - Branches with Dropdown -->
<li class="nav-item <?php if ($screen == 'branches'){echo 'active';}?>">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBranches" aria-expanded="true" aria-controls="collapseBranches">
        <i class="fas fa-fw fa-store"></i>
        <span>Branches</span>
    </a>
    <div id="collapseBranches" class="collapse" aria-labelledby="headingBranches" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
        <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $branch_id = htmlspecialchars($row['id']);
        $branch_name = htmlspecialchars($row['name']);
        echo '<a class="collapse-item" href="branch_index.php?id=' . $branch_id . '&b_id=' . $branch_id . '">' . $branch_name .' </a>';
    }
} else {
    echo '<li class="nav-item">
            <span class="nav-link">No branches available</span>
          </li>';
}
?>
<a class="collapse-item" href="branches.php">+ Add New Branch</a>
        </div>
    </div>
</li>

<!-- Nav Item - Tables -->
<li class="nav-item <?php if ($screen == 'employee'){echo 'active';}?>">
    <a class="nav-link" href="employee.php">
        <i class="fas fa-fw fa-user"></i>
        <span>Role Assignment</span></a>
</li>


<!-- Nav Item - Tables -->
<li class="nav-item <?php if ($screen == 'products'){echo 'active';}?>">
    <a class="nav-link" href="products.php">
        <i class="fas fa-fw fa-mug-hot"></i>
        <span>Products</span></a>
</li>
<hr class="sidebar-divider d-none d-md-block">

<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $branch_id = htmlspecialchars($row['id']);
        $branch_name = htmlspecialchars($row['name']);
        echo '<li class="nav-item">
                <a class="nav-link" href="#collapse'.$branch_id.'" data-toggle="collapse" aria-expanded="true"
                    aria-controls="collapse'.$branch_id.'">
                    <i class="fas fa-fw fa-table"></i>
                    <span>' . $branch_name . '</span>
                </a>
                <div id="collapse'.$branch_id.'" class="collapse" aria-labelledby="headingTwo"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">' . $branch_name .' Branch</h6>
                        <a class="collapse-item" href="view_branch.php?id=' . $branch_id . '&b_id=' . $branch_id . '">Inventory</a>
                        <a class="collapse-item" href="sales.php?id=' . $branch_id . '&b_id=' . $branch_id . '">Sales</a>
                    </div>
                </div>
              </li>';
    }
} else {
    echo '<li class="nav-item">
            <span class="nav-link">No branches available</span>
          </li>';
}

}else{?>

<?php 
$assigned_branch = $_SESSION['branch_id'];
$assigned_branch = $_SESSION['branch_id'];
$user_role = $_SESSION['role']; // Retrieve the user role from session

$sql = "SELECT * FROM branches WHERE id = " . intval($assigned_branch);
$result = $mysqli->query($sql);

if ($result === false) {
    die("Error retrieving branches: " . $mysqli->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $branch_id = htmlspecialchars($row['id']);
        $branch_name = htmlspecialchars($row['name']);
        echo '<li class="nav-item"> 
    <a class="nav-link" href="branch_index.php">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Branch Dashboard</span></a>
</li>
              ';

              if ($user_role === 'encoder') {
                echo '<li class="nav-item">
                            <a class="nav-link" href="restock_inventory.php?id=' . $branch_id . '&b_id=' . $branch_id . '">
                                <i class="fas fa-fw fa-table"></i>
                                <span>' . $branch_name . '</span>
                            </a>
                        </li>';
            }
        
        // Show Point Of Sales only if role is "cashier"
        if ($user_role === 'cashier') {
            echo '<li class="nav-item">
                    <a class="nav-link" href="pos.php">
                        <i class="fas fa-fw fa-cash-register"></i>
                        <span>Point Of Sales</span>
                    </a>
                  </li>';
        }
    }
} else {
    echo '<li class="nav-item">
            <span class="nav-link">No branches available</span>
          </li>';
}
}
?>

<!-- Divider -->

<!-- Sidebar Toggler (Sidebar) -->
<div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>

</ul>
<!-- End of Sidebar -->

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

<!-- Main Content -->
<div id="content">

    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

        <!-- Sidebar Toggle (Topbar) -->
        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
        </button>

        <!-- Topbar Navbar -->
        <ul class="navbar-nav ml-auto">

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= $_SESSION['name'];?></span>
                    <img class="img-profile rounded-circle"
                        src="img/undraw_profile.svg">
                </a>
                <!-- Dropdown - User Information -->
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                    aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Logout
                    </a>
                </div>
            </li>

        </ul>

    </nav>
    <!-- End of Topbar -->
    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
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
                    <a class="btn btn-primary" href="backend/signout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
<?php
ob_end_flush();
?>