<?php
include 'db.php';

// Đảm bảo phản hồi trả về JSON
header('Content-Type: application/json');

// Khởi tạo mảng chứa danh mục
$categories = [];

// Thực hiện truy vấn
$sql = "SELECT category_id, category_name FROM Categories";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row; // Thêm từng danh mục vào mảng
        }
    }
} else {
    // Trả về lỗi nếu truy vấn thất bại
    echo json_encode(['error' => 'Database query failed']);
    $conn->close();
    exit();
}

// Trả về JSON danh sách danh mục
echo json_encode($categories);
$conn->close();
?>
