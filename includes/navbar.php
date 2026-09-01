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

            <a href="my_medical_records.php">
              My Medical Records
            </a>

            
            <a href="verify_parcel.php">
                Collect Parcel
            </a>

            <a href="maintenance_list.php">
                My Maintenance Requests
            </a>

            <a href="my_leaves.php">
             Leave Requests
            </a>

            <a href="meals.php">
                Meals
            </a>
            
            <a href="mood_log.php">
                My Mood
            </a>

            <a href="academic_resources.php">
                Academic Resources
            </a>

            <a href="room_transfer.php">
                Room Transfer
            </a>
            <a href="recommend.php">
                Find a Roommate
            </a>
            <a href="visitor_list.php">
                My Visitor
            </a>

            <a href="laundry_request.php">
                Laundry Requests
            </a>

            <a href="exit_clearance.php">
                Exit Clearance
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

            <a href="medical_records.php">
             Medical Records
            </a>

        
            <a href="admin_maintenance.php">
                Maintenance
            </a>
            <a href="admin_room_transfer.php">
                Room Transfers
            </a>

            <a href="admin_room_assignment.php">
                Room Assignment
            </a>
            <a href="admin_visitor.php">
                Visitor QR Checking
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

            <a href="counselor_dashboard.php">
                Well-Being Alerts
            </a>

            <a href="moderator_resources.php">
                Moderator Dashboard
            </a>

            <a href="gate_check.php">
                Gate Check
            </a>
            
            <a href="admin_library.php">
                Manage Library
            </a>
            
            <a href="admin_accounts.php">
                Manage Accounts
            </a>

            <a href="admin_laundry.php">
                Manage Laundry
            </a>

        <?php endif; ?>

        <a href="logout.php" class="nav-logout">
            Log Out
        </a>

    </div>

</nav>
