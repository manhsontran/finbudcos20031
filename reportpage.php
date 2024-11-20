<?php
session_start();
include 'db.php'; // Connect to the database

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get filter dates if provided
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Query total expenses from the expenses_transaction table for the current user and filter by date
$show_total_expenses = false; // Biến để kiểm soát việc hiển thị bảng Total Expenses
$total_expenses_result = null;

if ($start_date && $end_date) {
    $show_total_expenses = true; // Hiển thị bảng khi có filter
    $sql = "SELECT SUM(amount) AS total_expenses FROM expenses_transaction WHERE user_id = ? AND expense_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $start_date, $end_date);
    $stmt->execute();
    $total_expenses_result = $stmt->get_result();
}

// Query individual transactions in the filtered date range
$show_transactions = false; // Biến để kiểm soát việc hiển thị bảng Expense Transactions
$transaction_result = null;

if ($start_date && $end_date) {
    $show_transactions = true; // Hiển thị bảng khi có filter
    $transaction_sql = "SELECT expense_transaction_id, amount, expense_date, description FROM expenses_transaction WHERE user_id = ? AND expense_date BETWEEN ? AND ?";
    $transaction_stmt = $conn->prepare($transaction_sql);
    $transaction_stmt->bind_param("iss", $user_id, $start_date, $end_date);
    $transaction_stmt->execute();
    $transaction_result = $transaction_stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">FinBud Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="expensepage.php">Expenses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="budget.php">Budgets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="finance_goalpage.php">Goals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="income_page.php">Income</a>
                </li>
                <li class="nav-item active">
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

    <div class="container mt-5">
        <h2>Financial Report</h2>
        <!-- Date Filter Form -->
        <div class="container mt-4">
            <form method="GET" action="reportpage.php" class="form-inline">
                <div class="form-group mr-2">
                    <label for="start_date" class="mr-2">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="end_date" class="mr-2">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <!-- Display Total Expenses if Filter is Applied -->
        <?php if ($show_total_expenses && $total_expenses_result): ?>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Total Expenses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($total_expenses_result->num_rows > 0) {
                            while ($row = $total_expenses_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . number_format($row['total_expenses'], 2) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td class='text-center'>No report data found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <!-- Display Individual Transactions if Filter is Applied -->
        <?php if ($show_transactions && $transaction_result): ?>
            <h4>Expense Transactions</h4>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($transaction_result->num_rows > 0) {
                            while ($row = $transaction_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['expense_transaction_id']) . "</td>
                                        <td>" . number_format($row['amount'], 2) . "</td>
                                        <td>" . htmlspecialchars($row['expense_date']) . "</td>
                                        <td>" . htmlspecialchars($row['description']) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>No expense transactions found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <div class="container mt-5">
    <h3>Monthly Expense Chart</h3>
    <canvas id="monthlyExpenseChart"></canvas>
</div>

<script>
    // Fetch data from the server
    fetch('generate_monthly_expense_data.php')
        .then(response => response.json())
        .then(data => {
            // Create the chart after retrieving data
            const ctx = document.getElementById('monthlyExpenseChart').getContext('2d');
            const monthlyExpenseChart = new Chart(ctx, {
                type: 'line', // Or 'bar' depending on preference
                data: {
                    labels: data.months,
                    datasets: [{
                        label: 'Total Expense Month',
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
                        y: { title: { display: true, text: 'Total Expense (VNĐ)' }}
                    }
                }
            });
        })
        .catch(error => console.error('Error:', error));
</script>

</body>
</html>

<?php
// Close database connections
if (isset($stmt)) {
    $stmt->close();
}
if (isset($transaction_stmt)) {
    $transaction_stmt->close();
}
$conn->close();
?>
