<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../dangnhap.php");
    exit;
}

// Xử lý xóa
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM order_details WHERE food_id = $id");
    $conn->query("DELETE FROM foods WHERE id = $id");
    header("Location: quanly_monan.php?msg=deleted");
    exit;
}

$sql = "SELECT foods.*, categories.name AS category_name 
        FROM foods 
        LEFT JOIN categories ON foods.category_id = categories.id 
        ORDER BY foods.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý tồn kho - Admin Food</title>
    <link rel="stylesheet" href="admin.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Food</h2>
    <a href="trangchuadmin.php">🏠 Dashboard</a>
    <a href="quanly_monan.php" class="active">🍴 Quản lý món ăn</a>
    <a href="quanly_danhmuc.php">📂 Quản lý danh mục</a>
    <a href="quanly_donhang.php">🛒 Quản lý đơn hàng</a>
    <a href="quanly_nguoidung.php">👥 Quản lý người dùng</a>
    <a href="../dangxuat.php" style="margin-top: 50px; color: #ff7675;">🚪 Đăng xuất</a>
</div>

<div class="main-content">
    <div class="header-box">
        <span>Quản lý kho hàng / <strong>Cập nhật số lượng</strong></span>
        <strong>Admin: <?= htmlspecialchars($_SESSION['user']['username']) ?></strong>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success">✅ Đã cập nhật tồn kho!</div>
        <?php elseif($_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-danger">🗑️ Đã xóa món ăn khỏi hệ thống.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div style="margin-bottom: 20px;">
        <a href="them_monan.php" class="btn btn-add">+ Thêm món mới</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hình ảnh</th>
                    <th>Tên món</th>
                    <th>Giá bán</th>
                    <th width="150">Tồn kho (Sửa)</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><img src="../img/<?= htmlspecialchars($row['image']) ?>" style="width: 50px; height: 50px; border-radius: 5px; object-fit: cover;"></td>
                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                    <td style="color: #e67e22; font-weight: bold;"><?= number_format($row['price'], 0, ',', '.') ?>đ</td>
                    
                    <td>
                        <form action="capnhat_tonkho.php" method="POST" style="display: flex; gap: 5px;">
                            <input type="hidden" name="food_id" value="<?= $row['id'] ?>">
                            <input type="number" name="new_stock" value="<?= $row['stock'] ?>" min="0"
                                   style="width: 55px; padding: 4px; border: 1px solid #ccc; border-radius: 4px; text-align: center;">
                            <button type="submit" title="Lưu số lượng" style="background:#2ecc71; color:white; border:none; padding:5px 8px; border-radius:4px; cursor:pointer;">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                    </td>

                    <td>
                        <a href="sua_monan.php?id=<?= $row['id'] ?>" class="btn btn-edit">Sửa</a>
                        <a href="quanly_monan.php?delete_id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Xóa?')">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>