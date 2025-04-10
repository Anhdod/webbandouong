<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red;'>Vui lòng <a href='../config/login.php'>đăng nhập</a> để yêu thích.</p>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($product_id > 0) {
        $check_stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND product_id = ?");
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $product_id);
            if ($stmt->execute()) {
                header("Location: product.php?id=$product_id&message=favorite_added");
                exit();
            } else {
                echo "<p style='color:red;'>Lỗi khi thêm vào yêu thích! <a href='product.php?id=$product_id'>Quay lại</a></p>";
            }
        } else {
            echo "<p style='color:orange;'>Sản phẩm này đã nằm trong danh sách yêu thích! <a href='product.php?id=$product_id'>Quay lại</a></p>";
        }
    } else {
        echo "<p style='color:red;'>Dữ liệu không hợp lệ! <a href='product.php'>Quay lại</a></p>";
    }
}
?>