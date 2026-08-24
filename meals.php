<?php
require 'includes/session_check.php';
require 'config/db.php';

$student_id = $_SESSION['student_id'];
$first_name = $_SESSION['name'] ?? 'User';

// Get my booked meals
$my_meals_stmt = $pdo->prepare("SELECT * FROM meal WHERE student_id = ? AND claim_status = FALSE ORDER BY meal_serve_date ASC, token_no ASC");
$my_meals_stmt->execute([$student_id]);
$my_meals = $my_meals_stmt->fetchAll();

// Get available meals for claim
$available_meals_stmt = $pdo->prepare("SELECT meal_serve_date, meal_type, COUNT(*) as available_count FROM meal WHERE is_released = TRUE AND claim_status = FALSE AND student_id != ? GROUP BY meal_serve_date, meal_type ORDER BY meal_serve_date ASC");
$available_meals_stmt->execute([$student_id]);
$available_meals = $available_meals_stmt->fetchAll();

// Get meals I have claimed
$my_claims_stmt = $pdo->prepare("SELECT * FROM meal WHERE claimed_by = ? ORDER BY meal_serve_date ASC");
$my_claims_stmt->execute([$student_id]);
$my_claims = $my_claims_stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meals — Dorm Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; min-width: 300px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f4f4f4; }
        .btn { padding: 5px 10px; border: none; background: #007bff; color: white; cursor: pointer; border-radius: 4px; text-decoration: none; display: inline-block; }
        .btn:hover { background: #0056b3; }
        .btn-release { background: #ffc107; color: #000; }
        .btn-release:hover { background: #e0a800; }
        .btn-claim { background: #28a745; }
        .btn-claim:hover { background: #218838; }
        .msg { padding: 10px; margin-top: 10px; border-radius: 4px; }
        .msg-success { background: #d4edda; color: #155724; }
        .msg-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="page" style="padding: 20px;">
        <h1>Dining Token & Meal Swap Exchange</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="msg msg-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="msg msg-error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div class="container">
            <!-- Book Meal -->
            <div class="card">
                <h2>Book a Meal</h2>
                <form action="book_meal.php" method="POST" style="margin-top: 10px;">
                    <div style="margin-bottom: 10px;">
                        <label>Date:</label><br>
                        <input type="date" name="meal_serve_date" required style="width: 100%; padding: 8px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Meal Type:</label><br>
                        <select name="meal_type" required style="width: 100%; padding: 8px;">
                            <option value="Breakfast">Breakfast</option>
                            <option value="Lunch">Lunch</option>
                            <option value="Dinner">Dinner</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Book Meal</button>
                </form>
            </div>

            <!-- My Meals -->
            <div class="card">
                <h2>My Booked Meals</h2>
                <?php if (count($my_meals) > 0): ?>
                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($my_meals as $meal): ?>
                            <tr>
                                <td><?= htmlspecialchars($meal['meal_serve_date']) ?></td>
                                <td><?= htmlspecialchars($meal['meal_type']) ?></td>
                                <td><?= htmlspecialchars($meal['released_status']) ?></td>
                                <td>
                                    <?php if (!$meal['is_released']): ?>
                                        <form action="release_meal.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="token_no" value="<?= $meal['token_no'] ?>">
                                            <button type="submit" class="btn btn-release">Release</button>
                                        </form>
                                    <?php else: ?>
                                        <em>N/A</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>No booked meals.</p>
                <?php endif; ?>
                
                <h3 style="margin-top:20px;">Meals I Claimed</h3>
                <?php if (count($my_claims) > 0): ?>
                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                        </tr>
                        <?php foreach ($my_claims as $claim): ?>
                            <tr>
                                <td><?= htmlspecialchars($claim['meal_serve_date']) ?></td>
                                <td><?= htmlspecialchars($claim['meal_type']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>No claimed meals.</p>
                <?php endif; ?>
            </div>

            <!-- Available Meals Pool -->
            <div class="card">
                <h2>Meal Exchange Pool</h2>
                <?php if (count($available_meals) > 0): ?>
                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Available</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($available_meals as $avail): ?>
                            <tr>
                                <td><?= htmlspecialchars($avail['meal_serve_date']) ?></td>
                                <td><?= htmlspecialchars($avail['meal_type']) ?></td>
                                <td><?= htmlspecialchars($avail['available_count']) ?></td>
                                <td>
                                    <form action="claim_meal.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="meal_serve_date" value="<?= $avail['meal_serve_date'] ?>">
                                        <input type="hidden" name="meal_type" value="<?= $avail['meal_type'] ?>">
                                        <button type="submit" class="btn btn-claim">Claim</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>No available meals right now.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
