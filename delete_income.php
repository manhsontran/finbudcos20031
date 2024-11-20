<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "error: not logged in";
    exit();
}

$user_id = $_SESSION['user_id'];
$income_id = isset($_POST['income_id']) ? intval($_POST['income_id']) : 0;

if ($income_id > 0) {
    // Xóa bản ghi thu nhập
    $sql = "DELETE FROM income WHERE income_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $income_id, $user_id);

    if ($stmt->execute()) {
        // Cập nhật tổng thu nhập
        $sql_update_total = "UPDATE total_income SET total_income = (SELECT SUM(amount) FROM income WHERE user_id = ?) WHERE user_id = ?";
        $stmt_update_total = $conn->prepare($sql_update_total);
        $stmt_update_total->bind_param("ii", $user_id, $user_id);
        $stmt_update_total->execute();
        $stmt_update_total->close();

        echo "success";
    } else {
        echo "error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "error: invalid income_id";
}

$conn->close();

?>
