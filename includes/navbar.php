<nav class="navbar">

    <a class="brand" href="dashboard.php">
        Dorm Management
    </a>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <?php if (
            isset($_SESSION['role']) &&
            $_SESSION['role'] === 'student'
        ): ?>

            <a href="parcel_list.php">
                My Parcels
            </a>

            <a href="verify_parcel.php">
                Collect Parcel
            </a>

            <a href="maintenance_submit.php">
                New Request
            </a>

            <a href="maintenance_list.php">
                My Requests
            </a>

            <a href="my_leaves.php">
             Leave Requests
            </a>

            <a href="meals.php">
                Meals
            </a>

        <?php elseif (
            isset($_SESSION['role']) &&
            $_SESSION['role'] === 'admin'
        ): ?>

            <a href="admin_parcel.php">
                Add Parcel
            </a>

            <a href="admin_parcel_list.php">
                Manage Parcels
            </a>

            <a href="admin_maintenance.php">
                Maintenance
            </a>

            <a href="staff_add.php">
                Add Staff
            </a>

            <a href="staff_list.php">
                Manage Staff
            </a>

            <a href="admin_leave.php">
                Manage Leave Requests
            </a>

        <?php endif; ?>

        <a href="logout.php" class="nav-logout">
            Log Out
        </a>

    </div>

</nav>