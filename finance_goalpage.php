<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Process Financial Goals form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_goal'])) {
    $goal_name = $_POST['goal_name'];
    $target_amount = $_POST['target_amount'];
    $target_date = $_POST['target_date'];

    $sql = "INSERT INTO financialgoals (user_id, goal_name, target_amount, current_amount, target_date) VALUES (?, ?, ?, 0, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isds", $user_id, $goal_name, $target_amount, $target_date);

    if ($stmt->execute()) {
        $goal_success = "Financial goal added successfully!";
        // Redirect to avoid duplicate submissions on refresh
        header("Location: finance_goalpage.php");
        exit();
    } else {
        $goal_error = "Error: " . $stmt->error;
    }

    $stmt->close();
}


// Process saving transaction form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_saving'])) {
    $goal_id = $_POST['goal_id'];
    $amount = $_POST['amount'];
    $transaction_date = $_POST['transaction_date'];
    $description = $_POST['description'];

    // Insert saving transaction
    $sql = "INSERT INTO saving_transaction (user_id, goal_id, amount, transaction_date, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidss", $user_id, $goal_id, $amount, $transaction_date, $description);

    if ($stmt->execute()) {
        // Update current_amount in FinancialGoals
        $update_goal_sql = "UPDATE financialgoals SET current_amount = current_amount + ? WHERE goal_id = ?";
        $update_stmt = $conn->prepare($update_goal_sql);
        $update_stmt->bind_param("di", $amount, $goal_id);
        $update_stmt->execute();
        $update_stmt->close();

        $saving_success = "Saving transaction added successfully!";
        // Redirect to avoid duplicate submissions on refresh
        header("Location: finance_goalpage.php");
        exit();
    } else {
        $saving_error = "Error: " . $stmt->error;
    }

    $stmt->close();
}


// Lấy danh sách FinancialGoals cho user_id để chọn trong saving Transaction và hiển thị bảng
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
// Lấy danh sách Saving Transactions của người dùng hiện tại
$saving_transactions = [];
$sql = "SELECT st.saving_transaction_id, fg.goal_name, st.amount, st.transaction_date, st.description 
        FROM saving_transaction AS st 
        JOIN financialgoals AS fg ON st.goal_id = fg.goal_id 
        WHERE st.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $saving_transactions[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Goal</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 50px;
            max-width: 900px;
        }
        .section-title {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            border-radius: 5px 5px 0 0;
        }
        .form-section, .table-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 0 0 5px 5px;
            border: 1px solid #ddd;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-delete {
            background-color: #dc3545;
            border: none;
            color: #fff;
            border-radius: 5px;
            padding: 5px 10px;
        }
        .table thead {
            background-color: #343a40;
            color: #fff;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .alert {
            margin-top: 20px;
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
                <li class="nav-item">
                    <a class="nav-link" href="expensepage.php">Expenses</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" href="budget.php">Budgets</a>
                </li>
                <li class="nav-item active">
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
        <!-- Form thêm Financial Goal -->
        <div class="form-section">
            <div class="section-title">Add Financial Goal</div>
            <?php if (isset($goal_success)) echo "<div class='alert alert-success'>$goal_success</div>"; ?>
            <?php if (isset($goal_error)) echo "<div class='alert alert-danger'>$goal_error</div>"; ?>
            <form action="finance_goalpage.php" method="post">
                <input type="hidden" name="add_goal" value="1">
                <div class="form-group">
                    <label for="goal_name">Goal Name:</label>
                    <input type="text" name="goal_name" id="goal_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="target_amount">Target Amount:</label>
                    <input type="number" name="target_amount" id="target_amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="target_date">Target Date:</label>
                    <input type="date" name="target_date" id="target_date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Goal</button>
            </form>
        </div>

        <!-- Hiển thị bảng Financial Goals -->
        <div class="table-section">
            <div class="section-title">Your Financial Goals</div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Goal Name</th>
                        <th>Target Amount</th>
                        <th>Current Amount</th>
                        <th>Target Date</th>
                        <th>Actions</th>
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
                            <td>
                                <button class="btn btn-warning btn-sm btn-edit-goal" data-id="<?php echo $goal['goal_id']; ?>">Edit</button>
                                <button class="btn btn-danger btn-sm btn-delete" data-id="<?php echo $goal['goal_id']; ?>">Delete</button>
                            </td>
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
        
                <!-- Edit Goal Modal -->
        <div class="modal fade" id="editGoalModal" tabindex="-1" aria-labelledby="editGoalModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editGoalModalLabel">Edit Financial Goal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editGoalForm">
                            <input type="hidden" id="edit-goal-id">
                            <div class="form-group">
                                <label for="edit-goal-name">Goal Name:</label>
                                <input type="text" id="edit-goal-name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-target-amount">Target Amount:</label>
                                <input type="number" id="edit-target-amount" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-current-amount">Current Amount:</label>
                                <input type="number" id="edit-current-amount" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-target-date">Target Date:</label>
                                <input type="date" id="edit-target-date" class="form-control" required>
                            </div>
                            <button type="button" id="saveGoalChanges" class="btn btn-primary">Save changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form thêm saving Transaction -->
        <div class="form-section">
            <div class="section-title">Add Saving Transaction</div>
            <?php if (isset($saving_success)) echo "<div class='alert alert-success'>$saving_success</div>"; ?>
            <?php if (isset($saving_error)) echo "<div class='alert alert-danger'>$saving_error</div>"; ?>
            <form action="finance_goalpage.php" method="post">
                <input type="hidden" name="add_saving" value="1">
                <div class="form-group">
                    <label for="goal_id">Select Goal:</label>
                    <select name="goal_id" id="goal_id" class="form-control" required>
                        <?php 
                        foreach ($goals as $goal) {
                            echo '<option value="' . htmlspecialchars($goal['goal_id']) . '">' . htmlspecialchars($goal['goal_name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" id="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="transaction_date">Transaction Date:</label>
                    <input type="date" name="transaction_date" id="transaction_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <input type="text" name="description" id="description" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Add Saving Transaction</button>
            </form>
        </div>

        <!-- Hiển thị bảng Saving Transactions -->
        <div class="table-section">
            <div class="section-title">Your Saving Transactions</div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Goal Name</th>
                        <th>Amount</th>
                        <th>Transaction Date</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($saving_transactions) > 0): ?>
                    <?php foreach ($saving_transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['saving_transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['goal_name']); ?></td>
                            <td><?php echo number_format($transaction['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm btn-edit-transaction" data-id="<?php echo $transaction['saving_transaction_id']; ?>">Edit</button>
                                <button class="btn btn-danger btn-sm btn-delete-transaction" data-id="<?php echo $transaction['saving_transaction_id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No saving transactions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTransactionModalLabel">Edit Saving Transaction</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editTransactionForm">
                        <input type="hidden" id="edit-transaction-id">
                        <div class="form-group">
                            <label for="edit-amount">Amount:</label>
                            <input type="number" step="0.01" id="edit-amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-transaction-date">Transaction Date:</label>
                            <input type="date" id="edit-transaction-date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-description">Description:</label>
                            <input type="text" id="edit-description" class="form-control">
                        </div>
                        <button type="button" id="saveTransactionChanges" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.btn-delete').click(function() {
                if (confirm("Are you sure you want to delete this goal?")) {
                    const button = $(this);
                    const goalId = button.data('id');

                    $.ajax({
                        url: 'delete_goal.php',
                        type: 'POST',
                        data: { goal_id: goalId },
                        success: function(response) {
                            if (response === 'success') {
                                alert("Goal deleted successfully!");
                                button.closest('tr').remove();
                            } else {
                                alert("Failed to delete goal.");
                            }
                        },
                        error: function() {
                            alert("An error occurred while trying to delete the goal.");
                        }
                    });
                }
            });
        });
        $(document).ready(function() {
        // Sự kiện click cho nút "Delete" trong Saving Transactions
        $('.btn-delete-transaction').click(function() {
            if (confirm("Are you sure you want to delete this transaction?")) {
                const button = $(this);
                const transactionId = button.data('id');

                $.ajax({
                    url: 'delete_saving_transaction.php', // Tạo tệp này ở bước tiếp theo
                    type: 'POST',
                    data: { saving_transaction_id: transactionId },
                    success: function(response) {
                        if (response === 'success') {
                            alert("Transaction deleted successfully!");
                            button.closest('tr').remove();
                        } else {
                            alert("Failed to delete transaction.");
                        }
                    },
                    error: function() {
                        alert("An error occurred while trying to delete the transaction.");
                    }
                });
            }
        });
                // Lấy dữ liệu mục tiêu khi nhấn Edit
        $('.btn-edit-goal').click(function() {
            const goalId = $(this).data('id');

            $.ajax({
                url: 'goal_action.php',
                method: 'GET',
                data: { id: goalId },
                dataType: 'json',
                success: function(data) {
                    if (data.status !== 'error') {
                        $('#edit-goal-id').val(data.goal_id);
                        $('#edit-goal-name').val(data.goal_name);
                        $('#edit-target-amount').val(data.target_amount);
                        $('#edit-current-amount').val(data.current_amount);
                        $('#edit-target-date').val(data.target_date);
                        $('#editGoalModal').modal('show');
                    } else {
                        alert(data.message);
                    }
                },
                error: function() {
                    alert("Failed to load goal data.");
                }
            });
        });

        // Cập nhật mục tiêu khi nhấn Save changes
        $('#saveGoalChanges').click(function() {
            const goalId = $('#edit-goal-id').val();
            const goalName = $('#edit-goal-name').val();
            const targetAmount = $('#edit-target-amount').val();
            const currentAmount = $('#edit-current-amount').val();
            const targetDate = $('#edit-target-date').val();

            $.ajax({
                url: 'goal_action.php',
                method: 'POST',
                data: {
                    goal_id: goalId,
                    goal_name: goalName,
                    target_amount: targetAmount,
                    current_amount: currentAmount,
                    target_date: targetDate
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while updating the goal.");
                }
            });
        });
         // Open modal to edit transaction
         $('.btn-edit-transaction').click(function() {
            const transactionId = $(this).data('id');

            $.ajax({
                url: 'manage_saving_transaction.php',
                method: 'GET',
                data: { transaction_id: transactionId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#edit-transaction-id').val(response.transaction.saving_transaction_id);
                        $('#edit-amount').val(response.transaction.amount);
                        $('#edit-transaction-date').val(response.transaction.transaction_date);
                        $('#edit-description').val(response.transaction.description);
                        $('#editTransactionModal').modal('show');
                    } else {
                        alert(response.message || 'Failed to load transaction data');
                    }
                },
                error: function() {
                    alert("An error occurred while trying to load transaction data.");
                }
            });
        });

        // Save changes to the transaction
        $('#saveTransactionChanges').click(function() {
            const transactionId = $('#edit-transaction-id').val();
            const amount = $('#edit-amount').val();
            const transactionDate = $('#edit-transaction-date').val();
            const description = $('#edit-description').val();

            $.ajax({
                url: 'manage_saving_transaction.php',
                method: 'POST',
                data: {
                    transaction_id: transactionId,
                    amount: amount,
                    transaction_date: transactionDate,
                    description: description
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert("Transaction updated successfully!");
                        location.reload();
                    } else {
                        alert(response.message || "Failed to update transaction.");
                    }
                },
                error: function() {
                    alert("An error occurred while updating the transaction.");
                }
            });
        });
    });

</script>

</body>
</html>

