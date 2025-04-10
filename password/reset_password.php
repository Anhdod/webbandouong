<?php
session_start();
include '../config/db.php';

if (isset($_GET['code'])) {
    $reset_code = $_GET['code'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_code = ?");
    $stmt->bind_param("s", $reset_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (strtotime($user['reset_expires']) > time()) {
            $_SESSION['reset_email'] = $user['email'];
        } else {
            $error_message = "Mã đặt lại đã hết hạn!";
        }
    } else {
        $error_message = "Mã đặt lại không hợp lệ!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['reset_email'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $error_message = "Mật khẩu xác nhận không khớp!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];

        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_expires = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        if ($stmt->execute()) {
            unset($_SESSION['reset_email']);
            $success_message = "Mật khẩu đã được đặt lại thành công! <a href='../config/login.php'>Đăng nhập ngay</a>";
        } else {
            $error_message = "Có lỗi xảy ra. Vui lòng thử lại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Coffee Shop</title>
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
        .reset-form {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin-top: 50px;
            margin-left: 35%;
        }
    </style>
</head>
<body>
<header>
        <nav class="navbar">
            <ul class="nav-left">
                <li><a href="../index.php">Trang chủ</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="../user/cart.php">Giỏ hàng</a></li>
                    <li><a href="../user/history.php">Lịch sử mua hàng</a></li>
                    <li><a href="../user/edit_profile.php">Chỉnh sửa thông tin cá nhân</a></li>
                    <li><a href="../user/favorites.php">Yêu thích</a></li>
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
    <form class="reset-form" method="post" action="">
        <h2>Đặt lại mật khẩu</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($_SESSION['reset_email'])): ?>
            <input type="password" name="new_password" class="form-control" placeholder="Mật khẩu mới" required>
            <input type="password" name="confirm_password" class="form-control mt-2" placeholder="Xác nhận mật khẩu" required>
            <button type="submit" class="btn btn-primary mt-3">Cập nhật mật khẩu</button>
        <?php endif; ?>
    </form>
</body>
</html>
<?php include '..config/footer.php'; ?>
