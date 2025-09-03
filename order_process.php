<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Validate and sanitize input
        $nama_pemesan = filter_input(INPUT_POST, 'nama_pemesan', FILTER_SANITIZE_STRING);
        $kontak = filter_input(INPUT_POST, 'kontak', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
        $service_option_id = filter_input(INPUT_POST, 'service_option_id', FILTER_VALIDATE_INT);
        $jumlah_halaman = filter_input(INPUT_POST, 'jumlah_halaman', FILTER_VALIDATE_INT);
        $jumlah_copy = filter_input(INPUT_POST, 'jumlah_copy', FILTER_VALIDATE_INT);
        $metode_pengambilan = filter_input(INPUT_POST, 'metode_pengambilan', FILTER_SANITIZE_STRING);
        $catatan = filter_input(INPUT_POST, 'catatan', FILTER_SANITIZE_STRING);

        // Validate required fields
        if (!$nama_pemesan || !$kontak || !$service_id || !$service_option_id || 
            !$jumlah_halaman || !$jumlah_copy || !$metode_pengambilan) {
            throw new Exception("Semua field wajib diisi!");
        }

        // Handle file upload
        if (!isset($_FILES['document']) || $_FILES['document']['error'] != 0) {
            throw new Exception("Error uploading file!");
        }

        $file = $_FILES['document'];
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Format file tidak didukung!");
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            throw new Exception("Ukuran file terlalu besar! Maksimal 5MB");
        }

        // Create uploads directory if not exists
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            throw new Exception("Error saving file!");
        }

        // Get service and option details for price calculation
        $stmt = $pdo->prepare("SELECT s.harga_dasar, so.harga_tambahan 
                              FROM services s 
                              JOIN service_options so ON so.service_id = s.id 
                              WHERE s.id = ? AND so.id = ?");
        $stmt->execute([$service_id, $service_option_id]);
        $prices = $stmt->fetch();

        if (!$prices) {
            throw new Exception("Invalid service or option selected!");
        }

        // Calculate total price
        $total_harga = ($prices['harga_dasar'] + $prices['harga_tambahan']) * $jumlah_halaman * $jumlah_copy;

        // Insert or get customer
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE kontak = ?");
        $stmt->execute([$kontak]);
        $customer = $stmt->fetch();

        if (!$customer) {
            $stmt = $pdo->prepare("INSERT INTO customers (nama_pemesan, kontak, email) VALUES (?, ?, ?)");
            $stmt->execute([$nama_pemesan, $kontak, $email]);
            $customer_id = $pdo->lastInsertId();
        } else {
            $customer_id = $customer['id'];
        }

        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, service_id, service_option_id, jumlah_halaman, 
                              jumlah_copy, catatan, file_path, metode_pengambilan, total_harga) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_id, $service_id, $service_option_id, $jumlah_halaman, 
                       $jumlah_copy, $catatan, $file_path, $metode_pengambilan, $total_harga]);
        
        $order_id = $pdo->lastInsertId();

        // Insert initial order history
        $stmt = $pdo->prepare("INSERT INTO order_history (order_id, status_baru) VALUES (?, 'masuk')");
        $stmt->execute([$order_id]);

        $pdo->commit();

        // Redirect to success page
        header('Location: order_success.php?id=' . $order_id);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        
        // Delete uploaded file if exists
        if (isset($file_path) && file_exists($file_path)) {
            unlink($file_path);
        }
        
        header('Location: order.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: order.php');
    exit();
} 