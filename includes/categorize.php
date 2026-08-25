<?php

function categorizeRequest(string $description): array
{
    $text = strtolower($description);

    $categories = [
        'Electrical' => ['spark', 'wire', 'wiring', 'socket', 'outlet', 'short circuit',
                          'electric', 'electricity', 'power', 'fuse', 'switch', 'bulb', 'light not working'],
        'Plumbing'   => ['leak', 'leaking', 'pipe', 'tap', 'faucet', 'toilet', 'flush',
                          'water', 'drain', 'clog', 'clogged', 'sink', 'shower', 'flood', 'flooding'],
        'Furniture'  => ['chair', 'table', 'bed', 'desk', 'wardrobe', 'mattress',
                          'cupboard', 'shelf', 'furniture'],
        'Structural' => ['door', 'window', 'lock', 'wall', 'ceiling', 'crack', 'glass', 'broken'],
        'Cleaning'   => ['dirty', 'trash', 'garbage', 'smell', 'odor', 'pest', 'cockroach',
                          'insect', 'mold', 'mould', 'dust'],
        'Internet'   => ['wifi', 'internet', 'network', 'router', 'lan', 'ethernet'],
    ];


    $highPriorityKeywords = [
        'spark', 'sparking', 'fire', 'smoke', 'shock', 'gas leak', 'gas smell',
        'flood', 'flooding', 'no water', 'no electricity', 'short circuit',
        'exposed wire', 'burning smell', 'ceiling collapse', 'can\'t lock', 'security',
    ];

    $mediumPriorityKeywords = [
        'leak', 'leaking', 'not working', 'broken', 'clogged', 'no internet',
        'no wifi', 'flickering', 'loose',
    ];


    $category = 'Other';
    foreach ($categories as $catName => $keywords) {
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) {
                $category = $catName;
                break 2;
            }
        }
    }

    $priority = 'Low';

    foreach ($highPriorityKeywords as $kw) {
        if (str_contains($text, $kw)) {
            $priority = 'High';
            break;
        }
    }

    if ($priority !== 'High') {
        foreach ($mediumPriorityKeywords as $kw) {
            if (str_contains($text, $kw)) {
                $priority = 'Medium';
                break;
            }
        }

        if ($priority === 'Low' && in_array($category, ['Electrical', 'Plumbing'], true)) {
            $priority = 'Medium';
        }
    }

    return ['category' => $category, 'priority' => $priority];
}
