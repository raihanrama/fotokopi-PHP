<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle delete order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    try {
        $order_id = $_POST['order_id'];
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete order history first
        $stmt = $pdo->prepare("DELETE FROM order_history WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        // Then delete the order
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        
        $pdo->commit();
        $success = "Pesanan berhasil dihapus!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting order: " . $e->getMessage());
        $error = "Terjadi kesalahan saat menghapus pesanan.";
    }
}

// Get statistics
$stats = [
    'total_orders' => 0,
    'pending_orders' => 0,
    'processing_orders' => 0,
    'completed_orders' => 0,
    'total_revenue' => 0
];

try {
    // Get total orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $stats['total_orders'] = $stmt->fetchColumn();

    // Get orders by status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    while ($row = $stmt->fetch()) {
        switch ($row['status']) {
            case 'masuk':
                $stats['pending_orders'] = $row['count'];
                break;
            case 'diproses':
                $stats['processing_orders'] = $row['count'];
                break;
            case 'selesai':
            case 'siap_diambil':
                $stats['completed_orders'] += $row['count'];
                break;
        }
    }

    // Get total revenue
    $stmt = $pdo->query("SELECT SUM(total_harga) FROM orders WHERE status != 'batal'");
    $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

    // Get recent orders
    $stmt = $pdo->query("
        SELECT o.*, c.nama_pemesan, c.kontak, s.nama_layanan, so.nama_opsi
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        JOIN services s ON o.service_id = s.id
        JOIN service_options so ON o.service_option_id = so.id
        ORDER BY o.tanggal_pesan DESC
        LIMIT 10
    ");
    $recent_orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error = "Terjadi kesalahan saat mengambil data.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CepatCopy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #3730a3;
            --accent-color: #6366f1;
            --text-color: #1e293b;
            --light-bg: #f8fafc;
            --gradient-primary: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            --gradient-secondary: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --gradient-light: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            font-family: 'Montserrat', sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background: var(--gradient-primary);
            color: white;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.8rem 1rem;
            margin: 0.2rem 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .main-content {
            padding: 2rem;
            background: var(--light-bg);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-card.orders .icon {
            background: var(--gradient-primary);
            color: white;
        }

        .stat-card.revenue .icon {
            background: var(--gradient-secondary);
            color: white;
        }

        .stat-card.pending .icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .stat-card.completed .icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .table thead th {
            background: var(--gradient-light);
            border: none;
            padding: 1rem;
            font-weight: 600;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: var(--gradient-secondary);
            transform: translateY(-2px);
        }

        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .welcome-section h2 {
            color: var(--primary-color);
            font-weight: 600;
        }

        .alert {
            border-radius: 10px;
            border: none;
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
                            <a href="dashboard.php" class="nav-link active">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="services.php" class="nav-link">
                                <i class="bi bi-gear me-2"></i>Kelola Layanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_news.php" class="nav-link">
                                <i class="bi bi-newspaper me-2"></i>Kelola Berita
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_about.php" class="nav-link">
                                <i class="bi bi-info-circle me-2"></i>Kelola About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="kelola_profil.php" class="nav-link">
                                <i class="bi bi-person me-2"></i>Kelola Profil
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
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <h2>Selamat Datang, Admin!</h2>
                    <p class="text-muted mb-0">Berikut adalah ringkasan aktivitas layanan fotocopy hari ini.</p>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card orders">
                            <div class="icon">
                                <i class="bi bi-cart"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['total_orders']; ?></h3>
                            <p class="text-muted mb-0">Total Pesanan</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card revenue">
                            <div class="icon">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <h3 class="mb-1">Rp <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></h3>
                            <p class="text-muted mb-0">Total Pendapatan</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card pending">
                            <div class="icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['pending_orders']; ?></h3>
                            <p class="text-muted mb-0">Pesanan Pending</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card completed">
                            <div class="icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['completed_orders']; ?></h3>
                            <p class="text-muted mb-0">Pesanan Selesai</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Pesanan Terbaru</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pelanggan</th>
                                        <th>Layanan</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($order['nama_pemesan']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['kontak']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($order['nama_layanan']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['nama_opsi']); ?></small>
                                            </td>
                                            <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                            <td>
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
                                                <span class="badge bg-<?php echo $status_class[$order['status']]; ?>">
                                                    <?php echo $status_text[$order['status']]; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['tanggal_pesan'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit_order.php?id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-primary btn-icon">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger btn-icon"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteOrderModal<?php echo $order['id']; ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Order Modals -->
    <?php foreach ($recent_orders as $order): ?>
    <div class="modal fade" id="deleteOrderModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus pesanan #<?php echo $order['id']; ?>?</p>
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Tindakan ini tidak dapat dibatalkan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="" method="POST" class="d-inline">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <button type="submit" name="delete_order" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>