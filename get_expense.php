<?php
session_start();
include 'db.php';

header('Content-Type: application/json'); // Đặt tiêu đề JSON

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$expenseId = intval($_GET['id']);

$sql = "SELECT expense_transaction_id, amount, category_id, expense_date, description 
        FROM expenses_transaction 
        WHERE expense_transaction_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $expenseId, $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Expense not found']);
    }
} else {
    echo json_encode(['error' => 'Query failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
