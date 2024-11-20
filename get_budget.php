<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];
$budgetId = intval($_GET['id']);

$sql = "SELECT b.budget_id, b.amount, b.category_id, b.start_date, b.end_date 
        FROM Budgets b
        WHERE b.budget_id = ? AND b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $budgetId, $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode([]);
    }
}

$stmt->close();
$conn->close();
?>
