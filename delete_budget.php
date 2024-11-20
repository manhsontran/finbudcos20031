<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $budget_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Xóa budget dựa trên budget_id và user_id để đảm bảo bảo mật
    $delete_sql = "DELETE FROM Budgets WHERE budget_id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $budget_id, $user_id);

    if ($stmt->execute()) {
        // Xóa thành công, chuyển hướng lại trang budget.php với thông báo thành công
        header("Location: budget.php?message=Budget+deleted+successfully");
    } else {
        // Thông báo lỗi nếu không xóa được
        header("Location: budget.php?error=Could+not+delete+budget");
    }
    $stmt->close();
} else {
    // Nếu không có ID, chuyển hướng lại trang budget.php với thông báo lỗi
    header("Location: budget.php?error=Invalid+budget+ID");
}

$conn->close();
?>
