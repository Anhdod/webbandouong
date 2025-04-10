<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';
$favorite_id = isset($_GET['favorite_id']) ? intval($_GET['favorite_id']) : 0;
$from = isset($_GET['from']) ? $_GET['from'] : 'product';

if ($favorite_id > 0) {
    $check_query = "SELECT user_id FROM favorites WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    if ($check_stmt === false) {
        $_SESSION['error'] = "Lỗi chuẩn bị truy vấn: " . $conn->error;
        header("Location: favorites.php");
        exit();
    }
    $check_stmt->bind_param("i", $favorite_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $favorite = $result->fetch_assoc();
        $is_owner = $favorite['user_id'] == $user_id;
        $is_admin = $role == 'admin';

        if ($is_owner || $is_admin) {
            $delete_query = "DELETE FROM favorites WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("i", $favorite_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $_SESSION['message'] = "Yêu thích đã được xóa thành công!";
            } else {
                $_SESSION['error'] = "Lỗi hệ thống khi xóa yêu thích: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Bạn không có quyền xóa yêu thích này!";
        }
    } else {
        $_SESSION['error'] = "Yêu thích không tồn tại!";
    }
} else {
    $_SESSION['error'] = "Thông tin không hợp lệ!";
}

if ($from === 'admin') {
    header("Location: ../admin/admin_reviews.php");
} else {
    header("Location: favorites.php");
}
exit();
?>