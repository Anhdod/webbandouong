<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red;'>Vui lòng <a href='../config/login.php'>đăng nhập</a> để bình luận.</p>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $comment = trim($_POST['comment']);

    if ($product_id > 0 && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, product_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $product_id, $comment);

        if ($stmt->execute()) {
            header("Location: product.php?id=$product_id&message=Bình luận đã được gửi thành công!");
            exit();
        } else {
            $_SESSION['error'] = "Lỗi khi gửi bình luận: " . $conn->error;
            header("Location: product.php?id=$product_id");
            exit();
        }
    } else {
        $_SESSION['error'] = "Dữ liệu không hợp lệ!";
        header("Location: product.php?id=$product_id");
        exit();
    }
}
?>