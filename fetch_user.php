<?php
// Kết nối đến database
include 'db.php'; // Đảm bảo rằng bạn có file db.php để kết nối cơ sở dữ liệu

// Thiết lập Content-Type thành JSON
header('Content-Type: application/json');

try {
    // Câu lệnh SQL để lấy danh sách người dùng
    $sql = "SELECT user_id, username, email FROM User";
    
    // Chuẩn bị truy vấn
    $stmt = $conn->prepare($sql);
    
    // Thực thi truy vấn
    $stmt->execute();
    
    // Lấy dữ liệu từ truy vấn
    $result = $stmt->get_result();
    
    // Kiểm tra xem có kết quả không
    if ($result->num_rows > 0) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        // Trả về dữ liệu dạng JSON
        echo json_encode([
            "status" => "success",
            "data" => $users
        ]);
    } else {
        // Nếu không có người dùng nào
        echo json_encode([
            "status" => "success",
            "data" => [],
            "message" => "No users found."
        ]);
    }
} catch (Exception $e) {
    // Nếu có lỗi xảy ra
    echo json_encode([
        "status" => "error",
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}

// Đóng kết nối
$conn->close();
?>
