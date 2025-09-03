<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tampilkan data POST di halaman untuk debugging
    // echo "<pre style='background:#eee;padding:10px;'>POST DATA: " . print_r($_POST, true) . "</pre>";
}
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = $error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid CSRF token");
        }

        if (isset($_POST['add_service'])) {
            $stmt = $pdo->prepare("INSERT INTO services (nama_layanan, deskripsi, harga_dasar) VALUES (?, ?, ?)");
            $result = $stmt->execute([
                $_POST['nama_layanan'], 
                $_POST['deskripsi'] ?? '', 
                $_POST['harga_dasar']
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = "Layanan berhasil ditambahkan!";
            } else {
                throw new Exception("Gagal menambahkan layanan");
            }
            
            header('Location: services.php');
            exit;
        }

        if (isset($_POST['edit_service'])) {
            // Debug log untuk melihat data yang akan diupdate
            error_log('AKAN UPDATE: ' . print_r([
                'nama_layanan' => $_POST['nama_layanan'],
                'deskripsi' => $_POST['deskripsi'] ?? '',
                'harga_dasar' => $_POST['harga_dasar'],
                'service_id' => $_POST['service_id']
            ], true));

            // Pastikan semua field required ada
            if (empty($_POST['nama_layanan']) || empty($_POST['harga_dasar']) || empty($_POST['service_id'])) {
                throw new Exception("Data tidak lengkap untuk update layanan");
            }

            $stmt = $pdo->prepare("UPDATE services SET nama_layanan = ?, deskripsi = ?, harga_dasar = ? WHERE id = ?");
            $result = $stmt->execute([
                trim($_POST['nama_layanan']),
                trim($_POST['deskripsi'] ?? ''),
                (float)$_POST['harga_dasar'],
                (int)$_POST['service_id']
            ]);
            
            $rowCount = $stmt->rowCount();
            error_log('UPDATE ROW COUNT: ' . $rowCount);
            
            if ($result && $rowCount > 0) {
                $_SESSION['success_message'] = "Layanan berhasil diperbarui!";
            } else if ($result && $rowCount === 0) {
                $_SESSION['info_message'] = "Tidak ada perubahan data atau layanan tidak ditemukan";
            } else {
                throw new Exception("Gagal memperbarui layanan");
            }
            
            header('Location: services.php');
            exit;
        }

        if (isset($_POST['delete_service'])) {
            // Check if service has any orders
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE service_id = ?");
            $stmt->execute([$_POST['service_id']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Tidak dapat menghapus layanan yang memiliki pesanan!");
            }
            
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $result = $stmt->execute([$_POST['service_id']]);
            
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Layanan berhasil dihapus!";
            } else {
                throw new Exception("Gagal menghapus layanan atau layanan tidak ditemukan");
            }
            
            header('Location: services.php');
            exit;
        }

        if (isset($_POST['add_option'])) {
            $stmt = $pdo->prepare("INSERT INTO service_options (service_id, nama_opsi, harga_tambahan) VALUES (?, ?, ?)");
            $result = $stmt->execute([
                $_POST['service_id'], 
                $_POST['nama_opsi'], 
                $_POST['harga_tambahan'] ?? 0
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = "Opsi layanan berhasil ditambahkan!";
            } else {
                throw new Exception("Gagal menambahkan opsi layanan");
            }
            
            header('Location: services.php');
            exit;
        }

        if (isset($_POST['edit_option'])) {
            $stmt = $pdo->prepare("UPDATE service_options SET nama_opsi = ?, harga_tambahan = ? WHERE id = ?");
            $result = $stmt->execute([
                $_POST['nama_opsi'], 
                $_POST['harga_tambahan'] ?? 0, 
                $_POST['option_id']
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Opsi layanan berhasil diperbarui!";
            } else if ($result && $stmt->rowCount() === 0) {
                $_SESSION['info_message'] = "Tidak ada perubahan data atau opsi tidak ditemukan";
            } else {
                throw new Exception("Gagal memperbarui opsi layanan");
            }
            
            header('Location: services.php');
            exit;
        }

        if (isset($_POST['delete_option'])) {
            // Check if option is used in any orders
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE service_option_id = ?");
            $stmt->execute([$_POST['option_id']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Tidak dapat menghapus opsi yang sudah digunakan dalam pesanan!");
            }
            
            $stmt = $pdo->prepare("DELETE FROM service_options WHERE id = ?");
            $result = $stmt->execute([$_POST['option_id']]);
            
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Opsi layanan berhasil dihapus!";
            } else {
                throw new Exception("Gagal menghapus opsi layanan atau opsi tidak ditemukan");
            }
            
            header('Location: services.php');
            exit;
        }

    } catch (Exception $e) {
        error_log("Error processing form: " . $e->getMessage());
        $error = $e->getMessage();
    }
}

// Handle success/info messages from session
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['info_message'])) {
    $info = $_SESSION['info_message'];
    unset($_SESSION['info_message']);
}

// Fetch all services with their options
try {
    $stmt = $pdo->query("
        SELECT s.*, 
               COUNT(so.id) as option_count,
               COUNT(o.id) as order_count
        FROM services s
        LEFT JOIN service_options so ON s.id = so.service_id
        LEFT JOIN orders o ON s.id = o.service_id
        GROUP BY s.id
        ORDER BY s.nama_layanan
    ");
    $services = $stmt->fetchAll();
    
    // Fetch all service options
    $stmt = $pdo->query("
        SELECT so.*, s.nama_layanan,
               COUNT(o.id) as order_count
        FROM service_options so
        JOIN services s ON so.service_id = s.id
        LEFT JOIN orders o ON so.id = o.service_option_id
        GROUP BY so.id
        ORDER BY s.nama_layanan, so.nama_opsi
    ");
    $service_options = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching services: " . $e->getMessage());
    $error = "Terjadi kesalahan saat mengambil data layanan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Layanan - Admin CepatCopy</title>
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
        }

        .navbar {
            background: var(--gradient-primary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3);
        }

        .btn-danger {
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
        }

        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .table thead th {
            background: var(--gradient-light);
            border: none;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background: var(--gradient-light);
            border-bottom: 1px solid #e2e8f0;
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .price-badge {
            background: var(--gradient-secondary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .option-count {
            background: #f1f5f9;
            color: var(--text-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-printer me-2"></i>CepatCopy Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
                <a class="nav-link active" href="services.php">
                    <i class="bi bi-gear me-1"></i>Layanan
                </a>
                <a class="nav-link" href="kelola_profil.php">
                    <i class="bi bi-person me-1"></i>Profil
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold text-primary">
                    <i class="bi bi-gear me-2"></i>Kelola Layanan
                </h2>
                <p class="text-muted">Kelola layanan dan opsi yang tersedia untuk pelanggan</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Layanan
                </button>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($info) && $info): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i><?php echo htmlspecialchars($info); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Services Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>Daftar Layanan
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($services)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada layanan yang ditambahkan</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Layanan</th>
                                    <th>Deskripsi</th>
                                    <th>Harga Dasar</th>
                                    <th>Jumlah Opsi</th>
                                    <th>Pesanan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($service['nama_layanan']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($service['deskripsi'] ?: '-'); ?>
                                        </td>
                                        <td>
                                            <span class="price-badge">
                                                Rp <?php echo number_format($service['harga_dasar'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="option-count">
                                                <?php echo $service['option_count']; ?> opsi
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $service['order_count']; ?> pesanan
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['nama_layanan'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($service['deskripsi'] ?? '', ENT_QUOTES); ?>', <?php echo $service['harga_dasar']; ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($service['order_count'] == 0): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['nama_layanan'], ENT_QUOTES); ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Tidak dapat dihapus karena masih ada pesanan">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Service Options Section -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-options me-2"></i>Opsi Layanan
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOptionModal">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Opsi
                        </button>
                    </div>
                </div>

                <?php if (empty($service_options)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-options display-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada opsi layanan yang ditambahkan</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Layanan</th>
                                    <th>Nama Opsi</th>
                                    <th>Harga Tambahan</th>
                                    <th>Pesanan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($service_options as $option): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($option['nama_layanan']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($option['nama_opsi']); ?></td>
                                        <td>
                                            <span class="price-badge">
                                                Rp <?php echo number_format($option['harga_tambahan'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $option['order_count']; ?> pesanan
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editOption(<?php echo $option['id']; ?>, <?php echo $option['service_id']; ?>, '<?php echo htmlspecialchars($option['nama_opsi'], ENT_QUOTES); ?>', <?php echo $option['harga_tambahan']; ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($option['order_count'] == 0): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteOption(<?php echo $option['id']; ?>, '<?php echo htmlspecialchars($option['nama_opsi'], ENT_QUOTES); ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Tidak dapat dihapus karena masih ada pesanan">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Layanan Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addServiceForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="add_service" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_layanan" class="form-label">Nama Layanan *</label>
                            <input type="text" class="form-control" id="nama_layanan" name="nama_layanan" 
                                   maxlength="100" required>
                            <div class="form-text">Maksimal 100 karakter</div>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="harga_dasar" class="form-label">Harga Dasar *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga_dasar" name="harga_dasar" 
                                       min="0" step="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Layanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Option Modal -->
    <div class="modal fade" id="addOptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Opsi Layanan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addOptionForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Pilih Layanan *</label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="">Pilih layanan...</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>">
                                        <?php echo htmlspecialchars($service['nama_layanan']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama_opsi" class="form-label">Nama Opsi *</label>
                            <input type="text" class="form-control" id="nama_opsi" name="nama_opsi" 
                                   maxlength="100" required>
                            <div class="form-text">Maksimal 100 karakter</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="harga_tambahan" class="form-label">Harga Tambahan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga_tambahan" name="harga_tambahan" 
                                       min="0" step="100" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_option" class="btn btn-success">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Opsi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i>Edit Layanan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editServiceForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="service_id" id="edit_service_id">
                        <div class="mb-3">
                            <label for="edit_nama_layanan" class="form-label">Nama Layanan *</label>
                            <input type="text" class="form-control" id="edit_nama_layanan" name="nama_layanan" maxlength="100" required>
                            <div class="form-text">Maksimal 100 karakter</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_harga_dasar" class="form-label">Harga Dasar *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="edit_harga_dasar" name="harga_dasar" min="0" step="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_service" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Option Modal -->
    <div class="modal fade" id="editOptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i>Edit Opsi Layanan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editOptionForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="option_id" id="edit_option_id">
                        <input type="hidden" name="service_id" id="edit_option_service_id">
                        <div class="mb-3">
                            <label for="edit_option_nama_opsi" class="form-label">Nama Opsi *</label>
                            <input type="text" class="form-control" id="edit_option_nama_opsi" name="nama_opsi" maxlength="100" required>
                            <div class="form-text">Maksimal 100 karakter</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_option_harga_tambahan" class="form-label">Harga Tambahan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="edit_option_harga_tambahan" name="harga_tambahan" min="0" step="100">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_option" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage"></p>
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" id="deleteForm" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit service function
        function editService(id, nama, deskripsi, harga) {
            document.getElementById('edit_service_id').value = id;
            document.getElementById('edit_nama_layanan').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_harga_dasar').value = harga;
            new bootstrap.Modal(document.getElementById('editServiceModal')).show();
        }
        // Edit option function
        function editOption(id, serviceId, nama, harga) {
            document.getElementById('edit_option_id').value = id;
            document.getElementById('edit_option_service_id').value = serviceId;
            document.getElementById('edit_option_nama_opsi').value = nama;
            document.getElementById('edit_option_harga_tambahan').value = harga;
            new bootstrap.Modal(document.getElementById('editOptionModal')).show();
        }
        // Delete service function
        function deleteService(id, nama) {
            document.getElementById('deleteMessage').innerHTML =
                `Apakah Anda yakin ingin menghapus layanan <strong>"${nama}"</strong>?`;
            const form = document.getElementById('deleteForm');
            form.innerHTML = `
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="service_id" value="${id}">
                <button type="submit" name="delete_service" class="btn btn-danger">
                    <i class="bi bi-trash me-2"></i>Hapus Layanan
                </button>
            `;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        // Delete option function
        function deleteOption(id, nama) {
            document.getElementById('deleteMessage').innerHTML =
                `Apakah Anda yakin ingin menghapus opsi <strong>"${nama}"</strong>?`;
            const form = document.getElementById('deleteForm');
            form.innerHTML = `
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="option_id" value="${id}">
                <button type="submit" name="delete_option" class="btn btn-danger">
                    <i class="bi bi-trash me-2"></i>Hapus Opsi
                </button>
            `;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        // Auto-hide alerts after 10 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 10000);
    </script>
</body>
</html>