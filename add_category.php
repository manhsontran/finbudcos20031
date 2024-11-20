<?php
include 'db.php'; // Kết nối cơ sở dữ liệu

$category_name = $_POST['category_name'];

// Chuẩn bị câu truy vấn để thêm category
$sql = "INSERT INTO Categories (category_name) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category_name);

if ($stmt->execute()) {
    echo "Category added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
