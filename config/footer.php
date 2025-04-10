<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        
        footer {
            background-color: #2c2c2c;
            color: #fff;
            padding: 40px 20px;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin-top: 100px;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        .footer-section {
            flex: 1;
            min-width: 200px;
        }
        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #f39c12; 
            text-transform: uppercase;
        }
        .footer-section p, .footer-section a {
            font-size: 14px;
            color: #ccc;
            text-decoration: none;
        }
        .footer-section a:hover {
            color: #f39c12;
            transition: color 0.3s ease;
        }
        .social-links a {
            margin: 0 10px;
            font-size: 16px;
        }
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #444;
            margin-top: 20px;
            font-size: 13px;
            color: #999;
        }
        @media (max-width: 768px) {
            .footer-container {
                flex-direction: column;
                text-align: center;
            }
            .social-links a {
                margin: 5px;
            }
        }
    </style>
</head>
<body>
    <footer>
        <div class="footer-container">
           
            <div class="footer-section">
                <h3>Liên Hệ</h3>
                <p>Email: <a href="mailto:info@do-uong-ngon.com">info@do-uong-ngon.com</a></p>
                <p>Hotline: <a href="tel:0123456789">0123 456 789</a></p>
                <p>Địa chỉ: 123 Đường Thơm Ngát, TP. HCM</p>
            </div>

            <div class="footer-section">
                <h3>Liên Kết Nhanh</h3>
                <p><a href="#">Trang Chủ</a></p>
                <p><a href="#">Sản Phẩm</a></p>
                <p><a href="#">Giới Thiệu</a></p>
                <p><a href="#">Liên Hệ</a></p>
            </div>

         
            <div class="footer-section">
                <h3>Theo Dõi Chúng Tôi</h3>
                <div class="social-links">
                    <a href="#">Facebook</a> |
                    <a href="#">Instagram</a> |
                    <a href="#">Twitter</a>
                </div>
                <p>Đăng ký nhận ưu đãi qua email:</p>
                <form action="#" method="post">
                    <input type="email" placeholder="Nhập email của bạn" style="padding: 5px; border: none;">
                    <button type="submit" style="padding: 5px 10px; background: #f39c12; border: none; color: #fff;">Đăng Ký</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?php echo date("Y"); ?> Đồ Uống Ngon. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>