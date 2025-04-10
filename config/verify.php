<?php
session_start();
include 'db.php';

if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];

    // Kiểm tra mã xác nhận trong cơ sở dữ liệu
    $query = "SELECT * FROM users WHERE verification_code = ? AND email_verified = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cập nhật trạng thái email_verified thành 1
        $update_query = "UPDATE users SET email_verified = 1, verification_code = NULL WHERE verification_code = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("s", $verification_code);

        if ($update_stmt->execute()) {
            $success_message = "Email của bạn đã được xác nhận thành công! <a href='login.php'>Đăng nhập ngay</a>";
        } else {
            $error_message = "Có lỗi xảy ra khi xác nhận. Vui lòng thử lại!";
        }
    } else {
        $error_message = "Mã xác nhận không hợp lệ hoặc đã được sử dụng!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận Email - Coffee Shop</title>
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
            height: 25px;
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
                    <li><a href="../user/cart.php">Giỏ hàng</a></li>
                    <li><a href="../user/history.php">Lịch sử mua hàng</a></li>
                    <li><a href="../user/edit_profile.php">Chỉnh sửa thông tin cá nhân</a></li>
                    <li><a href="../user/favorites.php">Yêu thích</a></li>
                <?php endif; ?>
            </ul>
            <ul class="nav-right">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="user">Xin chào, <?php echo $_SESSION['username']; ?></li>
                    <li><a href="logout.php">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="login.php">Đăng nhập</a></li>
                    <li><a href="register.php">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container mt-5">
        <h2>Xác nhận Email</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>