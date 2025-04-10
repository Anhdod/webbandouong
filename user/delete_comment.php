<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';
$comment_id = isset($_GET['comment_id']) ? intval($_GET['comment_id']) : 0;
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$from = isset($_GET['from']) ? $_GET['from'] : 'product'; // Mặc định từ product

if ($comment_id > 0 && $product_id > 0) {
    // Kiểm tra xem bình luận có tồn tại không
    $check_query = "SELECT user_id FROM comments WHERE id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $comment_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $comment = $result->fetch_assoc();
        $is_owner = $comment['user_id'] == $user_id;
        $is_admin = $role == 'admin';

        if ($is_owner || $is_admin) {
            $delete_query = "DELETE FROM comments WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("i", $comment_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $_SESSION['message'] = "Bình luận đã được xóa thành công!";
            } else {
                $_SESSION['error'] = "Lỗi hệ thống khi xóa bình luận: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Bạn không có quyền xóa bình luận này!";
        }
    } else {
        $_SESSION['error'] = "Bình luận không tồn tại!";
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