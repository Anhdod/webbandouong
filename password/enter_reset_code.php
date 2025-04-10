<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_code = trim($_POST['reset_code']);

    // Kiểm tra mã đặt lại trong cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND reset_code = ?");
    $stmt->bind_param("ss", $email, $entered_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Kiểm tra thời gian hết hạn
        if (strtotime($user['reset_expires']) > time()) {
            header("Location: reset_password.php?code=$entered_code");
            exit();
        } else {
            $error_message = "Mã xác nhận đã hết hạn! Vui lòng yêu cầu mã mới.";
        }
    } else {
        $error_message = "Mã xác nhận không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập mã xác nhận - Coffee Shop</title>
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
        .code-form {
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
    <form class="code-form" method="post" action="">
        <h2>Nhập mã xác nhận</h2>
        <p>Mã xác nhận đã được gửi đến email: <?php echo htmlspecialchars($email); ?></p>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <input type="text" name="reset_code" class="form-control" placeholder="Nhập mã 6 chữ số" required maxlength="6" pattern="\d{6}">
        <button type="submit" class="btn btn-primary mt-3">Xác nhận</button>
        <a href="forgot_password.php" class="btn btn-secondary mt-2">Yêu cầu mã mới</a>
    </form>
</body>
</html>
<?php include '..config/footer.php'; ?>
