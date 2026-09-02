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

            <div class="nav-dropdown">
                <button class="nav-dropdown-btn">
                    Housing & Room ▾
                </button>

                <div class="nav-dropdown-content">
                    <a href="room_transfer.php">
                        Room Transfer
                    </a>

                    <a href="recommend.php">
                        Find a Roommate
                    </a>
                </div>
            </div>

            <a href="visitor_list.php">
                My Visitors
            </a>

            <div class="nav-dropdown">
                <button class="nav-dropdown-btn">
                    Services ▾
                </button>

                <div class="nav-dropdown-content">
                    <a href="parcel_list.php">
                        My Parcel
                    </a>

                    <a href="verify_parcel.php">
                        Collect Parcel
                    </a>

                    <a href="maintenance_list.php">
                        Maintenance Request
                    </a>

                    <a href="laundry_request.php">
                        Laundry Request
                    </a>

                    <a href="my_medical_records.php">
                        Medical Records
                    </a>

                    <a href="my_leaves.php">
                        Leave Requests
                    </a>

                    <a href="exit_clearance.php">
                        Exit Clearance
                    </a>
                </div>
            </div>

            <div class="nav-dropdown">
                <button class="nav-dropdown-btn">
                    Student Life ▾
                </button>

                <div class="nav-dropdown-content">
                    <a href="meals.php">
                        Meal Information
                    </a>

                    <a href="mood_log.php">
                        My Mood
                    </a>

                    <a href="academic_resources.php">
                        Academic Resources
                    </a>
                </div>
            </div>

        <?php elseif (
            isset($_SESSION['role']) &&
            $_SESSION['role'] === 'admin'
        ): ?>

            <div class="nav-dropdown">
                <button class="nav-dropdown-btn">
                    Resident Management ▾
                </button>

                <div class="nav-dropdown-content">
                    <a href="admin_accounts.php">
                        Manage Accounts
                    </a>

                    <a href="staff_add.php">
                        Add Staff
                    </a>

                    <a href="staff_list.php">
                        Manage Staff
                    </a>
                </div>
            </div>

            <a href="admin_room_transfer.php">
                Room Transfer
            </a>

            <a href="admin_room_assignment.php">
                Room Assistance
            </a>

            <div class="nav-dropdown">
                <button class="nav-dropdown-btn">
                    Operations ▾
                </button>

                <div class="nav-dropdown-content">
                    <a href="admin_parcel.php">
                        Add Parcel
                    </a>

                    <a href="admin_parcel_list.php">
                        Manage Parcel
                    </a>

                    <a href="admin_maintenance.php">
                        Maintenance
                    </a>

                    <a href="admin_leave.php">
                        Manage Leave Requests
                    </a>

                    <a href="admin_laundry.php">
                        Manage Laundry
                    </a>
                </div>
            </div>

            <a href="medical_records.php">
                Medical Records
            </a>

            <div class="nav-dropdown">
                <button class="nav-dropdown-btn">
                    Security ▾
                </button>

                <div class="nav-dropdown-content">
                    <a href="admin_visitor.php">
                        Visitor QR
                    </a>

                    <a href="gate_check.php">
                        Gate Check
                    </a>
                </div>
            </div>

            <div class="nav-dropdown">
                <button class="nav-dropdown-btn">
                    Resources ▾
                </button>

                <div class="nav-dropdown-content">
                    <a href="admin_library.php">
                        Manage Library
                    </a>

                    <a href="moderator_resources.php">
                        Moderator Dashboard
                    </a>

                    <a href="counselor_dashboard.php">
                        Well-Being Alerts
                    </a>
                </div>
            </div>

        <?php endif; ?>

        <a href="logout.php" class="nav-logout">
            Logout
        </a>

    </div>

</nav>
