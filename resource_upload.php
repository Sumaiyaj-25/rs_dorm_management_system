<?php
require 'includes/session_check.php';
require 'config/db.php';

// Only students can upload resources
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: dashboard.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resource_type = trim($_POST['resource_type']);
    $course_reference = trim($_POST['course_reference']);
    
    if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['resource_file']['tmp_name'];
        $file_name = $_FILES['resource_file']['name'];
        
        // Extract extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
        
        if (in_array($file_ext, $allowed_exts)) {
            // Generate unique filename
            $new_file_name = uniqid('res_') . '.' . $file_ext;
            $upload_dir = 'uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $dest_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $dest_path)) {
                // Insert into database
                try {
                    $stmt = $pdo->prepare("INSERT INTO academic_resources (resource_type, course_reference, submitted_by, file_path, approval_status) VALUES (?, ?, ?, ?, 'Pending')");
                    if ($stmt->execute([$resource_type, $course_reference, $student_id, $new_file_name])) {
                        $message = "Resource uploaded successfully! It is now pending moderator approval.";
                    } else {
                        $error = "Failed to save resource info to the database.";
                    }
                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                $error = "Failed to move the uploaded file.";
            }
        } else {
            $error = "Invalid file type. Only PDF, DOCX, and images (JPG, PNG) are allowed.";
        }
    } else {
        $error = "Please select a valid file to upload.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Resource - Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-submit { background: #3498db; color: #fff; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .btn-submit:hover { background: #2980b9; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #3498db; text-decoration: none; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <a href="academic_resources.php" class="btn-back">&larr; Back to Resources</a>
        <h2>Upload Academic Resource</h2>
        <p>Share your class notes or exam prep materials. Top contributors earn rewards!</p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="resource_type">Resource Type</label>
                <select name="resource_type" id="resource_type" required>
                    <option value="">Select Type...</option>
                    <option value="Class Notes">Class Notes</option>
                    <option value="Exam Prep">Exam Prep Material</option>
                    <option value="Syllabus">Syllabus</option>
                    <option value="Assignment Solution">Assignment Solution</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="course_reference">Course Reference (e.g. CSE101)</label>
                <input type="text" name="course_reference" id="course_reference" required placeholder="Enter course code">
            </div>

            <div class="form-group">
                <label for="resource_file">File (PDF, DOCX, JPG, PNG)</label>
                <input type="file" name="resource_file" id="resource_file" accept=".pdf,.docx,.jpg,.jpeg,.png" required>
            </div>

            <button type="submit" class="btn-submit">Upload Resource</button>
        </form>
    </div>
</body>
</html>
