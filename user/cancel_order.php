<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $user_id = $_SESSION['user_id'];

    // Kiểm tra xem đơn hàng có thuộc về người dùng và đang "pending" không
    $query = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cập nhật trạng thái thành "cancelled"
        $update_query = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $order_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['message'] = "Đơn hàng #$order_id đã được hủy thành công.";
            } else {
                $_SESSION['error'] = "Không có thay đổi nào được thực hiện.";
            }
        } else {
            $_SESSION['error'] = "Lỗi khi hủy đơn hàng: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Đơn hàng không tồn tại hoặc không thể hủy.";
    }
} else {
    $_SESSION['error'] = "Không tìm thấy đơn hàng để hủy.";
}

header("Location: history.php");
exit();
?>