<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Log dữ liệu nhận từ client
    error_log("Received POST data: " . json_encode($_POST));

    $budget_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    // Log kiểm tra budget_id và user_id
    error_log("Budget ID: $budget_id, User ID: $user_id");

    if (!$budget_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid budget ID']);
        error_log("Invalid budget ID");
        exit();
    }

    $delete_sql = "DELETE FROM budgets WHERE budget_id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'SQL error']);
        error_log("SQL error during statement preparation: " . $conn->error);
        exit();
    }

    $stmt->bind_param("ii", $budget_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Budget deleted successfully']);
        error_log("Budget deleted successfully: Budget ID $budget_id");
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not delete budget']);
        error_log("Failed to delete budget: " . $stmt->error);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    error_log("Invalid request method or missing ID");
}

$conn->close();
?>
