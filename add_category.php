<?php
include 'db.php'; 

$category_name = $_POST['category_name'];


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
