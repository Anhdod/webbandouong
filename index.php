<?php
session_start();
include 'config/db.php';

$query = "SELECT * FROM products";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sản phẩm</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
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
            height: 25px;
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
        .nav-right {
            flex: 1;
            display: flex;
            justify-content: flex-end;
        }
        .slider {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .slide-item {
            padding: 10px;
        }
        .slide-item img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .user {
            color: black;
            font-weight: bold;
            padding: 8px 15px;
        }
        .search-box {
            display: block;
            width: 80%;
            max-width: 400px;
            margin: 20px auto;
            padding: 12px 15px;
            border-radius: 30px;
            border: 2px solid #999;
            font-size: 16px;
            background: white;
            color: #333;
            outline: none;
            transition: 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .search-box:focus {
            border-color: #444;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
        }
        #product-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .product-item {
            background: white;
            border-radius: 15px;
            text-align: center;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .product-item img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
        }
        .product-item h2 {
            color: #222;
            font-size: 18px;
            margin: 12px 0;
        }
        .product-item p {
            color: #555;
            font-size: 14px;
        }
        .price {
            color: #4caf50;
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .detail-btn {
            display: inline-block;
            padding: 10px 18px;
            background: #222;
            color: white;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #333;
        }
        .detail-btn:hover {
            background: #444;
            border-color: #666;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <ul class="nav-left">
                <li><a href="index.php">Trang chủ</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="user/cart.php">Giỏ hàng</a></li>
                    <li><a href="user/history.php">Lịch sử mua hàng</a></li>
                    <li><a href="user/edit_profile.php">Chỉnh sửa thông tin cá nhân</a></li>
                    <li><a href="user/favorites.php">Yêu thích</a></li>
                <?php endif; ?>
            </ul>
            <ul class="nav-right">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="user">Xin chào, <?php echo $_SESSION['username']; ?></li>
                    <li><a href="config/logout.php">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="config/login.php">Đăng nhập</a></li>
                    <li><a href="config/register.php">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="slider">
            <div class="slide-item">
                <img src="assets/images/b1.png" alt="Slide 1">
            </div>
            <div class="slide-item">
                <img src="assets/images/b2.png" alt="Slide 2">
            </div>
            <div class="slide-item">
                <img src="assets/images/b3.png" alt="Slide 3">
            </div>
        </div>

        <input type="text" id="search" class="search-box" placeholder="Nhập tên sản phẩm...">
        <div id="product-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product-item">
                    <img src="assets/images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                    <h2><?php echo $row['name']; ?></h2>
                    <p class="price">Giá: <?php echo number_format($row['price'], 0, ',', '.'); ?> VND</p>
                    <a class="detail-btn" href="user/product.php?id=<?php echo $row['id']; ?>">Xem chi tiết</a>
                </div>
            <?php endwhile; ?>
        </div>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>
       $(document).ready(function() {
            $('.slider').slick({
                autoplay: true,
                autoplaySpeed: 3000,
                dots: true,
                arrows: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true,
                responsive: [
                    {
                        breakpoint: 768,
                        settings: {
                            arrows: false
                        }
                    }
                ]
            });
            $("#search").keyup(function() {
                var keyword = $(this).val();
                $.ajax({
                    url: "search_product.php",
                    method: "POST",
                    data: { keyword: keyword },
                    success: function(response) {
                        $("#product-list").html(response);
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php include 'config/footer.php'; ?>