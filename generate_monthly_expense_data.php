<?php
include 'db.php'; // Kết nối database

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Truy vấn dữ liệu tổng chi phí theo tháng
$sql = "SELECT DATE_FORMAT(expense_date, '%Y-%m') AS month, SUM(amount) AS total_expense
        FROM expenses_transaction
        WHERE user_id = ?
        GROUP BY month
        ORDER BY month";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$months = [];
$expenses = [];
while ($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
    $expenses[] = (float)$row['total_expense'];
}

$stmt->close();
$conn->close();

echo json_encode(['months' => $months, 'expenses' => $expenses]);
