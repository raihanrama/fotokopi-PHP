<?php
require_once '../includes/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get order ID
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id > 0) {
    try {
        // Get file path before deleting
        $stmt = $pdo->prepare("SELECT file_path FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();

        if ($order) {
            // Delete the order
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);

            // Delete the file if it exists
            if ($order['file_path'] && file_exists('../' . $order['file_path'])) {
                unlink('../' . $order['file_path']);
            }

            header('Location: dashboard.php?success=1');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: dashboard.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

header('Location: dashboard.php');
exit(); 