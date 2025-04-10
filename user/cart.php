<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red;'>Vui lòng <a href='../config/login.php'>đăng nhập</a> để thêm sản phẩm vào giỏ hàng.</p>";
    exit();
}

include '../config/db.php';


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

//  thêm sản phẩm vào giỏ hàng
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                "name" => $product['name'],
                "price" => $product['price'],
                "quantity" => 1,
                "image" => $product['image']
            ];
        }
    }
    header('Location: cart.php');
    exit();
}

//  xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = intval($_GET['id']);
    unset($_SESSION['cart'][$id]);
    header('Location: cart.php');
    exit();
}

// Cập nhật số lượng sản phẩm trong giỏ hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $quantity) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id]['quantity'] = $quantity;
        }
    }
    header('Location: cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Coffee Shop</title>
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
        .cart-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .cart-image {
            width: 80px;
            height: auto;
        }
        img{
            width: 100px;
            height: 100px;
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
    <div class="container cart-container">
        <form method="POST" action="cart.php">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($_SESSION['cart'])): 
                    $total_price = 0;
                    foreach ($_SESSION['cart'] as $id => $product):
                        $subtotal = $product['price'] * $product['quantity'];
                        $total_price += $subtotal;
                ?>
                    <tr>
                        <td> <img src="../assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>"></td>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</td>
                        <td>
                            <input type="number" name="quantity[<?php echo $id; ?>]" value="<?php echo $product['quantity']; ?>" min="1" class="form-control w-50 mx-auto">
                        </td>
                        <td><?php echo number_format($subtotal, 0, ',', '.'); ?> VND</td>
                        <td>
                            <a href="cart.php?action=delete&id=<?php echo $id; ?>" class="btn btn-danger btn-sm">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                        <td colspan="2"><strong><?php echo number_format($total_price, 0, ',', '.'); ?> VND</strong></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Giỏ hàng trống</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
            <div class="text-center">
                <button type="submit" name="update_cart" class="btn btn-warning">Cập nhật giỏ hàng</button>
                <a href="checkout.php" class="btn btn-success">Thanh toán</a>
            </div>
            <a href="../index.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</body>
</html>
<?php include '../config/footer.php'; ?>
