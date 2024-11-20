<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

// Xử lý đăng nhập khi nhận được yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Lấy mật khẩu đã mã hóa từ cơ sở dữ liệu
    $sql = "SELECT user_id, password FROM User WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Liên kết các cột trong kết quả trả về
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        // So sánh mật khẩu nhập vào với mật khẩu đã mã hóa
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            echo json_encode(["status" => "success", "message" => "Welcome"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid username or password"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid username or password"]);
    }

    $stmt->close();
    $conn->close();
    exit(); // Kết thúc xử lý đăng nhập
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Vue.js -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12/dist/vue.js"></script>
</head>
<body>
    <div id="app" class="container d-flex align-items-center justify-content-center vh-100">
        <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
            <h3 class="text-center mb-4">Login</h3>
            <!-- Form đăng nhập -->
            <form @submit.prevent="submitForm">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" v-model="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" v-model="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span v-if="!loading">Login</span>
                    <span v-else>Logging in...</span>
                </button>
                <p class="mt-3">Don't have an account? <a href="signup.php">Sign up here</a>.</p>

                <p class="text-danger text-center mt-3" v-if="errorMessage">{{ errorMessage }}</p>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Vue.js logic -->
    <script>
        new Vue({
            el: '#app',
            data: {
                username: '',
                password: '',
                loading: false,
                errorMessage: ''
            },
            methods: {
                async submitForm() {
                    this.loading = true;
                    this.errorMessage = '';
                    
                    try {
                        const response = await fetch('', { // URL hiện tại (tức là chính file này)
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                username: this.username,
                                password: this.password
                            })
                        });
                        
                        const result = await response.text();

                        if (result.includes("Welcome")) {
                            window.location.href = 'dashboard.php';
                        } else {
                            this.errorMessage = result;
                        }
                    } catch (error) {
                        this.errorMessage = 'An error occurred. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        });
    </script>
</body>
</html>
