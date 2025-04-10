<?php
session_start();
include '../config/db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


if (empty($_SESSION['cart'])) {
    echo "<h1>Giỏ hàng của bạn đang trống!</h1>";
    echo "<a href='../index.php'>Quay lại trang chủ</a>";
    exit();
}

// Xử lý khi người dùng nhấn thanh toán
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $payment_method = trim($_POST['payment_method']);
    $total_price = 0;

    // Tính tổng tiền
    foreach ($_SESSION['cart'] as $product) {
        $total_price += $product['price'] * $product['quantity'];
    }

    // Chuẩn bị truy vấn INSERT vào bảng orders
    $query = "INSERT INTO orders (user_id, name, phone, address, total_price, status, payment_method) 
              VALUES (?, ?, ?, ?, ?, 'pending', ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }

    // Bind dữ liệu
    $stmt->bind_param("isssds", $user_id, $name, $phone, $address, $total_price, $payment_method);

    // Thực thi câu lệnh
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id; // Lấy ID đơn hàng vừa tạo

        // Thêm sản phẩm vào bảng order_details
        foreach ($_SESSION['cart'] as $id => $product) {
            $query = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("iiid", $order_id, $id, $product['quantity'], $product['price']);
                $stmt->execute();
            }
        }

        // Xóa giỏ hàng sau khi đặt hàng thành công
        unset($_SESSION['cart']);

        echo "<h1>Đặt hàng thành công!</h1>";
        echo "<a href='../index.php'>Quay lại trang chủ</a>";
    } else {
        echo "<h1>Đã xảy ra lỗi khi đặt hàng!</h1>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Thanh toán</h1>
        <form method="POST" action="checkout.php">
            <h2>Thông tin khách hàng:</h2>
            <div class="mb-3">
                <label class="form-label">Họ tên:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại:</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Địa chỉ:</label>
                <textarea name="address" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Phương thức thanh toán:</label>
                <select name="payment_method" class="form-control" required>
                    <option value="cod">Thanh toán khi nhận hàng</option>
                    <option value="bank">Chuyển khoản ngân hàng</option>
                    <option value="momo">Thanh toán qua MoMo</option>
                </select>
            </div>
            <h2>Thông tin đơn hàng:</h2>
            <ul class="list-group">
                <?php
                $total_price = 0;
                foreach ($_SESSION['cart'] as $product):
                    $subtotal = $product['price'] * $product['quantity'];
                    $total_price += $subtotal;
                ?>
                <li class="list-group-item">
                    <?php echo htmlspecialchars($product['name']); ?> - 
                    Số lượng: <?php echo $product['quantity']; ?> - 
                    Giá: <?php echo number_format($subtotal, 0, ',', '.'); ?> VND
                </li>
                <?php endforeach; ?>
            </ul>
            <h3 class="mt-3">Tổng cộng: <?php echo number_format($total_price, 0, ',', '.'); ?> VND</h3>
            <button type="submit" class="btn btn-primary mt-3">Xác nhận thanh toán</button>
            <a href="index.php" class="btn btn-secondary mt-3">Quay lại</a>
        </form>
    </div>
</body>
</html>
