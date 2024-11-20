<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['budget_id'])) {
    echo "error";
    exit();
}

$user_id = $_SESSION['user_id'];
$budgetId = intval($_POST['budget_id']);
$amount = floatval($_POST['amount']);
$category_id = intval($_POST['category_id']);
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

$sql = "UPDATE Budgets SET amount = ?, category_id = ?, start_date = ?, end_date = ? 
        WHERE budget_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dissii", $amount, $category_id, $start_date, $end_date, $budgetId, $user_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
