<?php
session_start();
include '../config/db.php';

// Lấy thông tin sản phẩm dựa vào ID trên URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
if (!$product) {
    echo "<h2>Sản phẩm không tồn tại!</h2>";
    exit();
}
$product_id = $id;

// Lấy số lượng yêu thích của sản phẩm
$favorite_count_query = "SELECT COUNT(*) AS favorite_count FROM favorites WHERE product_id = ?";
$favorite_stmt = $conn->prepare($favorite_count_query);
$favorite_stmt->bind_param("i", $product_id);
$favorite_stmt->execute();
$favorite_result = $favorite_stmt->get_result();
$favorite_count = $favorite_result->fetch_assoc()['favorite_count'];

// Lấy bình luận
$comment_query = "SELECT c.id AS comment_id, c.comment, c.created_at, u.username, u.id AS user_id 
                  FROM comments c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.product_id = ? 
                  ORDER BY c.created_at DESC";
$comment_stmt = $conn->prepare($comment_query);
$comment_stmt->bind_param("i", $product_id);
$comment_stmt->execute();
$comments = $comment_stmt->get_result();

// Lấy đánh giá
$rating_query = "SELECT r.id AS rating_id, r.rating, r.created_at, u.username, u.id AS user_id 
                 FROM ratings r 
                 JOIN users u ON r.user_id = u.id 
                 WHERE r.product_id = ? 
                 ORDER BY r.created_at DESC";
$rating_stmt = $conn->prepare($rating_query);
$rating_stmt->bind_param("i", $product_id);
$rating_stmt->execute();
$ratings = $rating_stmt->get_result();

// Lấy đánh giá trung bình
$rating_avg_result = $conn->query("SELECT AVG(rating) AS avg_rating FROM ratings WHERE product_id = $product_id");
$rating_avg = $rating_avg_result->fetch_assoc()['avg_rating'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Coffee Shop</title>
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
        .product-container { 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
            padding: 20px;
             margin-top: 80px; 
            }
        .product-image img {
            width: 100%;
            height: auto;
            border-radius: 10px; 
            }
        .product-title { 
            color:rgb(36, 34, 32); 
            font-weight: bold; 
        }
        .price { 
            font-size: 24px; 
            color: #e74c3c; 
            font-weight: bold; 
        }
        .btn-custom { 
            background-color:rgb(104, 86, 72); 
            color: white; 
            transition: 0.3s; 
            margin-top: 4px; 
            margin-bottom: 50px; 
        }
        .btn-custom:hover {
             background-color: #6f4e37; 
             color: white
            }
        .comment-section, .rating-section { 
            margin-top: 30px; 
        }
        .comment-list, .rating-list { 
            display: none; /* Ẩn ban đầu */ }
        .comment-list li, .rating-list li { 
            background: #f1f1f1; 
            padding: 10px; 
            border-radius: 8px; 
            margin-bottom: 10px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .favorite-count { 
            font-size: 18px; 
            color: #6f4e37; 
            margin-top: 10px; }
        .delete-comment, .delete-rating { 
            color: #dc3545; 
            text-decoration: none; 
            font-size: 14px; }
        .delete-comment:hover, .delete-rating:hover { 
            color: #c82333; 
            text-decoration: underline; 
        }
        .show-btn { 
            background-color:rgb(104, 86, 72); 
            color: white; 
            border: none; 
            padding: 8px 16px; 
            border-radius: 5px; 
            cursor: pointer; }
        .show-btn:hover { 
            background-color: #6f4e37; 
            color: white
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
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="product-container">
                    <div class="row">
                        <div class="col-md-6 product-image">
                            <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>
                            <p class="favorite-count">Số lượt yêu thích: <?php echo $favorite_count; ?></p>
                            <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-custom">Thêm vào giỏ hàng</a>
                            <form action="favorite.php" method="post">
                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                <button type="submit" class="btn btn-danger">Yêu thích</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="comment-section">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <button class="show-btn" onclick="toggleComments()">Hiển thị bình luận</button>
                    <ul class="comment-list" id="commentList">
                        <?php while ($row = $comments->fetch_assoc()): ?>
                            <li>
                                <span><strong><?php echo htmlspecialchars($row['username']); ?>:</strong> <?php echo htmlspecialchars($row['comment']); ?> (<?php echo $row['created_at']; ?>)</span>
                                <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $row['user_id'] || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'))): ?>
                                       <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                                            <a href="edit_comment.php?comment_id=<?= $row['comment_id'] ?>&product_id=<?= $product_id ?>" class="edit-comment">Sửa</a>
                                            <a href="delete_comment.php?comment_id=<?= $row['comment_id'] ?>&product_id=<?= $product_id ?>" class="delete-comment" onclick="return confirm('Bạn có chắc chắn muốn xóa không?')">Xóa</a>
                                        <?php endif; ?>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    <form action="comment.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <textarea name="comment" class="form-control" required></textarea>
                        <button type="submit" class="btn btn-custom mt-2">Gửi Bình Luận</button>
                    </form>
                </div>

                <div class="rating-section">
                    <button class="show-btn" onclick="toggleRatings()">Hiển thị đánh giá</button>
                    <ul class="rating-list" id="ratingList">
                        <?php while ($row = $ratings->fetch_assoc()): ?>
                            <li>
                                <span><strong><?php echo htmlspecialchars($row['username']); ?>:</strong> <?php echo $row['rating']; ?> sao (<?php echo $row['created_at']; ?>)</span>
                                <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $row['user_id'] || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'))): ?>
                                    <a href="delete_rating.php?rating_id=<?php echo $row['rating_id']; ?>&product_id=<?php echo $product_id; ?>" 
                                       class="delete-rating" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa đánh giá này không?')">Xóa</a>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    <form action="rate.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <select name="rating" class="form-select">
                            <option value="1">1 sao</option>
                            <option value="2">2 sao</option>
                            <option value="3">3 sao</option>
                            <option value="4">4 sao</option>
                            <option value="5">5 sao</option>
                        </select>
                        <button type="submit" class="btn btn-custom mt-2">Gửi Đánh Giá</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleComments() {
            const commentList = document.getElementById('commentList');
            const button = document.querySelector('.comment-section .show-btn');
            if (commentList.style.display === 'none' || commentList.style.display === '') {
                commentList.style.display = 'block';
                button.textContent = 'Ẩn bình luận';
            } else {
                commentList.style.display = 'none';
                button.textContent = 'Hiển thị bình luận';
            }
        }

        function toggleRatings() {
            const ratingList = document.getElementById('ratingList');
            const button = document.querySelector('.rating-section .show-btn');
            if (ratingList.style.display === 'none' || ratingList.style.display === '') {
                ratingList.style.display = 'block';
                button.textContent = 'Ẩn đánh giá';
            } else {
                ratingList.style.display = 'none';
                button.textContent = 'Hiển thị đánh giá';
            }
        }
    </script>
</body>
</html>
<?php include '../config/footer.php'; ?>
