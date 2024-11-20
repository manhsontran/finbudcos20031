<?php
include 'db.php';
header('Content-Type: application/json');

$category_id = intval($_GET['category_id']);
$sql = "SELECT sub_category_id, sub_category_name FROM sub_category WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$sub_categories = [];
while ($row = $result->fetch_assoc()) {
    $sub_categories[] = $row;
}

echo json_encode($sub_categories);
$stmt->close();
$conn->close();
?>
