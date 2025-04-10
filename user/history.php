<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red;'>Vui lòng <a href='login.php'>đăng nhập</a> để xem lịch sử mua hàng.</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý mua lại đơn hàng
if (isset($_GET['reorder']) && intval($_GET['reorder']) > 0) {
    $order_id = intval($_GET['reorder']);
    $query = "SELECT od.product_id, od.quantity FROM order_details od WHERE od.order_id = ? AND EXISTS (SELECT 1 FROM products p WHERE p.id = od.product_id)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    while ($item = $result->fetch_assoc()) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        $product_query = "SELECT name, price, image FROM products WHERE id = ?";
        $product_stmt = $conn->prepare($product_query);
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product = $product_stmt->get_result()->fetch_assoc();

        if ($product) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    "name" => $product['name'],
                    "price" => $product['price'],
                    "quantity" => $quantity,
                    "image" => $product['image']
                ];
            }
        }
    }
    $_SESSION['message'] = "Đã thêm sản phẩm từ đơn hàng #$order_id vào giỏ hàng!";
    header("Location: cart.php");
    exit();
}

// Lấy lịch sử mua hàng
$query = "SELECT * FROM orders WHERE user_id = ? AND status != 'cancelled' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mua hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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


        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Đơn hàng #<?php echo $order['id']; ?></h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Ngày đặt:</strong> <?php echo $order['created_at']; ?></p>
                        <p><strong>Trạng thái:</strong> <span id="status-<?php echo $order['id']; ?>"><?php echo $order['status']; ?></span></p>
                        <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total_price'], 0, ',', '.'); ?> VND</p>

                        <h5>Chi tiết sản phẩm:</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tên sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $order_id = $order['id'];
                                $item_query = "SELECT od.quantity, od.price, p.name 
                                               FROM order_details od 
                                               JOIN products p ON od.product_id = p.id 
                                               WHERE od.order_id = ?";
                                $stmt_item = $conn->prepare($item_query);
                                $stmt_item->bind_param("i", $order_id);
                                $stmt_item->execute();
                                $item_result = $stmt_item->get_result();
                                ?>
                                <?php while ($item = $item_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $item['name']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VND</td>
                                        <td><?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?> VND</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <div>
                            <?php if ($order['status'] == 'pending'): ?>
                                <a href="cancel_order.php?order_id=<?php echo $order['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">Hủy đơn</a>
                            <?php else: ?>
                                <span class="text-muted">Không thể hủy</span>
                            <?php endif; ?>
                            <a href="history.php?reorder=<?php echo $order['id']; ?>" class="btn btn-success btn-sm ms-2">Mua lại</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Bạn chưa có đơn hàng nào.</p>
        <?php endif; ?>

        <a href="../index.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <script>
        function updateOrderStatus() {
            $.ajax({
                url: "fetch_order_status.php",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    data.forEach(function(order) {
                        $("#status-" + order.id).text(order.status);
                    });
                }
            });
        }
        setInterval(updateOrderStatus, 5000);
    </script>
</body>
</html>
<?php include '../config/footer.php'; ?>
