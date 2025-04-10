<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}
include '../config/db.php';

// Xử lý thêm sản phẩm
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $stmt = $conn->prepare('INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssds', $name, $description, $price, $image);
    $stmt->execute();
    header('Location: manage_products.php');
    exit();
}

// Xử lý sửa sản phẩm
if (isset($_POST['edit_product'])) {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $stmt = $conn->prepare('UPDATE products SET name=?, description=?, price=?, image=? WHERE id=?');
    $stmt->bind_param('ssdsi', $name, $description, $price, $image, $id);
    $stmt->execute();
    header('Location: manage_products.php');
    exit();
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM products WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: manage_products.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
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

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
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

    <div class="container mt-5">
        <h1 class="mb-4">Quản lý sản phẩm</h1>

        <!-- Form thêm sản phẩm mới -->
        <div class="card mb-4">
            <div class="card-header">
                Thêm sản phẩm mới
            </div>
            <div class="card-body">
                <form method="post" action="manage_products.php">
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text" class="form-control" name="name" placeholder="Tên sản phẩm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả sản phẩm</label>
                        <textarea class="form-control" name="description" placeholder="Mô tả sản phẩm"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giá</label>
                        <input type="number" class="form-control" name="price" placeholder="Giá" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Đường dẫn hình ảnh</label>
                        <input type="text" class="form-control" name="image" placeholder="URL hình ảnh">
                    </div>
                    <button type="submit" name="add_product" class="btn btn-primary">Thêm sản phẩm</button>
                </form>
            </div>
        </div>

        <!-- Form sửa sản phẩm -->
        <?php
        if (isset($_GET['edit'])) {
            $id = $_GET['edit'];
            $result = $conn->query("SELECT * FROM products WHERE id=$id");
            $product = $result->fetch_assoc();
        ?>
        <div class="card mb-4">
            <div class="card-header">
                Sửa sản phẩm
            </div>
            <div class="card-body">
                <form method="post" action="manage_products.php">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text" class="form-control" name="name" value="<?php echo $product['name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả sản phẩm</label>
                        <textarea class="form-control" name="description"><?php echo $product['description']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giá</label>
                        <input type="number" class="form-control" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Đường dẫn hình ảnh</label>
                        <input type="text" class="form-control" name="image" value="<?php echo $product['image']; ?>">
                    </div>
                    <button type="submit" name="edit_product" class="btn btn-success">Cập nhật sản phẩm</button>
                    <a href="manage_products.php" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
        <?php } ?>

        <!-- Danh sách sản phẩm -->
        <h2 class="mb-4">Danh sách sản phẩm</h2>
        <div class="row">
            <?php
            $result = $conn->query('SELECT * FROM products');
            while ($row = $result->fetch_assoc()) {
                echo '<div class="col-md-4 mb-4">';
                echo '<div class="card product-card">';
                echo '<img src="../assets/images/' . $row['image'] . '" class="card-img-top" style="height: 250px; object-fit: cover;">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . $row['name'] . '</h5>';
                echo '<a href="manage_products.php?edit=' . $row['id'] . '" class="btn btn-warning me-2">Sửa</a>';
                echo '<a href="manage_products.php?delete=' . $row['id'] . '" class="btn btn-delete" onclick="return confirm(\'Bạn có chắc chắn muốn xóa sản phẩm này không?\')">Xóa</a>';
                echo '</div></div></div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
