<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// Lấy thông tin người dùng từ CSDL
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Nếu không tìm thấy người dùng (trường hợp hiếm gặp)
if (!$user) {
    $_SESSION['error_message'] = "Không tìm thấy người dùng!";
    header('Location: index.php');
    exit();
}

// Xử lý cập nhật thông tin người dùng
if (isset($_POST['update_profile'])) {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];

    // Nếu mật khẩu không thay đổi, giữ nguyên mật khẩu cũ
    if (empty($new_password)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE username = ?");
        $stmt->bind_param('sss', $new_username, $new_email, $username);
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE username = ?");
        $stmt->bind_param('ssss', $new_username, $new_email, $hashed_password, $username);
    }
    
    $stmt->execute();

    // Cập nhật session sau khi đổi tên đăng nhập
    $_SESSION['username'] = $new_username;
    $_SESSION['success_message'] = "Cập nhật thông tin cá nhân thành công!";
    header('Location: edit_profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin cá nhân - Coffee Shop</title>
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
    </style>
</head>
<body>
<header>
        <nav class="navbar">
            <ul class="nav-left">
                <li><a href="../index.php">Trang chủ</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="cart.php">Giỏ hàng</a></li>
                    <li><a href="history.php">Lịch sử mua hàng</a></li>
                    <li><a href="edit_profile.php">Chỉnh sửa thông tin cá nhân</a></li>
                    <li><a href="favorites.php">Yêu thích</a></li>
                <?php endif; ?>
            </ul>
            <ul class="nav-right">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="user">Xin chào, <?php echo $_SESSION['username']; ?></li>
                    <li><a href="../config/logout.php">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="../config/login.php">Đăng nhập</a></li>
                    <li><a href="../config/register.php">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container mt-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

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
                <label class="form-label">Mật khẩu mới (Để trống nếu không đổi)</label>
                <input type="password" class="form-control" name="password">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Cập nhật</button>
            <a href="../index.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</body>
</html>
<?php include '..config/footer.php'; ?>
