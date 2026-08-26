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
                New Maintenance Request
            </a>

            <a href="maintenance_list.php">
                My Maintenance Requests
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
            <a href="admin_maintenance.php">
                Maintenance
            </a>

        <?php endif; ?>


        <a href="meals.php">
            Meals
        </a>

        <a href="logout.php" class="nav-logout">
            Log Out
        </a>

    </div>

</nav>