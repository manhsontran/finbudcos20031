<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<tr><td colspan="5" class="text-center">Please log in to view budgets.</td></tr>';
    exit();
}

$user_id = $_SESSION['user_id'];

// Truy vấn ngân sách của người dùng
$sql = "SELECT b.budget_id, b.amount, c.category_name, b.start_date, b.end_date 
        FROM Budgets b
        JOIN Categories c ON b.category_id = c.category_id
        WHERE b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $output = '';
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>
                            <td>' . number_format($row['amount'], 2) . '</td>
                            <td>' . htmlspecialchars($row['category_name']) . '</td>
                            <td>' . htmlspecialchars($row['start_date']) . '</td>
                            <td>' . htmlspecialchars($row['end_date']) . '</td>
                            <td>
                                <button data-id="' . $row['budget_id'] . '" class="btn btn-warning btn-sm edit-budget">Edit</button>
                                <button data-id="' . $row['budget_id'] . '" class="btn btn-danger btn-sm delete-budget">Delete</button>
                            </td>
                        </tr>';
        }
    } else {
        $output .= '<tr><td colspan="5" class="text-center">No budgets found</td></tr>';
    }
    
    echo $output;
} else {
    echo '<tr><td colspan="5" class="text-center">Error fetching budgets</td></tr>';
}

$stmt->close();
$conn->close();
?>
