<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $expense_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Xóa expense dựa trên ID và user_id để đảm bảo bảo mật
    $delete_sql = "DELETE FROM expenses_transaction WHERE expense_transaction_id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $expense_id, $user_id);

    if ($stmt->execute()) {
        // Xóa thành công, chuyển hướng lại trang expensepage.php
        header("Location: expensepage.php?message=Expense+deleted+successfully");
    } else {
        // Thông báo lỗi nếu không xóa được
        header("Location: expensepage.php?error=Could+not+delete+expense");
    }
    $stmt->close();
} else {
    // Nếu không có ID, chuyển hướng lại trang expensepage.php với thông báo lỗi
    header("Location: expensepage.php?error=Invalid+expense+ID");
}

$conn->close();
?>
