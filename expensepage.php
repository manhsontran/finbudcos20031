<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch categories from the database
$sql = "SELECT category_id, category_name FROM Categories";
$result = $conn->query($sql);

// Fetch remaining budgets and corresponding category names for the user
$remaining_budget_sql = "SELECT rb.remaining_budget, c.category_name
                         FROM remaining_budget rb
                         JOIN Budgets b ON rb.budget_id = b.budget_id
                         JOIN Categories c ON b.category_id = c.category_id
                         WHERE b.user_id = ?";
$remaining_budget_stmt = $conn->prepare($remaining_budget_sql);
$remaining_budget_stmt->bind_param("i", $user_id);
$remaining_budget_stmt->execute();
$remaining_budget_result = $remaining_budget_stmt->get_result();

// Fetch expense transactions for the user with category and sub-category details
$expense_sql = "SELECT et.expense_transaction_id, et.amount, et.expense_date, et.description, c.category_name, sc.sub_category_name
                FROM `expenses_transaction` et
                JOIN Categories c ON et.category_id = c.category_id
                LEFT JOIN sub_category sc ON et.sub_category_id = sc.sub_category_id
                WHERE et.user_id = ?";
$expense_stmt = $conn->prepare($expense_sql);
$expense_stmt->bind_param("i", $user_id);
$expense_stmt->execute();
$expense_result = $expense_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinBud Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f8fa;
        }
        .container {
            margin-top: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            border-radius: 10px 10px 0 0;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 20px;
            padding: 5px 15px;
        }
        .btn-danger {
            border-radius: 20px;
        }
        .form-control {
            border-radius: 5px;
        }
        .table-responsive {
            padding: 15px;
        }
    </style>
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
                <li class="nav-item active">
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
        <!-- Remaining Budget Card -->
        <div class="card">
            <div class="card-header">
                Remaining Budget
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Category Name</th>
                            <th>Remaining Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($remaining_budget_result->num_rows > 0) {
                            while ($row = $remaining_budget_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['category_name']) . "</td>
                                        <td>" . number_format($row['remaining_budget'], 2) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2' class='text-center'>No remaining budget found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expense Transactions Card -->
        <div class="card">
            <div class="card-header">
                Expense Transactions
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Sub-Category</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($expense_result->num_rows > 0) {
                            while ($row = $expense_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . number_format($row['amount'], 2) . "</td>
                                        <td>" . htmlspecialchars($row['expense_date']) . "</td>
                                        <td>" . htmlspecialchars($row['category_name']) . "</td>
                                        <td>" . htmlspecialchars($row['sub_category_name'] ?? '') . "</td>
                                        <td>" . htmlspecialchars($row['description']) . "</td>
                                        <td>
                                            <button class='btn btn-warning btn-sm btn-edit-expense' data-id='" . $row['expense_transaction_id'] . "'>Edit</button>
                                            <a href='delete_expense.php?id=" . $row['expense_transaction_id'] . "' class='btn btn-danger btn-sm delete-expense'>Delete</a>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No expense transactions found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Edit Expense Modal -->
        <div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editExpenseForm">
                            <input type="hidden" id="edit-expense-id">
                            <div class="form-group">
                                <label for="edit-amount">Amount:</label>
                                <input type="number" step="0.01" id="edit-amount" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-expense-date">Expense Date:</label>
                                <input type="date" id="edit-expense-date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-category">Category:</label>
                                <select id="edit-category" class="form-control" required>
                                    <!-- Categories will be loaded here -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-sub-category">Sub-Category:</label>
                                <select id="edit-sub-category" class="form-control">
                                    <option value="">Select Sub-Category</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-description">Description:</label>
                                <input type="text" id="edit-description" class="form-control">
                            </div>
                            <button type="button" id="saveChanges" class="btn btn-primary">Save changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Expense Transaction Form Card -->
        <div class="card">
            <div class="card-header">
                Add Expense Transaction
            </div>
            <div class="card-body">
                <form action="process_expense.php" method="post">
                    <div class="form-group">
                        <label for="amount">Amount:</label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="expense_date">Expense Date:</label>
                        <input type="date" name="expense_date" id="expense_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['category_id'] . "'>" . htmlspecialchars($row['category_name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>No categories available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sub-category-id">Sub-Category:</label>
                        <select name="sub_category_id" id="sub-category-id" class="form-control">
                            <option value="">Select Sub-Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Expense</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load sub-categories based on selected category
            $('#category_id').change(function() {
                    const categoryId = $(this).val();
                    $.ajax({
                        url: 'fetch_sub_categories.php',
                        method: 'GET',
                        data: { category_id: categoryId },
                        dataType: 'json', // Thêm kiểu dữ liệu JSON
                        success: function(subCategories) {
                            let subCategoryOptions = '<option value="">Select Sub-Category</option>';
                            subCategories.forEach(subCategory => {
                                subCategoryOptions += `<option value="${subCategory.sub_category_id}">${subCategory.sub_category_name}</option>`;
                            });
                            $('#sub-category-id').html(subCategoryOptions);
                        },
                        error: function() {
                            alert("Failed to load sub-categories.");
                        }
                    });
                });

            // Confirm delete action
            $(document).on('click', '.delete-expense', function(e) {
                if (!confirm("Are you sure you want to delete this expense?")) {
                    e.preventDefault();
                }
            });
            // Mở modal chỉnh sửa và điền thông tin
            $('.btn-edit-expense').click(function() {
                const expenseId = $(this).data('id');

                // Lấy thông tin giao dịch để điền vào modal
                $.ajax({
                    url: 'get_expense.php',
                    method: 'GET',
                    data: { id: expenseId },
                    dataType: 'json',
                    success: function(data) {
                        $('#edit-expense-id').val(data.expense_transaction_id);
                        $('#edit-amount').val(data.amount);
                        $('#edit-expense-date').val(data.expense_date);
                        $('#edit-description').val(data.description);

                        // Tải danh sách Category và thiết lập Category đã chọn
                        $.ajax({
                            url: 'fetch_main_categories.php',
                            method: 'GET',
                            dataType: 'json',
                            success: function(categories) {
                                let categoryOptions = '';
                                categories.forEach(category => {
                                    categoryOptions += `<option value="${category.category_id}" ${category.category_id == data.category_id ? 'selected' : ''}>${category.category_name}</option>`;
                                });
                                $('#edit-category').html(categoryOptions);
                                
                                // Sau khi thiết lập Category, tải danh sách Sub-Category cho Category đã chọn
                                loadSubCategories(data.category_id, data.sub_category_id);
                            }
                        });

                        $('#editExpenseModal').modal('show');
                    },
                    error: function() {
                        alert("Failed to load expense data.");
                    }
                });
            });

            // Hàm tải Sub-Category dựa trên Category đã chọn
            $('#edit-category').change(function() {
                loadSubCategories($(this).val());
            });

            function loadSubCategories(categoryId, selectedSubCategoryId = null) {
                $.ajax({
                    url: 'fetch_sub_categories.php',
                    method: 'GET',
                    data: { category_id: categoryId },
                    dataType: 'json',
                    success: function(subCategories) {
                        let subCategoryOptions = '<option value="">Select Sub-Category</option>';
                        subCategories.forEach(subCategory => {
                            subCategoryOptions += `<option value="${subCategory.sub_category_id}" ${subCategory.sub_category_id == selectedSubCategoryId ? 'selected' : ''}>${subCategory.sub_category_name}</option>`;
                        });
                        $('#edit-sub-category').html(subCategoryOptions);
                    }
                });
            }

            // Gửi yêu cầu cập nhật khi nhấn "Save changes"
            $('#saveChanges').click(function() {
                const expenseId = $('#edit-expense-id').val();
                const amount = $('#edit-amount').val();
                const expenseDate = $('#edit-expense-date').val();
                const categoryId = $('#edit-category').val();
                const subCategoryId = $('#edit-sub-category').val();
                const description = $('#edit-description').val();

                $.ajax({
                    url: 'update_expense.php',
                    method: 'POST',
                    data: {
                        expense_id: expenseId,
                        amount: amount,
                        expense_date: expenseDate,
                        category_id: categoryId,
                        sub_category_id: subCategoryId,
                        description: description
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert("Expense updated successfully!");
                            location.reload();
                        } else {
                            alert("Failed to update expense: " + response.message);
                        }
                    },
                    error: function() {
                        alert("An error occurred while updating the expense.");
                    }
                });
            });
        });
    </script>
</body>
</html>


<?php
// Close database connections
$remaining_budget_stmt->close();
$expense_stmt->close();
$conn->close();
?>
