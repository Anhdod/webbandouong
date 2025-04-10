<?php
session_start();


if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}
include '../config/db.php';

// Cập nhật người dùng
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    if (in_array($role, ['user', 'admin']) && in_array($status, ['active', 'inactive'])) {
        $stmt = $conn->prepare("UPDATE users SET role = ?, status = ? WHERE id = ?");
        $stmt->bind_param('ssi', $role, $status, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Cập nhật người dùng thành công!";
        } else {
            $_SESSION['error_message'] = "Lỗi khi cập nhật!";
        }
    } else {
        $_SESSION['error_message'] = "Dữ liệu không hợp lệ!";
    }
    header('Location: manage_users.php');
    exit();
}
// Thêm người dùng mới
if (isset($_POST['add_user'])) {
    $new_username = trim($_POST['new_username']);
    $new_email = trim($_POST['new_email']);
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT); 
    $new_role = $_POST['new_role'];
    $new_status = $_POST['new_status'];

    // Kiểm tra email đã tồn tại chưa
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $new_email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Email đã tồn tại!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $new_username, $new_email, $new_password, $new_role, $new_status);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thêm người dùng mới thành công!";
        } else {
            $_SESSION['error_message'] = "Lỗi khi thêm người dùng!";
        }
    }

    header('Location: manage_users.php');
    exit();
}


if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']); 

   
    $conn->query("DELETE FROM order_details WHERE order_id IN (SELECT id FROM orders WHERE user_id = $user_id)");
    $conn->query("DELETE FROM orders WHERE user_id = $user_id");
    $conn->query("DELETE FROM comments WHERE user_id = $user_id");
    $conn->query("DELETE FROM ratings WHERE user_id = $user_id");
    $conn->query("DELETE FROM favorites WHERE user_id = $user_id");

    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Xóa người dùng thành công!";
    } else {
        $_SESSION['error_message'] = "Lỗi khi xóa người dùng: " . $conn->error;
    }

    header('Location: manage_users.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Coffee Shop</title>
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
            z-index: 1000;
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
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .table-hover tbody tr:hover {
            background-color: #e9ecef;
        }
        .btn-update {
            background-color: #007bff;
            color: white;
        }
        .btn-update:hover {
            background-color: #0056b3;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .card{
            margin-bottom: 30px;
        }
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

    <div class="container">
        <h2 class="text-center mb-4">Quản lý người dùng</h2>

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

        <!-- Danh sách người dùng -->
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM users");
                        while ($user = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($user['role'])) ?></td>
                                <td><?= ucfirst(htmlspecialchars($user['status'])) ?></td>
                                <td>
                                    <form method="POST" action="manage_users.php" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="role" class="form-select form-select-sm d-inline" style="width: auto;">
                                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <select name="status" class="form-select form-select-sm d-inline" style="width: auto;">
                                            <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= $user['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                        <button type="submit" name="update_user" class="btn btn-update btn-sm" onclick="return confirm('Bạn có chắc muốn cập nhật thông tin người dùng này không?')">Cập nhật</button>
                                    </form>
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Chỉnh sửa</a>
                                    <a href="manage_users.php?delete=<?= $user['id'] ?>" class="btn btn-delete btn-sm" onclick="return confirm('Bạn có chắc muốn xóa người dùng này không?')">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Form thêm người dùng -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">Thêm người dùng mới</div>
    <div class="card-body">
        <form method="POST" action="manage_users.php">
            <div class="mb-3">
                <label class="form-label">Tên đăng nhập</label>
                <input type="text" name="new_username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="new_email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Vai trò</label>
                <select name="new_role" class="form-select">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="new_status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" name="add_user" class="btn btn-success">Thêm người dùng</button>
        </form>
    </div>
</div>

    </div>
</body>
</html>
  


