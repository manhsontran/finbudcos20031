<?php
session_start();
include 'db.php'; 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_income'])) {
    $income_category_id = $_POST['income_category_id'];
    $amount = $_POST['amount'];
    $income_date = $_POST['income_date'];
    $description = $_POST['description'];


    $sql = "INSERT INTO income (user_id, income_category_id, amount, income_date, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $user_id, $income_category_id, $amount, $income_date, $description);

    if ($stmt->execute()) {
        $income_success = "Income added successfully!";
    } else {
        $income_error = "Error: " . $stmt->error;
    }

    $stmt->close();
        header("Location: add_income.php");
    exit();
}
$incomes = [];
$sql = "SELECT i.income_id, ic.category_name, i.amount, i.income_date, i.description 
        FROM income i
        JOIN income_category ic ON i.income_category_id = ic.income_category_id
        WHERE i.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $incomes[] = $row;
}
$stmt->close();

$sql_total = "SELECT total_income FROM total_income WHERE user_id = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_income = 0;

if ($row = $result_total->fetch_assoc()) {
    $total_income = $row['total_income'];
}

$stmt_total->close();
$categories = [];
$sql = "SELECT income_category_id, category_name FROM income_category";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Income</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">FinBud Dashboard</a>
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
                <li class="nav-item">
                    <a class="nav-link" href="budget.php">Budgets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="finance_goalpage.php">Goals</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="add_income.php">Income</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportpage.php">Reports</a>
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
    <h2>Add Income</h2>

    <?php if (isset($income_success)) echo "<div class='alert alert-success'>$income_success</div>"; ?>
    <?php if (isset($income_error)) echo "<div class='alert alert-danger'>$income_error</div>"; ?>

    <form action="add_income.php" method="post">
        <input type="hidden" name="add_income" value="1">
        <div class="form-group">
            <label for="income_category_id">Income Category:</label>
            <select name="income_category_id" id="income_category_id" class="form-control" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['income_category_id']); ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="income_date">Income Date:</label>
            <input type="date" name="income_date" id="income_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <input type="text" name="description" id="description" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Add Income</button>
    </form>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Income ID</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($incomes) > 0): ?>
                <?php foreach ($incomes as $income): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($income['income_id']); ?></td>
                        <td><?php echo htmlspecialchars($income['category_name']); ?></td>
                        <td><?php echo number_format($income['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($income['income_date']); ?></td>
                        <td><?php echo htmlspecialchars($income['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No income records found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

      <div class="alert alert-info">
        <strong>Total Income:</strong> <?php echo number_format($total_income, 2); ?>
    </div>
</div>
</body>
</html>
