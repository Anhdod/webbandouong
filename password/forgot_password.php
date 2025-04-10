<?php
session_start();
include '../config/db.php';

try {
    require '../vendor/autoload.php';
} catch (Exception $e) {
    die("Không thể tải PHPMailer: " . $e->getMessage());
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reset_code = sprintf("%06d", mt_rand(100000, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $reset_code, $expires_at, $email);
        if ($stmt->execute()) { 
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'danhyeumot@gmail.com';
                $mail->Password = 'cldr otek yttq feig';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('your_email@gmail.com', 'Coffee Shop');
                $mail->addAddress($email);
                $mail->Subject = 'Đặt lại mật khẩu - Coffee Shop';
                $mail->isHTML(true);
                $mail->Body = "Chào bạn,<br><br>
                               Mã đặt lại mật khẩu của bạn là: <h3>$reset_code</h3><br>
                               Vui lòng nhập mã này vào trang xác nhận.<br>
                               Mã này sẽ hết hạn sau 1 giờ.<br><br>
                               Trân trọng,<br>Coffee Shop Team";

                $mail->send();
                $_SESSION['reset_email'] = $email;
                header("Location: enter_reset_code.php");
                exit();
            } catch (Exception $e) {
                $error_message = "Không thể gửi email. Lỗi: " . $mail->ErrorInfo;
            }
        } else {
            $error_message = "Không thể lưu mã vào cơ sở dữ liệu!";
        }
    } else {
        $error_message = "Email không tồn tại trong hệ thống!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Coffee Shop</title>
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
        .forgot-form {
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
    <form class="forgot-form" method="post" action="">
        <h2>Quên mật khẩu</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <input type="email" name="email" class="form-control" placeholder="Nhập email của bạn" required>
        <button type="submit" class="btn btn-primary mt-3">Gửi yêu cầu</button>
        <a href="../config/login.php" class="btn btn-secondary mt-2">Quay lại đăng nhập</a>
    </form>
</body>
</html>
<?php include '..config/footer.php'; ?>
