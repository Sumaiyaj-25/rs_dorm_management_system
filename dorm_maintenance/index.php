<?php
session_start();
header('Location: ' . (isset($_SESSION['student_id']) ? 'dashboard.php' : 'login.php'));
exit;
