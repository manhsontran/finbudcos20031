<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "error";
    exit();
}

$user_id = $_SESSION['user_id'];
$saving_transaction_id = isset($_POST['saving_transaction_id']) ? intval($_POST['saving_transaction_id']) : 0;

// Kiểm tra xem saving_transaction_id có được truyền vào không
if ($saving_transaction_id > 0) {
    $sql = "DELETE FROM saving_transaction WHERE saving_transaction_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $saving_transaction_id, $user_id);

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
