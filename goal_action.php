<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý yêu cầu GET để lấy dữ liệu mục tiêu
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $goal_id = intval($_GET['id']);

    $sql = "SELECT goal_id, goal_name, target_amount, current_amount, target_date FROM financialgoals WHERE goal_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $goal_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Goal not found']);
    }

    $stmt->close();
}
// Xử lý yêu cầu POST để cập nhật dữ liệu mục tiêu
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goal_id'])) {
    $goal_id = intval($_POST['goal_id']);
    $goal_name = $_POST['goal_name'];
    $target_amount = floatval($_POST['target_amount']);
    $current_amount = floatval($_POST['current_amount']);
    $target_date = $_POST['target_date'];

    $sql = "UPDATE financialgoals SET goal_name = ?, target_amount = ?, current_amount = ?, target_date = ? WHERE goal_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddsii", $goal_name, $target_amount, $current_amount, $target_date, $goal_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Goal updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update goal']);
    }

    $stmt->close();
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

$conn->close();
?>
