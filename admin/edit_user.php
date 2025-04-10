<?php
session_start();
include '../config/db.php';


if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Kiểm tra ID người dùng
if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit();
}

$user_id = $_GET['id'];

// Lấy thông tin người dùng từ CSDL
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Nếu không tìm thấy người dùng
if (!$user) {
    $_SESSION['error_message'] = "Không tìm thấy người dùng!";
    header('Location: manage_users.php');
    exit();
}

// Xử lý cập nhật thông tin người dùng
if (isset($_POST['update_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE id = ?");
    $stmt->bind_param('ssssi', $username, $email, $role, $status, $user_id);
    $stmt->execute();

    $_SESSION['success_message'] = "Cập nhật thông tin người dùng thành công!";
    header('Location: manage_users.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa người dùng - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Chỉnh sửa thông tin người dùng</h2>

        <form method="post" action="">
            <div class="mb-3">
                <label class="form-label">Tên đăng nhập</label>
                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Vai trò</label>
                <select name="role" class="form-select">
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $user['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" name="update_user" class="btn btn-primary">Cập nhật</button>
            <a href="manage_users.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</body>
</html>
