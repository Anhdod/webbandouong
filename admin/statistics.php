<?php
session_start();
include '../config/db.php';


if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Lấy thống kê nhanh
$total_products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;

// Lấy doanh thu theo tháng
$revenue_data = array_fill(0, 12, 0);
$result_revenue = $conn->query("SELECT MONTH(created_at) AS month, SUM(total_price) AS revenue FROM orders WHERE YEAR(created_at) = YEAR(CURRENT_DATE) GROUP BY MONTH(created_at)");
if ($result_revenue) {
    while ($row = $result_revenue->fetch_assoc()) {
        $revenue_data[$row['month'] - 1] = $row['revenue'];
    }
}

// Lấy đơn hàng mới nhất
$recent_orders = $conn->query("SELECT o.id, COALESCE(u.username, 'Khách vãng lai') AS customer_name, o.total_price, o.created_at FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5") or die("Lỗi truy vấn đơn hàng: " . $conn->error);

// Lấy sản phẩm bán chạy nhất
$top_products = $conn->query("SELECT p.name, SUM(o.quantity) AS sold FROM order_details o JOIN products p ON o.product_id = p.id GROUP BY o.product_id ORDER BY sold DESC LIMIT 5") or die("Lỗi truy vấn sản phẩm bán chạy: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <ul  class="nav-left">
            <li><a href="../admin.php">Trang chủ</a></li>
            <li><a href="statistics.php">Thống kê</a></li>
            <li><a href="manage_products.php">Quản lý sản phẩm</a></li>
            <li><a href="manage_orders.php">Quản lý đơn hàng</a></li>
            <li><a href="manage_users.php">Quản lý người dùng</a></li>
            <li><a href="admin_reviews.php">Reviews</a></li>
            <li><a href="../config/logout.php">Đăng xuất</a></li>
        </ul>
    </nav>
    </header>
    <div class="container mt-4">
        <h2 class="mb-4">Thống kê tổng quan</h2>

        <div class="row">
            <div class="col-md-4">
                <div class="card text-bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Sản phẩm</h5>
                        <p class="card-text fs-3"><?= $total_products ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Đơn hàng</h5>
                        <p class="card-text fs-3"><?= $total_orders ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Người dùng</h5>
                        <p class="card-text fs-3"><?= $total_users ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h3>Doanh thu theo tháng</h3>
        <canvas id="revenueChart"></canvas>
        <script>
            var ctx = document.getElementById('revenueChart').getContext('2d');
            var revenueChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: [<?= implode(',', $revenue_data) ?>],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>

        <h3 class="mt-4">Đơn hàng mới nhất</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Ngày đặt</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $recent_orders->fetch_assoc()) { ?>
                <tr>
                    <td><?= $order['id'] ?? 'N/A' ?></td>
                    <td><?= $order['customer_name'] ?? 'N/A' ?></td>
                    <td><?= isset($order['total_price']) ? number_format($order['total_price'], 0, ',', '.') . ' VNĐ' : '0 VNĐ' ?></td>
                    <td><?= $order['created_at'] ?? 'N/A' ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <h3 class="mt-4">Sản phẩm bán chạy</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng bán</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $top_products->fetch_assoc()) { ?>
                <tr>
                    <td><?= $product['name'] ?? 'Chưa có dữ liệu' ?></td>
                    <td><?= $product['sold'] ?? 0 ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="../admin.php" class="btn btn-secondary">Quay lại</a>
    </div>
   
</body>
</html>
