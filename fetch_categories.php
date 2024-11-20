<?php
include 'db.php'; // Kết nối cơ sở dữ liệu

$sql = "SELECT category_id, category_name FROM Categories";
$result = $conn->query($sql);

$options = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $options .= "<option value='" . $row['category_id'] . "'>" . htmlspecialchars($row['category_name']) . "</option>";
    }
} else {
    $options = "<option value=''>No categories available</option>";
}

echo $options;

$conn->close();
?>
