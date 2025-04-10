<?php
include '../config/db.php';

if (isset($_POST['keyword'])) {
    $keyword = "%" . $_POST['keyword'] . "%";
    $query = "SELECT * FROM products WHERE name LIKE ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='product-item'>
                    <img src='assets/images/{$row['image']}' alt='{$row['name']}'>
                    <h2>{$row['name']}</h2>
                    <p>{$row['description']}</p>
                    <p class='price'>Giá: " . number_format($row['price'], 0, ',', '.') . " VND</p>
                    <a class='detail-btn' href='product.php?id={$row['id']}'>Xem chi tiết</a>
                  </div>";
        }
    } else {
        echo "<p>Không tìm thấy sản phẩm.</p>";
    }
}
?>
