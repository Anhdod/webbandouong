<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';
$rating_id = isset($_GET['rating_id']) ? intval($_GET['rating_id']) : 0;
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$from = isset($_GET['from']) ? $_GET['from'] : 'product';

if ($rating_id > 0 && $product_id > 0) {
    $check_query = "SELECT user_id FROM ratings WHERE id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $rating_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $rating = $result->fetch_assoc();
        $is_owner = $rating['user_id'] == $user_id;
        $is_admin = $role == 'admin';

        if ($is_owner || $is_admin) {
            $delete_query = "DELETE FROM ratings WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("i", $rating_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $_SESSION['message'] = "Đánh giá đã được xóa thành công!";
            } else {
                $_SESSION['error'] = "Lỗi hệ thống khi xóa đánh giá: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Bạn không có quyền xóa đánh giá này!";
        }
    } else {
        $_SESSION['error'] = "Đánh giá không tồn tại!";
    }
} else {
    $_SESSION['error'] = "Thông tin không hợp lệ!";
}

if ($from === 'admin') {
    header("Location: ../admin/admin_reviews.php");
} else {
    header("Location: product.php?id=$product_id");
}
exit();
?>