<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

// Lấy danh sách sản phẩm yêu thích
if ($role == 'admin') {
    $query = "SELECT f.id AS favorite_id, p.id, p.name, p.price, p.image, u.username 
              FROM favorites f 
              JOIN products p ON f.product_id = p.id 
              JOIN users u ON f.user_id = u.id";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT f.id AS favorite_id, p.id, p.name, p.price, p.image 
              FROM favorites f 
              JOIN products p ON f.product_id = p.id 
              WHERE f.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm yêu thích - Coffee Shop</title>
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
        .favorites-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; padding: 20px; max-width: 500px; margin: 0 auto; }
        .favorite-item { background: white; border-radius: 15px; text-align: center; padding: 20px; border: 1px solid #ddd; }
        .favorite-item img { width: 100%; height: 220px; object-fit: cover; border-radius: 12px; }
        .favorite-item h2 { color: #222; font-size: 18px; margin: 12px 0; }
        .favorite-item .price { color: #4caf50; font-size: 18px; font-weight: bold; margin: 10px 0; }
        .detail-btn, .remove-btn { display: inline-block; padding: 10px 18px; border-radius: 8px; transition: all 0.3s; font-size: 14px; font-weight: bold; text-decoration: none; margin: 5px; }
        .detail-btn { background: #222; color: white; border: 1px solid #333; }
        .detail-btn:hover { background: #444; border-color: #666; transform: scale(1.05); }
        .remove-btn { background: #dc3545; color: white; border: 1px solid #c82333; }
        .remove-btn:hover { background: #c82333; border-color: #a71d2a; transform: scale(1.05); }
        .empty-message { text-align: center; font-size: 18px; color: #555; margin-top: 20px; }
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

    <main>
        <div class="container">
            <div class="favorites-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="favorite-item">
                            <img src="../assets/images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                            <h2><?php echo $row['name']; ?></h2>
                            <?php if ($role == 'admin'): ?>
                                <p>Người yêu thích: <?php echo $row['username']; ?></p>
                            <?php endif; ?>
                            <p class="price">Giá: <?php echo number_format($row['price'], 0, ',', '.'); ?> VND</p>
                            <a class="detail-btn" href="product.php?id=<?php echo $row['id']; ?>">Xem chi tiết</a>
                            <a class="remove-btn" href="remove_favorite.php?favorite_id=<?php echo $row['favorite_id']; ?>" 
                            onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích không?')">Xóa</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-message">Chưa có sản phẩm yêu thích nào.</p>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="../index.php" class="btn btn-secondary">Quay lại trang chủ</a>
            </div>
        </div>
    </main>
</body>
</html>


