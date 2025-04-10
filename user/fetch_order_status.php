<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT id, status FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders);
?>
