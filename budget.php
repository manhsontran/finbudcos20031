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
    <title>Finbud - Budget</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 18px rgba(0, 0, 0, 0.1);
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
            padding: 12px;
        }
        .table-striped tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }
        .btn-primary, .btn-danger, .btn-warning {
            border-radius: 20px;
            padding: 6px 20px;
            font-size: 14px;
        }
        .form-control {
            border-radius: 5px;
        }
        .table-responsive {
            padding: 15px;
        }

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
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="expensepage.php"><i class="fas fa-wallet"></i> Expenses</a>
                </li>
                <li class="nav-item active">
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
    <!-- Add Budget Form Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus-circle"></i> Add Budget with Category
        </div>
        <div class="card-body">
            <form action="add_budget.php" method="post">
                <div class="form-group">
                    <label for="budget-amount"><i class="fas fa-dollar-sign"></i> Amount:</label>
                    <input type="number" name="amount" id="budget-amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="category-id"><i class="fas fa-list-alt"></i> Choose Category:</label>
                    <select name="category_id" id="category-id" class="form-control">
                        <!-- Categories will be loaded here via AJAX -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="new-category"><i class="fas fa-plus"></i> Or Add New Category:</label>
                    <input type="text" name="new_category" id="new-category" class="form-control" placeholder="Enter new category name">
                </div>
                <div class="form-group">
                    <label for="start-date"><i class="fas fa-calendar-alt"></i> Start Date:</label>
                    <input type="date" name="start_date" id="start-date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="end-date"><i class="fas fa-calendar-alt"></i> End Date:</label>
                    <input type="date" name="end_date" id="end-date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Add Budget</button>
            </form>
        </div>
    </div>

    <!-- Display Budgets Table Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Your Budgets with Categories
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="budget-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-dollar-sign"></i> Amount</th>
                        <th><i class="fas fa-list-alt"></i> Category</th>
                        <th><i class="fas fa-calendar-alt"></i> Start Date</th>
                        <th><i class="fas fa-calendar-alt"></i> End Date</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
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
                <h5 class="modal-title" id="editBudgetModalLabel"><i class="fas fa-edit"></i> Edit Budget</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-budget-form">
                    <input type="hidden" id="edit-budget-id" name="budget_id">
                    <div class="form-group">
                        <label for="edit-amount"><i class="fas fa-dollar-sign"></i> Amount:</label>
                        <input type="number" id="edit-amount" name="amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-category-id"><i class="fas fa-list-alt"></i> Choose Category:</label>
                        <select id="edit-category-id" name="category_id" class="form-control">
                            <!-- Categories will be loaded here via AJAX -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-start-date"><i class="fas fa-calendar-alt"></i> Start Date:</label>
                        <input type="date" id="edit-start-date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-end-date"><i class="fas fa-calendar-alt"></i> End Date:</label>
                        <input type="date" id="edit-end-date" name="end_date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
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

            $(document).on('click', '.delete-budget', function (e) {
                e.preventDefault();
                if (confirm("Are you sure you want to delete this budget?")) {
                    var budgetId = $(this).data('id');
                    
                    // Log budgetId để kiểm tra dữ liệu được truyền đúng
                    console.log("Budget ID to delete:", budgetId);

                    $.ajax({
                        url: 'delete_budget.php',
                        method: 'POST',
                        data: { id: budgetId },
                        success: function (response) {
                            // Log phản hồi từ server
                            console.log("Server response:", response);

                            try {
                                var res = JSON.parse(response); // Parse JSON
                                console.log("Parsed response:", res); // Log nội dung JSON sau khi parse

                                if (res.status === 'success') {
                                    alert(res.message);
                                    // Tải lại danh sách budgets
                                    $.ajax({
                                        url: 'fetch_budgets.php',
                                        method: 'GET',
                                        success: function (data) {
                                            console.log("Updated budget list:", data); // Log danh sách budget mới
                                            $('#budget-table tbody').html(data);
                                        }
                                    });
                                } else {
                                    alert(res.message);
                                }
                            } catch (err) {
                                console.error("Error parsing JSON response:", err, response); // Log lỗi parse JSON
                            }
                        },
                        error: function (xhr, status, error) {
                            // Log lỗi AJAX
                            console.error("AJAX Error:", status, error);
                            console.error("XHR Object:", xhr);
                        }
                    });
                }
            });

        });
    </script>
</body>
</html>


