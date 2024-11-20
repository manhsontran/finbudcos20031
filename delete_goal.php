<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "error";
    exit();
}

$user_id = $_SESSION['user_id'];
$goal_id = isset($_POST['goal_id']) ? intval($_POST['goal_id']) : 0;

// Kiểm tra xem goal_id có được truyền vào không
if ($goal_id > 0) {
    $sql = "DELETE FROM financialgoals WHERE goal_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $goal_id, $user_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
} else {
    echo "error";
}

$conn->close();
?>
