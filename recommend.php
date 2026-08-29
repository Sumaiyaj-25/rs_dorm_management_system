<?php

require 'includes/session_check.php';
require 'config/db.php';
require 'includes/roommate_match.php';

$student_id = $_SESSION['student_id'];

$my_pref_stmt = $pdo->prepare(
    'SELECT Cleanliness, NoiseTolerance, StudyHabit, SleepingHabit
     FROM preferences
     WHERE Student_ID = ?'
);
$my_pref_stmt->execute([$student_id]);
$my_pref = $my_pref_stmt->fetch(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $my_pref) {

    $target_id = intval($_POST['target_id'] ?? 0);

    if ($target_id <= 0 || $target_id === (int) $student_id) {
        $error = 'Invalid roommate selection.';
    } else {

        $target_pref_stmt = $pdo->prepare(
            'SELECT Cleanliness, NoiseTolerance, StudyHabit, SleepingHabit
             FROM preferences
             WHERE Student_ID = ?'
        );
        $target_pref_stmt->execute([$target_id]);
        $target_pref = $target_pref_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target_pref) {
            $error = 'This student does not have roommate preferences yet.';
        } else {


            $existing = $pdo->prepare(
                'SELECT Requesting_Student_ID, Potential_Roommate_ID, Status
                 FROM compatible
                 WHERE
                    (Requesting_Student_ID = ? AND Potential_Roommate_ID = ?)
                    OR
                    (Requesting_Student_ID = ? AND Potential_Roommate_ID = ?)'
            );
            $existing->execute([
                $student_id,
                $target_id,
                $target_id,
                $student_id
            ]);

            $existing_request = $existing->fetch(PDO::FETCH_ASSOC);

            if ($existing_request) {

                if (in_array(
                    $existing_request['Status'],
                    ['Pending', 'Accepted'],
                    true
                )) {
                    $error = 'There is already an active roommate request with this student.';
                } else {

                    if (
                        $existing_request['Requesting_Student_ID'] == $student_id &&
                        $existing_request['Potential_Roommate_ID'] == $target_id
                    ) {
                        $score = calculateCompatibility($my_pref, $target_pref);

                        $update = $pdo->prepare(
                            'UPDATE compatible
                             SET Score = ?, Status = ?
                             WHERE Requesting_Student_ID = ?
                               AND Potential_Roommate_ID = ?'
                        );
                        $update->execute([
                            $score,
                            'Pending',
                            $student_id,
                            $target_id
                        ]);

                        $success = 'Roommate request sent again!';
                    } else {
                        $error = 'This student previously rejected a request from you.';
                    }
                }

            } else {

                $score = calculateCompatibility($my_pref, $target_pref);

                $insert = $pdo->prepare(
                    'INSERT INTO compatible
                     (Requesting_Student_ID, Potential_Roommate_ID, Score, Status)
                     VALUES (?, ?, ?, ?)'
                );

                $insert->execute([
                    $student_id,
                    $target_id,
                    $score,
                    'Pending'
                ]);

                $success = 'Roommate request sent!';
            }
        }
    }
}

$candidates = [];

if ($my_pref) {

    $me = $pdo->prepare(
        'SELECT Gender
         FROM student
         WHERE Student_ID = ?'
    );
    $me->execute([$student_id]);
    $myGender = $me->fetchColumn();

    if ($myGender) {

        $cand_stmt = $pdo->prepare(
            'SELECT
                s.Student_ID,
                s.FirstName,
                s.LastName,
                s.Department,
                s.Gender,
                p.Cleanliness,
                p.NoiseTolerance,
                p.StudyHabit,
                p.SleepingHabit
             FROM student s
             JOIN preferences p
               ON p.Student_ID = s.Student_ID
             WHERE s.Student_ID <> ?
               AND s.Gender = ?
               AND NOT EXISTS (
                    SELECT 1
                    FROM compatible m
                    WHERE
                        (
                            (m.Requesting_Student_ID = ?
                             AND m.Potential_Roommate_ID = s.Student_ID)
                            OR
                            (m.Requesting_Student_ID = s.Student_ID
                             AND m.Potential_Roommate_ID = ?)
                        )
                        AND m.Status IN (\'Pending\', \'Accepted\')
               )'
        );

        $cand_stmt->execute([
            $student_id,
            $myGender,
            $student_id,
            $student_id
        ]);

    } else {

        $cand_stmt = $pdo->prepare(
            'SELECT
                s.Student_ID,
                s.FirstName,
                s.LastName,
                s.Department,
                s.Gender,
                p.Cleanliness,
                p.NoiseTolerance,
                p.StudyHabit,
                p.SleepingHabit
             FROM student s
             JOIN preferences p
               ON p.Student_ID = s.Student_ID
             WHERE s.Student_ID <> ?
               AND NOT EXISTS (
                    SELECT 1
                    FROM compatible m
                    WHERE
                        (
                            (m.Requesting_Student_ID = ?
                             AND m.Potential_Roommate_ID = s.Student_ID)
                            OR
                            (m.Requesting_Student_ID = s.Student_ID
                             AND m.Potential_Roommate_ID = ?)
                        )
                        AND m.Status IN (\'Pending\', \'Accepted\')
               )'
        );

        $cand_stmt->execute([
            $student_id,
            $student_id,
            $student_id
        ]);
    }

    $rows = $cand_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {

        $row['Score'] = calculateCompatibility($my_pref, $row);

        if ($row['Score'] >= 90) {
            $row['MatchLabel'] = 'Excellent Match';
            $row['MatchMessage'] = 'Excellent compatibility based on your preferences.';
        } elseif ($row['Score'] >= 75) {
            $row['MatchLabel'] = 'Very Good Match';
            $row['MatchMessage'] = 'Very good compatibility based on your preferences.';
        } elseif ($row['Score'] >= 50) {
            $row['MatchLabel'] = 'Good Match';
            $row['MatchMessage'] = 'Good compatibility based on your preferences.';
        } else {
            $row['MatchLabel'] = 'Low Match';
            $row['MatchMessage'] = 'Lower compatibility based on your preferences.';
        }

        $candidates[] = $row;
    }

    usort($candidates, function ($a, $b) {
        return $b['Score'] <=> $a['Score'];
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find a Roommate — Roommate Recommendation</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>Find a Roommate</h1>

    <p class="dashboard-subtitle">
        Find students whose roommate preferences are most compatible with yours.
    </p>

    <?php if (!$my_pref): ?>

        <p class="empty-state">
            You haven't set your preferences yet.
            <a href="preferences.php">Set them now</a>
            to see your roommate matches.
        </p>

    <?php else: ?>

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

        <?php if (empty($candidates)): ?>

            <p class="empty-state">
                No available students to match with right now.
            </p>

        <?php else: ?>

            <div class="request-list">

                <?php foreach ($candidates as $c): ?>

                    <div class="request-card">

                        <div class="request-header">

                            <span class="badge badge-score">
                                <?= htmlspecialchars((string) $c['Score']) ?>% Match
                            </span>

                            <span class="badge badge-category">
                                <?= htmlspecialchars($c['Department'] ?? 'Student') ?>
                            </span>

                        </div>

                        <p class="request-desc">
                            <strong>
                                <?= htmlspecialchars(
                                    $c['FirstName'] . ' ' . $c['LastName']
                                ) ?>
                            </strong>
                        </p>

                        <p class="request-desc">
                            <?= htmlspecialchars($c['MatchLabel']) ?>
                        </p>

                        <p class="request-meta">
                            <?= htmlspecialchars($c['MatchMessage']) ?>
                        </p>

                        <form method="POST" style="margin-top: 15px;">

                            <input
                                type="hidden"
                                name="target_id"
                                value="<?= (int) $c['Student_ID'] ?>"
                            >

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Send Roommate Request
                            </button>

                        </form>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>