<?php

require 'includes/session_check.php';
require 'config/db.php';

$student_id = $_SESSION['student_id'];

$cleanliness_options = ['Very Clean', 'Moderately Clean', 'Relaxed'];
$noise_options       = ['Low', 'Medium', 'High'];
$study_options       = ['Early Morning', 'Late Night', 'Flexible'];
$sleep_options       = ['Early Bird', 'Night Owl', 'Flexible'];

$error   = '';
$success = '';


$stmt = $pdo->prepare(
    'SELECT PreferenceID, Cleanliness, NoiseTolerance, StudyHabit, SleepingHabit, Others
     FROM preferences
     WHERE Student_ID = ?'
);

$stmt->execute([$student_id]);

$current = $stmt->fetch(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($current) {

        $error = 'Your roommate preferences have already been saved and cannot be changed.';

    } else {

        $cleanliness = trim($_POST['cleanliness'] ?? '');
        $noise       = trim($_POST['noise_tolerance'] ?? '');
        $study       = trim($_POST['study_habit'] ?? '');
        $sleep       = trim($_POST['sleeping_habit'] ?? '');
        $others      = trim($_POST['others'] ?? '');

        if (
            !in_array($cleanliness, $cleanliness_options, true) ||
            !in_array($noise, $noise_options, true) ||
            !in_array($study, $study_options, true) ||
            !in_array($sleep, $sleep_options, true)
        ) {

            $error = 'Please choose a valid option for every preference.';

        } else {

 
            $insert = $pdo->prepare(
                'INSERT INTO preferences
                 (Student_ID, Cleanliness, NoiseTolerance, StudyHabit, SleepingHabit, Others)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );

            try {

                $insert->execute([
                    $student_id,
                    $cleanliness,
                    $noise,
                    $study,
                    $sleep,
                    $others
                ]);

                $success = 'Your roommate preferences have been saved.';


                $stmt->execute([$student_id]);
                $current = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {

            
                $error = 'Your preferences could not be saved. Please try again.';
            }
        }
    }
}

function selected_if($value, $option)
{
    return $value === $option ? ' selected' : '';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        My Preferences — Roommate Recommendation
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="page">

    <h1>My Roommate Preferences</h1>

    <p class="dashboard-subtitle">
        These preferences are used to calculate a compatibility score
        with other students when you look for a roommate.
    </p>

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

    <?php if ($current): ?>

        <p class="hint">
            Your roommate preferences have already been saved.
            They cannot be changed.
        </p>

    <?php endif; ?>

    <form class="form-card" method="POST">

        <label>

            Cleanliness

            <select
                name="cleanliness"
                required
                <?= $current ? 'disabled' : '' ?>
            >

                <option value="">
                    Select an option
                </option>

                <?php foreach ($cleanliness_options as $opt): ?>

                    <option
                        value="<?= htmlspecialchars($opt) ?>"
                        <?= selected_if($current['Cleanliness'] ?? '', $opt) ?>
                    >
                        <?= htmlspecialchars($opt) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>


        <label>

            Noise Tolerance

            <select
                name="noise_tolerance"
                required
                <?= $current ? 'disabled' : '' ?>
            >

                <option value="">
                    Select an option
                </option>

                <?php foreach ($noise_options as $opt): ?>

                    <option
                        value="<?= htmlspecialchars($opt) ?>"
                        <?= selected_if($current['NoiseTolerance'] ?? '', $opt) ?>
                    >
                        <?= htmlspecialchars($opt) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>


        <label>

            Study Habit

            <select
                name="study_habit"
                required
                <?= $current ? 'disabled' : '' ?>
            >

                <option value="">
                    Select an option
                </option>

                <?php foreach ($study_options as $opt): ?>

                    <option
                        value="<?= htmlspecialchars($opt) ?>"
                        <?= selected_if($current['StudyHabit'] ?? '', $opt) ?>
                    >
                        <?= htmlspecialchars($opt) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>


        <label>

            Sleeping Habit

            <select
                name="sleeping_habit"
                required
                <?= $current ? 'disabled' : '' ?>
            >

                <option value="">
                    Select an option
                </option>

                <?php foreach ($sleep_options as $opt): ?>

                    <option
                        value="<?= htmlspecialchars($opt) ?>"
                        <?= selected_if($current['SleepingHabit'] ?? '', $opt) ?>
                    >
                        <?= htmlspecialchars($opt) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>


        <label>

            Anything else? (optional)

            <textarea
                name="others"
                rows="3"
                placeholder="e.g. Non-smoker, plays music in the evening..."
                <?= $current ? 'readonly' : '' ?>
            ><?= htmlspecialchars($current['Others'] ?? '') ?></textarea>

        </label>


        <?php if (!$current): ?>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Save Preferences
            </button>

        <?php endif; ?>

    </form>

</div>

</body>
</html>
