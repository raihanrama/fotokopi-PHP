<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header('Location: dashboard.php');
    exit();
}

// Get order details
try {
    $stmt = $pdo->prepare("
        SELECT o.*, c.nama_pemesan, c.kontak, c.email,
               s.nama_layanan, s.harga_dasar,
               so.nama_opsi, so.harga_tambahan
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        JOIN services s ON o.service_id = s.id
        JOIN service_options so ON o.service_option_id = so.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: dashboard.php');
        exit();
    }

    // Get order history
    $stmt = $pdo->prepare("
        SELECT * FROM order_history 
        WHERE order_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$order_id]);
    $history = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Terjadi kesalahan saat mengambil data pesanan.";
    error_log("Order detail error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - CepatCopy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <h4 class="text-white mb-4">CepatCopy Admin</h4>
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="services.php" class="nav-link">
                                <i class="bi bi-gear me-2"></i>Kelola Layanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Detail Pesanan #<?php echo $order_id; ?></h2>
                    <div>
                        <a href="edit_order.php?id=<?php echo $order_id; ?>" class="btn btn-primary me-2">
                            <i class="bi bi-pencil me-2"></i>Edit
                        </a>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Status pesanan berhasil diperbarui!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <!-- Order Details -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Detail Pesanan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6>Informasi Pelanggan</h6>
                                        <p class="mb-1">
                                            <strong>Nama:</strong> <?php echo htmlspecialchars($order['nama_pemesan']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Kontak:</strong> <?php echo htmlspecialchars($order['kontak']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Detail Layanan</h6>
                                        <p class="mb-1">
                                            <strong>Layanan:</strong> <?php echo htmlspecialchars($order['nama_layanan']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Opsi:</strong> <?php echo htmlspecialchars($order['nama_opsi']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Jumlah Halaman:</strong> <?php echo $order['jumlah_halaman']; ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Jumlah Copy:</strong> <?php echo $order['jumlah_copy']; ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Informasi Pengambilan</h6>
                                        <p class="mb-1">
                                            <strong>Metode:</strong> 
                                            <?php echo $order['metode_pengambilan'] == 'ambil' ? 'Ambil Sendiri' : 'Ojek Online'; ?>
                                        </p>
                                        <?php if ($order['catatan']): ?>
                                            <p class="mb-1">
                                                <strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($order['catatan'])); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Informasi Harga</h6>
                                        <p class="mb-1">
                                            <strong>Harga Dasar:</strong> 
                                            Rp <?php echo number_format($order['harga_dasar'], 0, ',', '.'); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Harga Tambahan:</strong> 
                                            Rp <?php echo number_format($order['harga_tambahan'], 0, ',', '.'); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Total Harga:</strong> 
                                            Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?>
                                        </p>
                                    </div>
                                </div>

                                <?php if ($order['file_path']): ?>
                                    <div class="mt-3">
                                        <h6>File Dokumen</h6>
                                        <a href="../<?php echo htmlspecialchars($order['file_path']); ?>" 
                                           class="btn btn-sm btn-primary" target="_blank">
                                            <i class="bi bi-download me-2"></i>Download File
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Order History -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Riwayat Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <?php foreach ($history as $item): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker"></div>
                                            <div class="timeline-content">
                                                <h6 class="mb-1">
                                                    <?php
                                                    $status_text = [
                                                        'masuk' => 'Masuk',
                                                        'diproses' => 'Diproses',
                                                        'selesai' => 'Selesai',
                                                        'siap_diambil' => 'Siap Diambil'
                                                    ];
                                                    echo $status_text[$item['status_baru']] ?? ucfirst($item['status_baru']);
                                                    ?>
                                                </h6>
                                                <p class="text-muted mb-1">
                                                    <?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?>
                                                </p>
                                                <?php if ($item['catatan']): ?>
                                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($item['catatan'])); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Order Status -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Status Pesanan</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $status_class = [
                                    'masuk' => 'warning',
                                    'diproses' => 'info',
                                    'selesai' => 'success',
                                    'siap_diambil' => 'primary'
                                ];
                                $status_text = [
                                    'masuk' => 'Masuk',
                                    'diproses' => 'Diproses',
                                    'selesai' => 'Selesai',
                                    'siap_diambil' => 'Siap Diambil'
                                ];
                                ?>
                                <div class="text-center mb-3">
                                    <span class="badge bg-<?php echo $status_class[$order['status']]; ?> fs-5">
                                        <?php echo $status_text[$order['status']]; ?>
                                    </span>
                                </div>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-calendar me-2"></i>
                                    Tanggal Pesanan: <?php echo date('d/m/Y H:i', strtotime($order['tanggal_pesan'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-marker {
            position: absolute;
            left: -30px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #0d6efd;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #0d6efd;
        }
        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 15px;
            height: calc(100% - 15px);
            width: 2px;
            background: #dee2e6;
        }
    </style>
</body>
</html> 