<?php
session_start();
include "../config/db.php";

// Kiểm tra quyền Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    exit("Từ chối truy cập");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['food_id'])) {
    $id = intval($_POST['food_id']);
    $new_stock = intval($_POST['new_stock']);

    // Đảm bảo số lượng không âm
    if ($new_stock < 0) $new_stock = 0;

    $stmt = $conn->prepare("UPDATE foods SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_stock, $id);

    if ($stmt->execute()) {
        header("Location: quanly_monan.php?msg=updated");
    } else {
        header("Location: quanly_monan.php?msg=error");
    }
    exit;
}
?>