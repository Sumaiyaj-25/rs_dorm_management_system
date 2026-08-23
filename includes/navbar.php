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

            <a href="maintenance_submit.php">
                New Request
            </a>

            <a href="maintenance_list.php">
                My Requests
            </a>


        <?php elseif (
            isset($_SESSION['role']) &&
            $_SESSION['role'] === 'admin'
        ): ?>

            <a href="admin_maintenance.php">
                Maintenance
            </a>

        <?php endif; ?>


        <a href="logout.php" class="nav-logout">
            Log Out
        </a>

    </div>

</nav>
