<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red;'>Vui lòng <a href='../config/login.php'>đăng nhập</a> để đánh giá.</p>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

    if ($product_id > 0 && $rating >= 1 && $rating <= 5) {
        $check_query = "SELECT * FROM ratings WHERE user_id = ? AND product_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $update_query = "UPDATE ratings SET rating = ?, created_at = NOW() WHERE user_id = ? AND product_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("iii", $rating, $user_id, $product_id);
            $update_stmt->execute();
        } else {
            $insert_query = "INSERT INTO ratings (user_id, product_id, rating) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iii", $user_id, $product_id, $rating);
            $insert_stmt->execute();
        }

        header("Location: product.php?id=$product_id&message=rating_added");
        exit();
    } else {
        echo "<p style='color:red;'>Dữ liệu không hợp lệ! <a href='product.php?id=$product_id'>Quay lại</a></p>";
    }
}
?>