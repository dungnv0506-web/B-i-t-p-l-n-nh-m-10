<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

require 'config/db.php';

$user = $_SESSION['user'];
$order_id = intval($_GET['id'] ?? 0);

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user['id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("❌ Đơn hàng không tồn tại");
}

// Lấy chi tiết món
$stmt = $conn->prepare(
    "SELECT f.name, f.image, od.quantity, od.price 
     FROM order_details od 
     JOIN foods f ON od.food_id = f.id 
     WHERE od.order_id = ?"
);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();

// Cấu hình thanh trạng thái
$steps_config = [
    'pending'    => ['label' => 'Xác nhận', 'icon' => '⏳', 'step' => 1],
    'processing' => ['label' => 'Chế biến', 'icon' => '🍳', 'step' => 2],
    'shipping'   => ['label' => 'Đang giao', 'icon' => '🚚', 'step' => 3],
    'completed'  => ['label' => 'Đã nhận',   'icon' => '✅', 'step' => 4]
];
$current_step = $steps_config[$order['status']]['step'] ?? 0;
$is_cancelled = ($order['status'] == 'cancelled');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $order['id'] ?></title>
    <link rel="stylesheet" href="trangchu.css">
    <link rel="stylesheet" href="order_detail.css">
</head>
<body>

<header class="topbar">
    <div class="logo">🍔 FoodStore</div>
    <div class="user-box">
        Xin chào <b><?= htmlspecialchars($user['username']) ?></b>
        <a href="donhang.php">📦 Đơn hàng</a>
        <a href="dangxuat.php">Đăng xuất</a>
    </div>
</header>

<section class="hero small-hero">
    <h1>📄 Chi tiết đơn hàng #<?= $order['id'] ?></h1>
</section>

<main class="main order-container">

    <div class="status-stepper">
        <?php if ($is_cancelled): ?>
            <div class="step-cancelled">❌ Đơn hàng này đã bị hủy</div>
        <?php else: ?>
            <?php foreach ($steps_config as $key => $info): ?>
                <div class="step <?= ($current_step >= $info['step']) ? 'active' : '' ?>">
                    <div class="step-icon"><?= $info['icon'] ?></div>
                    <div class="step-text"><?= $info['label'] ?></div>
                    <?php if ($info['step'] < 4): ?>
                        <div class="step-line"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="order-summary-grid">
        <div class="order-box">
            <h3 class="order-box-header">📍 Vận chuyển</h3>
            <p><b>Ngày đặt:</b> <?= date("d/m/Y H:i", strtotime($order['created_at'])) ?></p>
            <p><b>Trạng thái:</b> <span class="status status-<?= $order['status'] ?>"><?= $order['status'] ?></span></p>
        </div>
        <div class="order-box">
            <h3 class="order-box-header">💰 Tổng tiền</h3>
            <p>Thanh toán khi nhận hàng</p>
            <h2 class="total-price-large"><?= number_format($order['total']) ?>đ</h2>
        </div>
    </div>

    <h3 style="margin-bottom:15px;">🍽️ Món đã đặt</h3>
    <div class="cart-list">
        <?php while ($item = $items->fetch_assoc()): ?>
        <div class="cart-item">
            <img src="images/<?= $item['image'] ?>">
            <div class="cart-info">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p>Số lượng: <b><?= $item['quantity'] ?></b></p>
                <p class="price"><?= number_format($item['price'] * $item['quantity']) ?>đ</p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="cart-actions">
        <a href="donhang.php" class="btn-back">⬅ Quay lại</a>
    </div>

</main>
</body>
</html>