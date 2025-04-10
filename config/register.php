<?php
session_start();
include 'db.php';

try {
    require '../vendor/autoload.php';
} catch (Exception $e) {
    die("Không thể tải PHPMailer: " . $e->getMessage());
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['verify_code'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);

    if ($password !== $confirm_password) {
        $error_message = "Mật khẩu xác nhận không khớp!";
    } else {
        $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Tên đăng nhập hoặc Email đã tồn tại!";
        } else {
            $verification_code = bin2hex(random_bytes(16));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';

            $query = "INSERT INTO users (username, password, email, role, verification_code, email_verified) VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssss", $username, $hashed_password, $email, $role, $verification_code);

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
                    $mail->addAddress($email, $username);
                    $mail->Subject = 'Xác nhận đăng ký tài khoản Coffee Shop';
                    $mail->isHTML(true);
                    $mail->Body = "Chào $username,<br><br>
                                   Cảm ơn bạn đã đăng ký. Nhập mã xác nhận sau vào form trên trang đăng ký:<br>
                                   <h3>$verification_code</h3>";

                    $mail->send();
                    $_SESSION['pending_verification'] = $email; 
                    $show_verification_form = true; 
                } catch (Exception $e) {
                    $error_message = "Không thể gửi email xác nhận. Lỗi: " . $mail->ErrorInfo;
                }
            } else {
                $error_message = "Đã xảy ra lỗi khi lưu thông tin. Vui lòng thử lại!";
            }
        }
    }
}

// Xử lý mã xác nhận nhập thủ công
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_code'])) {
    $entered_code = trim($_POST['verify_code']);
    $email = $_SESSION['pending_verification'] ?? '';

    $query = "SELECT * FROM users WHERE email = ? AND verification_code = ? AND email_verified = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $entered_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $update_query = "UPDATE users SET email_verified = 1, verification_code = NULL WHERE email = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            unset($_SESSION['pending_verification']);
            $success_message = "Xác nhận thành công! <a href='login.php'>Đăng nhập ngay</a>";
        } else {
            $error_message = "Có lỗi khi xác nhận. Vui lòng thử lại!";
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
    <title>Coffee Shop - Đăng ký</title>
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
      
        .register-form {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin-top: 50px;
            margin-left: 35%;
        }
        .register-form h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 28px;
        }
        .register-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .register-form button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            transition: background 0.3s;
        }
        .register-form button:hover {
            background-color: #0056b3;
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
    <main>
        <form class="register-form" method="POST" action="">
            <?php if (!isset($show_verification_form)): ?>
                <h2>Đăng ký tài khoản</h2>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <input type="text" name="username" placeholder="Tên đăng nhập" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                <button type="submit">Đăng ký</button>
                <a class="nav-link" href="login.php">Đã có tài khoản</a>
                <a href="../index.php" class="btn btn-secondary">Quay lại</a>
            <?php elseif (isset($show_verification_form)): ?>
                <h2>Xác nhận Email</h2>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php else: ?>
                    <p>Vui lòng nhập mã xác nhận đã được gửi đến email của bạn.</p>
                    <input type="text" name="verify_code" placeholder="Nhập mã xác nhận" required>
                    <button type="submit">Xác nhận</button>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </main>
</body>
</html>
<?php include 'footer.php'; ?>
