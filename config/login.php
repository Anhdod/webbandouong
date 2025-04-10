<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['email_verified'] == 0) {
                $error_message = "Vui lòng xác nhận email trước khi đăng nhập!";
            } elseif (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header("Location: ../admin.php");
                } else {
                    header("Location: ../index.php");
                }
                exit();
            } else {
                $error_message = "Mật khẩu không chính xác.";
            }
        } else {
            $error_message = "Tên đăng nhập không tồn tại.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Coffee Shop</title>
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
        .login-form {
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
        .form-control {
            margin-bottom: 15px;
            height: 45px;
        }
        .btn-primary {
            width: 100%;
            height: 45px;
            font-size: 18px;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background-color: #5a3e2b;
            border-color: #5a3e2b;
        }
        .form-title {
            font-weight: bold;
            margin-bottom: 20px;
            color: #5a3e2b;
        }
        .btn{
            margin-top: 10px;
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
    <form class="login-form" method="post" action="">
        <h2 class="form-title">Đăng nhập</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"> <?php echo $error_message; ?> </div>
        <?php endif; ?>
        <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
        <button type="submit" class="btn btn-primary">Đăng nhập</button>
        <a class="nav-link " href="register.php">Đăng ký ngay</a>
        <a href="../password/forgot_password.php" class="nav-link">Quên mật khẩu?</a>
        <a href="../index.php" class="btn btn-secondary">Quay lại</a>
    </form>
    
</body>
</html>
<?php include 'footer.php'; ?>
