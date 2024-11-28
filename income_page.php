<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý form thêm thu nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_income'])) {
    $income_category_id = $_POST['income_category_id'];
    $amount = $_POST['amount'];
    $income_date = $_POST['income_date'];
    $description = $_POST['description'];

    // Thêm bản ghi thu nhập vào bảng income
    $sql = "INSERT INTO income (user_id, income_category_id, amount, income_date, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $user_id, $income_category_id, $amount, $income_date, $description);

    if ($stmt->execute()) {
        $income_success = "Income added successfully!";
    } else {
        $income_error = "Error: " . $stmt->error;
    }

    $stmt->close();
        // Redirect để tránh việc gửi lại form khi refresh
        header("Location: income_page.php");
    exit();
}
// Lấy danh sách thu nhập của người dùng cùng với tên danh mục
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

// Lấy tổng thu nhập của người dùng
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
// Lấy danh sách các danh mục thu nhập
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
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
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

        .table th, .table td {
            vertical-align: middle;
        }

        .btn {
            border-radius: 5px;
        }

        .modal-header {
            background-color: #343a40;
            color: #ffffff;
        }

        .modal-footer .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
                <li class="nav-item">
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
                <li class="nav-item active">
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
    </nav><br>

<!-- Main Container -->
<div class="container mt-4">
    <div class="card">
        <div class="card-header"><i class="fas fa-coins"></i> Add Income</div>
        <div class="card-body">
            <?php if (isset($income_success)) echo "<div class='alert alert-success'>$income_success</div>"; ?>
            <?php if (isset($income_error)) echo "<div class='alert alert-danger'>$income_error</div>"; ?>

            <!-- Add Income Form -->
            <form action="income_page.php" method="post">
                <input type="hidden" name="add_income" value="1">
                <div class="form-group">
                    <label for="income_category_id"><i class="fas fa-tags"></i> Income Category:</label>
                    <select name="income_category_id" id="income_category_id" class="form-control" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['income_category_id']); ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount"><i class="fas fa-dollar-sign"></i> Amount:</label>
                    <input type="number" name="amount" id="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="income_date"><i class="fas fa-calendar-alt"></i> Income Date:</label>
                    <input type="date" name="income_date" id="income_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description"><i class="fas fa-pencil-alt"></i> Description:</label>
                    <input type="text" name="description" id="description" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Income</button>
            </form>
        </div>
    </div>

    <!-- Income Records Table -->
    <div class="card mt-4">
    <div class="card-header"><i class="fas fa-table"></i> Income Records</div>
    <div class="card-body">
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> Income ID</th>
                    <th><i class="fas fa-tags"></i> Category</th>
                    <th><i class="fas fa-dollar-sign"></i> Amount</th>
                    <th><i class="fas fa-calendar-alt"></i> Date</th>
                    <th><i class="fas fa-pencil-alt"></i> Description</th>
                    <th><i class="fas fa-cogs"></i> Action</th>
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
                            <td>
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                                <button class="btn btn-warning btn-sm btn-edit-income" 
                                        data-id="<?php echo htmlspecialchars($income['income_id']); ?>" 
                                        data-category-id="<?php echo htmlspecialchars($income['income_category_id'] ?? ''); ?>" 
                                        data-amount="<?php echo htmlspecialchars($income['amount']); ?>" 
                                        data-date="<?php echo htmlspecialchars($income['income_date']); ?>" 
                                        data-description="<?php echo htmlspecialchars($income['description']); ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No income records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<!-- Edit Income Modal -->
<div class="modal fade" id="editIncomeModal" tabindex="-1" role="dialog" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIncomeModalLabel"><i class="fas fa-edit"></i> Edit Income</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editIncomeForm" action="update_income.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="income_id" id="edit-income-id">
                    <div class="form-group">
                        <label for="edit-income-category-id"><i class="fas fa-tags"></i> Income Category:</label>
                        <select name="income_category_id" id="edit-income-category-id" class="form-control" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['income_category_id']); ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-amount"><i class="fas fa-dollar-sign"></i> Amount:</label>
                        <input type="number" name="amount" id="edit-amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-income-date"><i class="fas fa-calendar-alt"></i> Income Date:</label>
                        <input type="date" name="income_date" id="edit-income-date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-description"><i class="fas fa-pencil-alt"></i> Description:</label>
                        <input type="text" name="description" id="edit-description" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script>
        $(document).on('click', '.btn-edit-income', function() {
            const incomeId = $(this).data('id');
            const categoryId = $(this).data('category-id');
            const amount = $(this).data('amount');
            const date = $(this).data('date');
            const description = $(this).data('description');

            $('#edit-income-id').val(incomeId);
            $('#edit-income-category-id').val(categoryId);
            $('#edit-amount').val(amount);
            $('#edit-income-date').val(date);
            $('#edit-description').val(description);

            $('#editIncomeModal').modal('show');
        });
    </script>
</body>
</html>

