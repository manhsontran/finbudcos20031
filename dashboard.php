<?php
session_start();
include 'db.php'; // Connect to the database

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch total income for the user
$total_income = 0;
$sql = "SELECT SUM(amount) AS total_income FROM Income WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total_income);
$stmt->fetch();
$stmt->close();

// Fetch username for the logged-in user
$username = '';
$sql = "SELECT username FROM User WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Fetch remaining budget details for the user
$remaining_budget = [];
$sql = "SELECT c.category_name, rb.remaining_budget
        FROM remaining_budget rb
        JOIN Budgets b ON rb.budget_id = b.budget_id
        JOIN Categories c ON b.category_id = c.category_id
        WHERE b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $remaining_budget[] = $row;
}
$stmt->close();

// Fetch financial goals for the logged-in user
$goals = [];
$sql = "SELECT goal_id, goal_name, target_amount, current_amount, target_date FROM financialgoals WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $goals[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finbud - Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        /* Navigation Bar Styling */
        nav.navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar-nav .nav-link {
            color: #007bff;
            margin-right: 20px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #0056b3;
        }

        .navbar-brand {
            color: #007bff;
            font-weight: 600;
            font-size: 1.4em;
        }

        /* Card Styling */
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
            padding: 12px 20px;
        }

        .card-body {
            padding: 20px;
        }

        .income-section {
            background-color: #e6f0ff;
            font-size: 1.3em;
            color: #0056b3;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Table Styling */
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            padding: 12px;
        }

        .table-striped tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table-bordered td, .table-bordered th {
            border: 1px solid #dee2e6;
        }

        /* Monthly Expense Chart Styling */
        .chart-section {
            margin-top: 20px;
        }

        .chart-section canvas {
            max-height: 450px;
            background-color: white;
            border-radius: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <a class="navbar-brand ml-3" href="#"><i class="fas fa-chart-bar"></i> FinBud Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="expensepage.php"><i class="fas fa-wallet"></i> Expenses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="budget.php"><i class="fas fa-chart-line"></i> Budgets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="finance_goalpage.php"><i class="fas fa-bullseye"></i> Goals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="income_page.php"><i class="fas fa-coins"></i> Income</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportpage.php"><i class="fas fa-file-alt"></i> Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="filter.php"><i class="fas fa-file-alt"></i> Filter</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a id="nav-link" class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Welcome Message -->
        <h2 class="text-center my-4">Hi, <?php echo htmlspecialchars($username); ?>!</h2>

        <!-- Total Income Section -->
        <div class="income-section">
            <p><i class="fas fa-wallet"></i> Total Income: <?php echo number_format($total_income, 2); ?></p>
        </div>

        <!-- Remaining Budget Section -->
        <div class="card">
    <div class="card-header">
        <i class="fas fa-piggy-bank"></i> Remaining Budget
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><i class="fas fa-tags"></i> Category Name</th>
                    <th><i class="fas fa-dollar-sign"></i> Remaining Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($remaining_budget) > 0): ?>
                    <?php foreach ($remaining_budget as $budget): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($budget['category_name']); ?></td>
                            <td><?php echo number_format($budget['remaining_budget'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center"><i class="fas fa-info-circle"></i> No remaining budget found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


        <!-- Financial Goals Section -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bullseye"></i> Your Financial Goals
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><i class="fas fa-trophy"></i> Goal Name</th>
                            <th><i class="fas fa-dollar-sign"></i> Target Amount</th>
                            <th><i class="fas fa-chart-line"></i> Current Amount</th>
                            <th><i class="fas fa-calendar-alt"></i> Target Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($goals) > 0): ?>
                            <?php foreach ($goals as $goal): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($goal['goal_name']); ?></td>
                                    <td><?php echo number_format($goal['target_amount'], 2); ?></td>
                                    <td><?php echo number_format($goal['current_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($goal['target_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No financial goals found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Monthly Expense Chart -->
        <div class="card chart-section">
            <div class="card-header">
                <i class="fas fa-calendar-day"></i> Monthly Expense Chart
            </div>
            <div class="card-body">
                <canvas id="monthlyExpenseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Monthly Expense Chart Script -->
    <script>
        fetch('generate_monthly_expense_data.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('monthlyExpenseChart').getContext('2d');
                const monthlyExpenseChart = new Chart(ctx, {
                    type: 'line', 
                    data: {
                        labels: data.months,
                        datasets: [{
                            label: 'Total Expense per Month',
                            data: data.expenses,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { title: { display: true, text: 'Month' }},
                            y: { 
                                title: { display: true, text: 'Total Expense' },
                                beginAtZero: true
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Error:', error));
    </script>
</body>
</html>



