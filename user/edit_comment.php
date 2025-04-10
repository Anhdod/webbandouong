<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$comment_id = $_GET['comment_id'] ?? 0;
$product_id = $_GET['product_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $comment = trim($_POST['comment']);
    $stmt = $conn->prepare("UPDATE comments SET comment = ?, created_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $comment, $comment_id, $_SESSION['user_id']);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: product.php?id=$product_id&message=Đã cập nhật bình luận!");
    } else {
        header("Location: product.php?id=$product_id&error=Lỗi khi cập nhật!");
    }
    exit();
}

$stmt = $conn->prepare("SELECT comment FROM comments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $_SESSION['user_id']);
$stmt->execute();
$comment = $stmt->get_result()->fetch_assoc()['comment'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa bình luận</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Chỉnh sửa bình luận</h2>
        <form method="post">
            <textarea name="comment" class="form-control" required><?= htmlspecialchars($comment) ?></textarea>
            <button type="submit" class="btn btn-custom mt-2">Lưu</button>
            <a href="product.php?id=<?= $product_id ?>" class="btn btn-secondary mt-2">Hủy</a>
        </form>
    </div>
</body>
</html>