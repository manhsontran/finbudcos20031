<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget</title>
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
        .btn-primary, .btn-danger, .btn-warning {
            border-radius: 20px;
            padding: 5px 15px;
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
                <li class="nav-item">
                    <a class="nav-link" href="expensepage.php">Expenses</a>
                </li>
                <li class="nav-item active">
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
        <!-- Add Budget Form Card -->
        <div class="card">
            <div class="card-header">
                Add Budget with Category
            </div>
            <div class="card-body">
                <form action="add_budget.php" method="post">
                    <div class="form-group">
                        <label for="budget-amount">Amount:</label>
                        <input type="number" name="amount" id="budget-amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="category-id">Choose Category:</label>
                        <select name="category_id" id="category-id" class="form-control">
                            <!-- Categories sẽ được tải vào đây bằng AJAX -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new-category">Or Add New Category:</label>
                        <input type="text" name="new_category" id="new-category" class="form-control" placeholder="Enter new category name">
                    </div>
                    <div class="form-group">
                        <label for="start-date">Start Date:</label>
                        <input type="date" name="start_date" id="start-date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="end-date">End Date:</label>
                        <input type="date" name="end_date" id="end-date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Budget</button>
                </form>
            </div>
        </div>

        <!-- Display Budgets Table Card -->
        <div class="card">
            <div class="card-header">
                Your Budgets with Categories
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="budget-table">
                    <thead class="thead-dark">
                        <tr>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Budget Modal -->
    <div class="modal fade" id="editBudgetModal" tabindex="-1" role="dialog" aria-labelledby="editBudgetModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBudgetModalLabel">Edit Budget</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit-budget-form">
                        <input type="hidden" id="edit-budget-id" name="budget_id">
                        <div class="form-group">
                            <label for="edit-amount">Amount:</label>
                            <input type="number" id="edit-amount" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-category-id">Choose Category:</label>
                            <select id="edit-category-id" name="category_id" class="form-control">
                                <!-- Categories sẽ được tải vào đây bằng AJAX -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-start-date">Start Date:</label>
                            <input type="date" id="edit-start-date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-end-date">End Date:</label>
                            <input type="date" id="edit-end-date" name="end_date" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- AJAX to load Budgets and Categories, with Edit and Delete functionality -->
    <script>
        $(document).ready(function() {
            // Load Budgets
            $.ajax({
                url: 'fetch_budgets.php',
                method: 'GET',
                success: function(data) {
                    $('#budget-table tbody').html(data);
                }
            });
            
            // Load Categories for Add and Edit forms
            $.ajax({
                url: 'fetch_categories.php',
                method: 'GET',
                success: function(data) {
                    $('#category-id, #edit-category-id').html(data);
                }
            });

            // Open Edit Modal with Budget Data
            $(document).on('click', '.edit-budget', function() {
                var budgetId = $(this).data('id');
                $.ajax({
                    url: 'get_budget.php',
                    method: 'GET',
                    data: { id: budgetId },
                    success: function(data) {
                        var budget = JSON.parse(data);
                        $('#edit-budget-id').val(budget.budget_id);
                        $('#edit-amount').val(budget.amount);
                        $('#edit-category-id').val(budget.category_id);
                        $('#edit-start-date').val(budget.start_date);
                        $('#edit-end-date').val(budget.end_date);
                        $('#editBudgetModal').modal('show');
                    }
                });
            });

            // Handle Edit Budget Form Submission
            $('#edit-budget-form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'update_budget.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response === "success") {
                            alert("Budget updated successfully!");
                            $('#editBudgetModal').modal('hide');
                            $.ajax({
                                url: 'fetch_budgets.php',
                                method: 'GET',
                                success: function(data) {
                                    $('#budget-table tbody').html(data);
                                }
                            });
                        } else {
                            alert("Failed to update budget. Please try again.");
                        }
                    }
                });
            });

            // Confirm delete action
            $(document).on('click', '.delete-budget', function(e) {
                e.preventDefault();
                if (confirm("Are you sure you want to delete this budget?")) {
                    var budgetId = $(this).data('id');
                    $.ajax({
                        url: 'delete_budget.php',
                        method: 'POST',
                        data: { id: budgetId },
                        success: function(response) {
                            if (response == "success") {
                                alert("Budget deleted successfully!");
                                $.ajax({
                                    url: 'fetch_budgets.php',
                                    method: 'GET',
                                    success: function(data) {
                                        $('#budget-table tbody').html(data);
                                    }
                                });
                            } else {
                                alert("Failed to delete budget. Please try again.");
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
