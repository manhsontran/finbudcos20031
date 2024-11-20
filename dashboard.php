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
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            margin-top: 50px;
            max-width: 800px;
        }
        .section-title {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            border-radius: 5px 5px 0 0;
        }
        .table-section, .income-section, .chart-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 30px;
        }
        .income-section {
            background-color: #e3f2fd;
            font-weight: bold;
            font-size: 1.2em;
            color: #004085;
        }
    </style>
</head>
<body>
        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-white sticky-top">
        <a class="navbar-brand" href="#"><i class="fas fa-chart-bar"></i> FinBud Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="dashboard.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="expensepage.php">Expenses</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" href="budget.php">Budgets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="finance_goalpage.php">Goals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="income_page.php">Income</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportpage.php">Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="filter.html">Filter</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <!-- Welcome Message -->
        <h2>Hi, <?php echo htmlspecialchars($username); ?></h2>
        <!-- Total Income Section -->
        <div class="income-section">
            Total Income: <?php echo number_format($total_income, 2); ?>
        </div>

        <!-- Remaining Budget Table -->
        <div class="table-section">
            <div class="section-title">Remaining Budget</div>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Category Name</th>
                        <th>Remaining Amount</th>
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
                            <td colspan="2" class="text-center">No remaining budget found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Financial Goals Table -->
        <div class="table-section">
            <div class="section-title">Your Financial Goals</div>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Goal Name</th>
                        <th>Target Amount</th>
                        <th>Current Amount</th>
                        <th>Target Date</th>
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
                            <td colspan="5" class="text-center">No financial goals found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Monthly Expense Chart Section -->
        <div class="chart-section">
            <h3>Monthly Expense Chart</h3>
            <canvas id="monthlyExpenseChart"></canvas>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Monthly Expense Chart Script -->
    <script>
    // Fetch data from the server for the chart
    fetch('generate_monthly_expense_data.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('monthlyExpenseChart').getContext('2d');
            const monthlyExpenseChart = new Chart(ctx, {
                type: 'line', // Choose 'line' or 'bar'
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
                            title: { display: true, text: 'Total Expense (VNĐ)' },
                            beginAtZero: true // Ensure y-axis starts from 0
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error:', error));
</script>

</body>
</html>
