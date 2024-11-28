<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = $_POST['amount'];
$category_id = $_POST['category_id'];
$new_category = $_POST['new_category'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];


if (!empty($new_category)) {

    $stmt = $conn->prepare("INSERT INTO Categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $new_category);
    if ($stmt->execute()) {
        $category_id = $stmt->insert_id;
    }
    $stmt->close();
}


$stmt = $conn->prepare("INSERT INTO Budgets (user_id, category_id, amount, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $user_id, $category_id, $amount, $start_date, $end_date);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Budget added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add budget: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit();
