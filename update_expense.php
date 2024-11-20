<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$expense_id = intval($_POST['expense_id']);
$amount = $_POST['amount'];
$expense_date = $_POST['expense_date'];
$category_id = $_POST['category_id'];
$sub_category_id = $_POST['sub_category_id'];
$description = $_POST['description'];

$sql = "UPDATE expenses_transaction SET amount = ?, expense_date = ?, category_id = ?, sub_category_id = ?, description = ? 
        WHERE expense_transaction_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dsissii", $amount, $expense_date, $category_id, $sub_category_id, $description, $expense_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
