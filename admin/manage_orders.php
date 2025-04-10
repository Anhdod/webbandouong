<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $order_id);
    $stmt->execute();
    header('Location: manage_orders.php');
    exit();
}

// Xóa đơn hàng
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // Đảm bảo ID là số nguyên

    // Bắt đầu giao dịch để đảm bảo xóa đồng bộ
    $conn->begin_transaction();
    try {
        // Xóa chi tiết đơn hàng trước
        $stmt = $conn->prepare('DELETE FROM order_details WHERE order_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Xóa đơn hàng
        $stmt = $conn->prepare('DELETE FROM orders WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Kiểm tra xem có bản ghi nào bị xóa không
        if ($stmt->affected_rows > 0) {
            $conn->commit(); // Xác nhận giao dịch
            $_SESSION['success_message'] = "Xóa đơn hàng #$id thành công!";
        } else {
            throw new Exception("Không tìm thấy đơn hàng để xóa.");
        }
    } catch (Exception $e) {
        $conn->rollback(); // Hoàn tác nếu có lỗi
        $_SESSION['error_message'] = "Lỗi khi xóa đơn hàng: " . $e->getMessage();
    }

    header('Location: manage_orders.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
       body, h1, h2, p, ul, li, a, input {
            margin: 0;
            padding: 0;
            list-style: none;
            text-decoration: none;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }
        body {
            background-color: white;
            color: #333;
            padding-top: 70px;
        }
        .navbar {
            background: white;
            color: black;
            width: 100%;
            height: 45px;
            position: fixed;
            top: 0;
            left: 0;
            padding: 10px 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar ul {
            display: flex;
            align-items: center;
        }
        .navbar ul li {
            margin: 0 15px;
        }
        .navbar ul li a {
            color: black;
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.3s ease-in-out;
        }
        .navbar ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .nav-left {
            flex: 2;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-completed { color: #28a745; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
        .btn-update { background-color: #007bff; color: white; }
        .btn-update:hover { background-color: #0056b3; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-delete:hover { background-color: #c82333; }
    </style>
</head>
<body>
<header>
    <nav class="navbar">
        <ul  class="nav-left">
            <li><a href="../admin.php">Trang chủ</a></li>
            <li><a href="statistics.php">Thống kê</a></li>
            <li><a href="manage_products.php">Quản lý sản phẩm</a></li>
            <li><a href="manage_orders.php">Quản lý đơn hàng</a></li>
            <li><a href="manage_users.php">Quản lý người dùng</a></li>
            <li><a href="admin_reviews.php">Reviews</a></li>
            <li><a href="../config/logout.php">Đăng xuất</a></li>
        </ul>
    </nav>
    </header>

    <div class="container mt-5">
        <h1 class="mb-4">Quản lý đơn hàng</h1>

        <!-- Thông báo trạng thái -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Người đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Cập nhật</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("
                    SELECT orders.*, users.username 
                    FROM orders 
                    JOIN users ON orders.user_id = users.id 
                    ORDER BY orders.created_at DESC
                ");
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                    echo '<td>' . number_format($row['total_price'], 0, ',', '.') . ' VND</td>';
                    echo '<td class="';
                    if ($row['status'] == 'pending') echo 'status-pending';
                    elseif ($row['status'] == 'completed') echo 'status-completed';
                    elseif ($row['status'] == 'cancelled') echo 'status-cancelled';
                    echo '">' . $row['status'] . '</td>';
                    echo '<td>' . $row['created_at'] . '</td>';
                    echo '<td>
                        <form method="post" action="manage_orders.php">
                            <input type="hidden" name="order_id" value="' . $row['id'] . '">
                            <select name="status" class="form-select form-select-sm mb-2">
                                <option value="pending"' . ($row['status'] == 'pending' ? ' selected' : '') . '>Đang xử lý</option>
                                <option value="completed"' . ($row['status'] == 'completed' ? ' selected' : '') . '>Hoàn thành</option>
                                <option value="cancelled"' . ($row['status'] == 'cancelled' ? ' selected' : '') . '>Đã hủy</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-update btn-sm">Cập nhật</button>
                        </form>
                    </td>';
                    echo '<td>
                        <a href="manage_orders.php?delete=' . $row['id'] . '" 
                        class="btn btn-delete btn-sm"
                        onclick="return confirm(\'Bạn có chắc chắn muốn xóa đơn hàng này không?\')">
                        Xóa
                        </a>
                    </td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>