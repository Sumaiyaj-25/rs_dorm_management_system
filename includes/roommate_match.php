<?php

function calculateCompatibility(array $a, array $b): float
{
    $fields = ['Cleanliness', 'NoiseTolerance', 'StudyHabit', 'SleepingHabit'];

    $points = 0;

    foreach ($fields as $field) {
        $valA = $a[$field] ?? '';
        $valB = $b[$field] ?? '';

        if ($valA === $valB && $valA !== '') {
            $points += 1;
        } elseif ($valA === 'Flexible' || $valB === 'Flexible') {
            $points += 0.5;
        }
    }

    return round(($points / count($fields)) * 100, 2);
}
