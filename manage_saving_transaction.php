<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['transaction_id'])) {
    // Fetch the transaction for editing
    $transaction_id = intval($_GET['transaction_id']);
    $sql = "SELECT * FROM saving_transaction WHERE saving_transaction_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $transaction_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();

    if ($transaction) {
        echo json_encode(['status' => 'success', 'transaction' => $transaction]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id'])) {
    // Update the transaction
    $transaction_id = intval($_POST['transaction_id']);
    $amount = floatval($_POST['amount']);
    $transaction_date = $_POST['transaction_date'];
    $description = $_POST['description'];

    $sql = "UPDATE saving_transaction SET amount = ?, transaction_date = ?, description = ? WHERE saving_transaction_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dssii", $amount, $transaction_date, $description, $transaction_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update transaction']);
    }

    $stmt->close();
}

$conn->close();
?>
