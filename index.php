<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f4faff; 
            color: #333; 
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding-bottom: 100px; 
        }

        /* Navigation Bar */
        .navbar {
            background-color: #ffffff; 
            border-bottom: 1px solid #e0e0e0; 
            
        }

        .navbar-nav .nav-link {
            color: #007bff; 
            transition: color 0.3s;
        }

        .navbar-nav .nav-link:hover {
            color: #0056b3; 
        }

        .navbar-brand {
            margin-left: 40px;
        }

        #nav-link {
            margin-right: 40px;
        }

        .navbar-center {
            display: flex;
            justify-content: center; 
            width: 85%; 
        }

        .navbar-center .nav-item {
            margin: 0 15px; 
        }

        /* Main Card */
        .main-card {
            background-color: #ffffff; 
            color: #333;
            border-radius: 10px;
            padding: 20px;
            margin-top: 50px;
            box-shadow: 0 10px 20px rgba(0, 123, 255, 0.1); 
            transition: transform 0.3s;
        }

        .main-card:hover {
            transform: scale(1.02);
        }

        /* Feature Cards Layout */
        .feature-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        .feature-container .card {
            background-color: #f8f9fa; 
            color: #007bff;
            border: none;
            box-shadow: 0 8px 15px rgba(0, 123, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            flex: 1 1 30%;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-container .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 123, 255, 0.15);
        }

        .feature-container .card h5 {
            font-weight: bold;
            color: #0056b3;
        }

        .card .card-text, .card-title {
            text-align: center;
        }


        /* Button Styling */
        .btn {
            background-color: #007bff;
            border: none;
            color: white;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        /* Responsive Design for Small Screens */
        @media (max-width: 768px) {
            .feature-container .card {
                flex: 1 1 100%;
            }
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
            <ul class="navbar-nav navbar-center">
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
                    <a class="nav-link" href="filter.html"><i class="fas fa-file-alt"></i> Filter</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a id="nav-link" class="nav-link text-danger" href="signup.php"><i class="fas fa-sign-out-alt"></i> Signup</a>
                </li>
                <li class="nav-item">
                    <a id="nav-link" class="nav-link text-primary" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Main Card -->
    <div class="container main-card">
        <div class="row">
            <div class="col-md-8">
                <p class="project-code">Project Code: i05</p>
                <h1>Personal Finance Tracker (FinBud)</h1>
                <p class="text-primary">FinBud is a comprehensive personal finance tracker designed to help users manage their finances effortlessly.</p>
            </div>
            <div class="col-md-4">
                <div class="card-image">
                    <img src="image1.png" alt="Savings Tracker Image" style="width:100%; border-radius:10px;">
                </div>
            </div>
        </div>
    </div><br><br>
    
    <!-- Feature Containers -->
    <div class="container feature-container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-wallet"></i> Expense Tracking</h5>
                <p class="card-text">Easily log every transaction, categorize your spending, and keep track of where your money is going. This helps you identify unnecessary expenses and make adjustments to stay within your budget.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chart-line"></i> Budget Creation</h5>
                <p class="card-text">Create personalized budgets for various expense categories. FinBud allows you to set monthly or weekly limits, helping you prevent overspending and encouraging smart saving habits.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-bullseye"></i> Financial Goal Setting</h5>
                <p class="card-text">Define and monitor your financial goals, whether saving for a vacation, a new gadget, or an emergency fund. With FinBud, track progress over time and stay motivated to reach your targets.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chart-pie"></i> Spending Pattern Visualization</h5>
                <p class="card-text">Gain insights into your spending behavior through visual charts and graphs. FinBud helps you understand patterns, so you can adjust your habits and make better financial decisions.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-money-bill-wave"></i> Income Tracking</h5>
                <p class="card-text">Record all your income sources, including salary, side jobs, or passive income, to have a clear view of your cash flow and make adjustments where necessary.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-file-alt"></i> Detailed Financial Reports</h5>
                <p class="card-text">Generate monthly or quarterly reports to review your overall financial health. These reports make it easier to identify areas for improvement and to celebrate your achievements.</p>
            </div>
        </div>
    </div><br><br>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

