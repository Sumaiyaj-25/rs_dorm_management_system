<?php
require 'includes/session_check.php';
require 'config/db.php';
require 'includes/categorize.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $description = trim($_POST['description'] ?? '');
    $dormName    = trim($_POST['dorm_name'] ?? '');
    $roomNo      = trim($_POST['room_no'] ?? '');

   
    if ($description === '' || $dormName === '' || $roomNo === '') {

        $error = 'Please enter the dorm name, room number, and describe the issue.';

    } elseif (!in_array($dormName, ['Maloncho', 'Nikunjo'], true)) {

        $error = 'Please select either Maloncho or Nikunjo.';

    } else {

        $photoPath = null;


        if (!empty($_FILES['photo']['name'])) {

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            $ext = strtolower(
                pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION)
            );

            if (!in_array($ext, $allowed, true)) {

                $error = 'Photo must be a JPG, PNG, or WEBP file.';

            } elseif ($_FILES['photo']['size'] > 5 * 1024 * 1024) {

                $error = 'Photo must be smaller than 5MB.';

            } else {

                $uploadDir = __DIR__ . '/uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = uniqid('req_', true) . '.' . $ext;

                if (
                    move_uploaded_file(
                        $_FILES['photo']['tmp_name'],
                        $uploadDir . $filename
                    )
                ) {

                    $photoPath = 'uploads/' . $filename;

                } else {

                    $error = 'Photo upload failed. Please try again.';
                }
            }
        }


        if ($error === '') {

            $result = categorizeRequest($description);

            $stmt = $pdo->prepare(
                'INSERT INTO maintenance_request
                (
                    Description,
                    Photo,
                    Category,
                    Priority,
                    Status,
                    Student_ID,
                    Room_No,
                    Dorm_name
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $description,
                $photoPath,
                $result['category'],
                $result['priority'],
                'Submitted',
                $_SESSION['student_id'],
                $roomNo,
                $dormName
            ]);

            $success =
                "Request submitted successfully. " .
                "Category: {$result['category']} / " .
                "{$result['priority']} priority.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>New Maintenance Request</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>New Maintenance Request</h1>

    <?php if ($error): ?>

        <p class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endif; ?>


    <?php if ($success): ?>

        <p class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </p>

    <?php endif; ?>


    <form
        class="form-card"
        method="POST"
        enctype="multipart/form-data"
    >

        <!-- DORM -->

        <label>
            Dorm Name

            <select name="dorm_name" required>

                <option value="">
                    Select dorm
                </option>

                <option value="Maloncho">
                    Maloncho
                </option>

                <option value="Nikunjo">
                    Nikunjo
                </option>

            </select>

        </label>



        <label>
            Room Number

            <input
                type="text"
                name="room_no"
                placeholder="Enter your room number"
                required
            >

        </label>



        <label>
            Describe the issue

            <textarea
                name="description"
                rows="5"
                required
                placeholder="e.g. The bathroom tap has been leaking since this morning."
            ></textarea>

        </label>



        <label>
            Photo (optional)

            <input
                type="file"
                name="photo"
                accept=".jpg,.jpeg,.png,.webp"
            >

        </label>


        <p class="hint">
            Category and priority are assigned automatically based on your description.
        </p>


        <button
            type="submit"
            class="btn btn-primary"
        >
            Submit Request
        </button>

    </form>

</div>

</body>
</html>