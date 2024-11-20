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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery (phải được tải trước Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Popper.js (cần cho Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


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
                <li class="nav-item ">
                    <a class="nav-link" href="budget.php">Budgets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="finance_goalpage.php">Goals</a>
                </li>
                <li class="nav-item active">
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
<div class="container mt-5">
    <h2>Add Income</h2>

    <!-- Thông báo thành công hoặc lỗi khi thêm thu nhập -->
    <?php if (isset($income_success)) echo "<div class='alert alert-success'>$income_success</div>"; ?>
    <?php if (isset($income_error)) echo "<div class='alert alert-danger'>$income_error</div>"; ?>

    <!-- Form thêm thu nhập -->
    <form action="income_page.php" method="post">
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
    <!-- Bảng hiển thị danh sách thu nhập và danh mục -->
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Income ID</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Description</th>
                <th>Action</th>
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
                    <button class="btn btn-danger btn-sm btn-delete-income" data-id="<?php echo $income['income_id']; ?>">Delete</button>
                    <button class="btn btn-warning btn-sm btn-edit-income" 
                            data-id="<?php echo htmlspecialchars($income['income_id']); ?>" 
                            data-category-id="<?php echo htmlspecialchars($income['income_category_id'] ?? ''); ?>" 
                            data-amount="<?php echo htmlspecialchars($income['amount']); ?>" 
                            data-date="<?php echo htmlspecialchars($income['income_date']); ?>" 
                            data-description="<?php echo htmlspecialchars($income['description']); ?>">
                        Edit
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
      <!-- Hiển thị tổng thu nhập -->
      <div class="alert alert-info">
        <strong>Total Income:</strong> <?php echo number_format($total_income, 2); ?>
    </div>
    <!-- Edit Income Modal -->
    <div class="modal fade" id="editIncomeModal" tabindex="-1" role="dialog" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editIncomeModalLabel">Edit Income</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editIncomeForm" action="update_income.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="income_id" id="edit-income-id">
                        <div class="form-group">
                            <label for="edit-income-category-id">Income Category:</label>
                            <select name="income_category_id" id="edit-income-category-id" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['income_category_id']); ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-amount">Amount:</label>
                            <input type="number" name="amount" id="edit-amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-income-date">Income Date:</label>
                            <input type="date" name="income_date" id="edit-income-date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-description">Description:</label>
                            <input type="text" name="description" id="edit-description" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<script>
    $(document).ready(function() {
        // Sự kiện click cho nút "Delete" trong Income Table
        $('.btn-delete-income').click(function() {
            if (confirm("Are you sure you want to delete this income record?")) {
                const button = $(this);
                const incomeId = button.data('id');

                $.ajax({
                    url: 'delete_income.php',
                    type: 'POST',
                    data: { income_id: incomeId },
                    success: function(response) {
                        console.log(response); // Kiểm tra phản hồi từ server
                        if (response.trim() === 'success') {
                            alert("Income record deleted successfully!");
                            location.reload(); // Tự động tải lại trang
                        } else {
                            alert("Failed to delete income record.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", error); // Hiển thị lỗi nếu xảy ra
                        alert("An error occurred while trying to delete the income record.");
                    }
                });
            }
        });
        // Open Edit Income Modal and populate data
        $('.btn-edit-income').click(function() {
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
    });
</script>



</body>
</html>
