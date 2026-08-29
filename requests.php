<?php

require 'includes/session_check.php';
require 'config/db.php';

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $requesting_student_id = intval(
        $_POST['requesting_student_id'] ?? 0
    );

    $new_status = $_POST['new_status'] ?? '';

    $allowed_statuses = ['Accepted', 'Rejected'];

    if (
        $requesting_student_id > 0 &&
        in_array($new_status, $allowed_statuses, true)
    ) {

        $update = $pdo->prepare(
            'UPDATE compatible
             SET Status = ?
             WHERE Requesting_Student_ID = ?
               AND Potential_Roommate_ID = ?
               AND Status = \'Pending\''
        );

        $update->execute([
            $new_status,
            $requesting_student_id,
            $student_id
        ]);
    }

    header('Location: requests.php');
    exit;
}


/* ----------------------------------------------------------
   Statistics
---------------------------------------------------------- */

$stats_stmt = $pdo->prepare(
    "SELECT
        (SELECT COUNT(*)
         FROM compatible
         WHERE Potential_Roommate_ID = ?
           AND Status = 'Pending') AS incoming_pending,

        (SELECT COUNT(*)
         FROM compatible
         WHERE Requesting_Student_ID = ?
           AND Status = 'Pending') AS sent_pending,

        (SELECT COUNT(*)
         FROM compatible
         WHERE
            (Requesting_Student_ID = ?
             OR Potential_Roommate_ID = ?)
           AND Status = 'Accepted') AS accepted_count"
);

$stats_stmt->execute([
    $student_id,
    $student_id,
    $student_id,
    $student_id
]);

$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);




$incoming_stmt = $pdo->prepare(
    "SELECT
        m.Requesting_Student_ID,
        m.Potential_Roommate_ID,
        m.Score,
        m.Status,
        s.Student_ID,
        s.FirstName,
        s.LastName,
        s.Department
     FROM compatible m
     JOIN student s
       ON s.Student_ID = m.Requesting_Student_ID
     WHERE m.Potential_Roommate_ID = ?
       AND m.Status = 'Pending'
     ORDER BY m.Score DESC"
);

$incoming_stmt->execute([$student_id]);

$incoming = $incoming_stmt->fetchAll(PDO::FETCH_ASSOC);

$sent_stmt = $pdo->prepare(
    "SELECT
        m.Requesting_Student_ID,
        m.Potential_Roommate_ID,
        m.Score,
        m.Status,
        s.Student_ID,
        s.FirstName,
        s.LastName,
        s.Department
     FROM compatible m
     JOIN student s
       ON s.Student_ID = m.Potential_Roommate_ID
     WHERE m.Requesting_Student_ID = ?
     ORDER BY m.Status, m.Score DESC"
);

$sent_stmt->execute([$student_id]);

$sent = $sent_stmt->fetchAll(PDO::FETCH_ASSOC);



$accepted_stmt = $pdo->prepare(
    "SELECT
        m.Requesting_Student_ID,
        m.Potential_Roommate_ID,
        m.Score,
        s.FirstName,
        s.LastName,
        s.Department
     FROM compatible m
     JOIN student s
       ON s.Student_ID = m.Potential_Roommate_ID
     WHERE m.Requesting_Student_ID = ?
       AND m.Status = 'Accepted'

     UNION

     SELECT
        m.Requesting_Student_ID,
        m.Potential_Roommate_ID,
        m.Score,
        s.FirstName,
        s.LastName,
        s.Department
     FROM compatible m
     JOIN student s
       ON s.Student_ID = m.Requesting_Student_ID
     WHERE m.Potential_Roommate_ID = ?
       AND m.Status = 'Accepted'"
);

$accepted_stmt->execute([
    $student_id,
    $student_id
]);

$accepted = $accepted_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        My Requests — Roommate Recommendation
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>My Roommate Requests</h1>


    <div class="card-grid">

        <div class="stat-card">
            <span class="stat-number">
                <?= (int) ($stats['incoming_pending'] ?? 0) ?>
            </span>

            <span class="stat-label">
                Waiting on you
            </span>
        </div>


        <div class="stat-card">
            <span class="stat-number">
                <?= (int) ($stats['sent_pending'] ?? 0) ?>
            </span>

            <span class="stat-label">
                Waiting on them
            </span>
        </div>


        <div class="stat-card">
            <span class="stat-number">
                <?= (int) ($stats['accepted_count'] ?? 0) ?>
            </span>

            <span class="stat-label">
                Accepted matches
            </span>
        </div>

    </div>

    <div class="dashboard-section">

        <h2>Accepted Matches</h2>

        <?php if (empty($accepted)): ?>

            <p class="empty-state">
                You don't have any accepted roommate matches yet.
            </p>

        <?php else: ?>

            <div class="request-list">

                <?php foreach ($accepted as $r): ?>

                    <div class="request-card">

                        <div class="request-header">

                            <span class="badge badge-status-accepted">
                                Accepted
                            </span>

                            <span class="badge badge-score">
                                <?= htmlspecialchars((string) $r['Score']) ?>% Match
                            </span>

                        </div>

                        <p class="request-desc">

                            <strong>
                                <?= htmlspecialchars(
                                    $r['FirstName'] . ' ' . $r['LastName']
                                ) ?>
                            </strong>

                            is your accepted roommate match.

                        </p>

                        <div class="request-meta">

                            <span>
                                <?= htmlspecialchars(
                                    $r['Department'] ?? 'Student'
                                ) ?>
                            </span>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>


    <div class="dashboard-section">

        <h2>Requests Sent To You</h2>

        <?php if (empty($incoming)): ?>

            <p class="empty-state">
                No pending requests right now.
            </p>

        <?php else: ?>

            <div class="request-list">

                <?php foreach ($incoming as $r): ?>

                    <div class="request-card">

                        <div class="request-header">

                            <span class="badge badge-score">
                                <?= htmlspecialchars((string) $r['Score']) ?>% Match
                            </span>

                            <span class="badge badge-category">
                                <?= htmlspecialchars(
                                    $r['Department'] ?? 'Student'
                                ) ?>
                            </span>

                        </div>

                        <p class="request-desc">

                            <strong>
                                <?= htmlspecialchars(
                                    $r['FirstName'] . ' ' . $r['LastName']
                                ) ?>
                            </strong>

                            wants to be your roommate.

                        </p>

                        <form
                            method="POST"
                            style="margin-top: 15px; display:flex; gap:10px;"
                        >

                            <input
                                type="hidden"
                                name="requesting_student_id"
                                value="<?= (int) $r['Requesting_Student_ID'] ?>"
                            >

                            <button
                                type="submit"
                                name="new_status"
                                value="Accepted"
                                class="btn btn-primary"
                            >
                                Accept
                            </button>

                            <button
                                type="submit"
                                name="new_status"
                                value="Rejected"
                                class="btn btn-secondary"
                            >
                                Decline
                            </button>

                        </form>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>



    <div class="dashboard-section">

        <h2>Requests You've Sent</h2>

        <?php if (empty($sent)): ?>

            <p class="empty-state">

                You haven't sent any requests yet.
                <a href="recommend.php">
                    Find a roommate
                </a>.

            </p>

        <?php else: ?>

            <div class="request-list">

                <?php foreach ($sent as $r): ?>

                    <div class="request-card">

                        <div class="request-header">

                            <span class="badge badge-score">
                                <?= htmlspecialchars((string) $r['Score']) ?>% Match
                            </span>

                            <span class="badge badge-status-<?= strtolower($r['Status']) ?>">
                                <?= htmlspecialchars($r['Status']) ?>
                            </span>

                        </div>

                        <p class="request-desc">

                            <strong>
                                <?= htmlspecialchars(
                                    $r['FirstName'] . ' ' . $r['LastName']
                                ) ?>
                            </strong>

                            (<?= htmlspecialchars(
                                $r['Department'] ?? 'Student'
                            ) ?>)

                        </p>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>