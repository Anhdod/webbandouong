<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Lấy danh sách người đánh giá
$ratings = $conn->query("
    SELECT r.id AS rating_id, u.username, r.product_id, r.rating, r.created_at 
    FROM ratings r 
    JOIN users u ON r.user_id = u.id
");

// Lấy danh sách người bình luận
$comments = $conn->query("
    SELECT c.id AS comment_id, u.username, c.product_id, c.comment, c.created_at 
    FROM comments c 
    JOIN users u ON c.user_id = u.id
");

// Lấy danh sách người yêu thích
$favorites = $conn->query("
    SELECT f.id AS favorite_id, u.username, f.product_id, f.created_at 
    FROM favorites f 
    JOIN users u ON f.user_id = u.id
");

// Thống kê số lượng
$total_ratings = $conn->query("SELECT COUNT(DISTINCT user_id) AS total FROM ratings")->fetch_assoc()['total'];
$total_comments = $conn->query("SELECT COUNT(DISTINCT user_id) AS total FROM comments")->fetch_assoc()['total'];
$total_favorites = $conn->query("SELECT COUNT(DISTINCT user_id) AS total FROM favorites")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đánh giá & bình luận - Admin</title>
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
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
        }
        .delete-btn:hover {
            background-color: #c82333;
            color: white;
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
        <h2 class="text-center mb-4">Quản lý Đánh Giá, Bình Luận & Yêu Thích</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Thống kê nhanh -->
        <div class="row text-center">
            <div class="col-md-4">
                <h4>Tổng số người đánh giá</h4>
                <p class="fs-3 text-primary"><?= $total_ratings ?></p>
            </div>
            <div class="col-md-4">
                <h4>Tổng số người bình luận</h4>
                <p class="fs-3 text-success"><?= $total_comments ?></p>
            </div>
            <div class="col-md-4">
                <h4>Tổng số người yêu thích</h4>
                <p class="fs-3 text-danger"><?= $total_favorites ?></p>
            </div>
        </div>

        <!-- Danh sách người đánh giá -->
        <h3 class="mt-4">Danh sách Người Đánh Giá</h3>
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>Người dùng</th>
                    <th>ID Sản phẩm</th>
                    <th>Đánh giá</th>
                    <th>Thời gian</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $ratings->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><a href="product.php?id=<?= $row['product_id'] ?>">#<?= $row['product_id'] ?></a></td>
                    <td><?= $row['rating'] ?> ⭐</td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="../user/delete_rating.php?rating_id=<?= $row['rating_id'] ?>&product_id=<?= $row['product_id'] ?>&from=admin" 
                           class="delete-btn" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa đánh giá này không?')">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Danh sách người bình luận -->
        <h3 class="mt-4">Danh sách Người Bình Luận</h3>
        <table class="table table-bordered table-striped">
            <thead class="table-success">
                <tr>
                    <th>Người dùng</th>
                    <th>ID Sản phẩm</th>
                    <th>Bình luận</th>
                    <th>Thời gian</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $comments->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><a href="product.php?id=<?= $row['product_id'] ?>">#<?= $row['product_id'] ?></a></td>
                    <td><?= htmlspecialchars($row['comment']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="../user/delete_comment.php?comment_id=<?= $row['comment_id'] ?>&product_id=<?= $row['product_id'] ?>&from=admin" 
                           class="delete-btn" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này không?')">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Danh sách người yêu thích -->
        <h3 class="mt-4">Danh sách Người Yêu Thích</h3>
        <table class="table table-bordered table-striped">
            <thead class="table-danger">
                <tr>
                    <th>Người dùng</th>
                    <th>ID Sản phẩm</th>
                    <th>Thời gian</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $favorites->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><a href="product.php?id=<?= $row['product_id'] ?>">#<?= $row['product_id'] ?></a></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="../user/remove_favorite.php?favorite_id=<?= $row['favorite_id'] ?>&from=admin" 
                           class="delete-btn" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa yêu thích này không?')">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="../admin.php" class="btn btn-secondary mt-3">Quay lại</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>