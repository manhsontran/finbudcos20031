<?php
include 'db.php';
header('Content-Type: application/json');

$sql = "SELECT category_id, category_name FROM Categories";
$result = $conn->query($sql);

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode($categories);
$conn->close();
?>
