<?php
session_start();
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
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token!";
    } else {
        // Add new admin
        if (isset($_POST['add_admin'])) {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $nama_lengkap = trim($_POST['nama_lengkap']);
            $email = trim($_POST['email']);
            $no_telepon = trim($_POST['no_telepon']);
            $alamat = trim($_POST['alamat']);
            $jabatan = $_POST['jabatan'];
            
            if (empty($username) || strlen($username) > 50) {
                $error = "Username harus diisi dan maksimal 50 karakter!";
            } elseif (strlen($password) < 6) {
                $error = "Password minimal 6 karakter!";
            } elseif (empty($nama_lengkap) || strlen($nama_lengkap) > 100) {
                $error = "Nama lengkap harus diisi dan maksimal 100 karakter!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Email tidak valid!";
            } else {
                try {
                    // Check for duplicate username
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $error = "Username sudah digunakan!";
                    } else {
                        // Check for duplicate email
                        $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = ?");
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            $error = "Email sudah digunakan!";
                        } else {
                            // Start transaction
                            $pdo->beginTransaction();
                            
                            // Insert user
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                            $stmt->execute([$username, $hashed_password]);
                            $user_id = $pdo->lastInsertId();
                            
                            // Insert admin profile
                            $stmt = $pdo->prepare("INSERT INTO admin (user_id, nama_lengkap, email, no_telepon, alamat, jabatan) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$user_id, $nama_lengkap, $email, $no_telepon, $alamat, $jabatan]);
                            
                            $pdo->commit();
                            $success = "Admin berhasil ditambahkan!";
                            
                            // Regenerate CSRF token
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                    }
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    error_log("Error adding admin: " . $e->getMessage());
                    $error = "Terjadi kesalahan saat menambahkan admin.";
                }
            }
        }
        
        // Edit admin
        elseif (isset($_POST['edit_admin'])) {
            $admin_id = intval($_POST['admin_id']);
            $nama_lengkap = trim($_POST['nama_lengkap']);
            $email = trim($_POST['email']);
            $no_telepon = trim($_POST['no_telepon']);
            $alamat = trim($_POST['alamat']);
            $jabatan = $_POST['jabatan'];
            $status = $_POST['status'];
            
            if (empty($nama_lengkap) || strlen($nama_lengkap) > 100) {
                $error = "Nama lengkap harus diisi dan maksimal 100 karakter!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Email tidak valid!";
            } else {
                try {
                    // Check for duplicate email (excluding current admin)
                    $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $admin_id]);
                    if ($stmt->fetch()) {
                        $error = "Email sudah digunakan!";
                    } else {
                        $stmt = $pdo->prepare("UPDATE admin SET nama_lengkap = ?, email = ?, no_telepon = ?, alamat = ?, jabatan = ?, status = ? WHERE id = ?");
                        $stmt->execute([$nama_lengkap, $email, $no_telepon, $alamat, $jabatan, $status, $admin_id]);
                        $success = "Data admin berhasil diperbarui!";
                        
                        // Regenerate CSRF token
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                } catch (PDOException $e) {
                    error_log("Error updating admin: " . $e->getMessage());
                    $error = "Terjadi kesalahan saat memperbarui admin.";
                }
            }
        }
        
        // Change password
        elseif (isset($_POST['change_password'])) {
            $admin_id = intval($_POST['admin_id']);
            $new_password = $_POST['new_password'];
            
            if (strlen($new_password) < 6) {
                $error = "Password minimal 6 karakter!";
            } else {
                try {
                    // Get user_id from admin
                    $stmt = $pdo->prepare("SELECT user_id FROM admin WHERE id = ?");
                    $stmt->execute([$admin_id]);
                    $user_id = $stmt->fetchColumn();
                    
                    if ($user_id) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashed_password, $user_id]);
                        $success = "Password berhasil diubah!";
                        
                        // Regenerate CSRF token
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } else {
                        $error = "Admin tidak ditemukan!";
                    }
                } catch (PDOException $e) {
                    error_log("Error changing password: " . $e->getMessage());
                    $error = "Terjadi kesalahan saat mengubah password.";
                }
            }
        }
        
        // Delete admin
        elseif (isset($_POST['delete_admin'])) {
            $admin_id = intval($_POST['admin_id']);
            
            try {
                // Check if admin has order history
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_history WHERE admin_id = ?");
                $stmt->execute([$admin_id]);
                $history_count = $stmt->fetchColumn();
                
                if ($history_count > 0) {
                    $error = "Tidak dapat menghapus admin karena masih ada riwayat pesanan yang terkait!";
                } else {
                    // Get user_id from admin
                    $stmt = $pdo->prepare("SELECT user_id FROM admin WHERE id = ?");
                    $stmt->execute([$admin_id]);
                    $user_id = $stmt->fetchColumn();
                    
                    if ($user_id) {
                        // Start transaction
                        $pdo->beginTransaction();
                        
                        // Delete admin profile
                        $stmt = $pdo->prepare("DELETE FROM admin WHERE id = ?");
                        $stmt->execute([$admin_id]);
                        
                        // Delete user
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        
                        $pdo->commit();
                        $success = "Admin berhasil dihapus!";
                        
                        // Regenerate CSRF token
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } else {
                        $error = "Admin tidak ditemukan!";
                    }
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Error deleting admin: " . $e->getMessage());
                $error = "Terjadi kesalahan saat menghapus admin.";
            }
        }
    }
}

// Fetch all admins
try {
    $stmt = $pdo->query("
        SELECT a.*, u.username,
               COUNT(oh.id) as history_count
        FROM admin a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN order_history oh ON a.id = oh.admin_id
        GROUP BY a.id
        ORDER BY a.created_at DESC
    ");
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching admins: " . $e->getMessage());
    $error = "Terjadi kesalahan saat mengambil data admin.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin - Admin CepatCopy</title>
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

        .jabatan-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .jabatan-badge.super_admin {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
        }

        .jabatan-badge.admin {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .jabatan-badge.operator {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge.aktif {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .status-badge.nonaktif {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
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
                <a class="nav-link" href="services.php">
                    <i class="bi bi-gear me-1"></i>Layanan
                </a>
                <a class="nav-link active" href="manage_admin.php">
                    <i class="bi bi-people me-1"></i>Admin
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
                    <i class="bi bi-people me-2"></i>Kelola Admin
                </h2>
                <p class="text-muted">Kelola akun admin dan operator sistem</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="bi bi-person-plus me-2"></i>Tambah Admin
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

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Admins Section -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>Daftar Admin
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($admins)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people display-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada admin yang ditambahkan</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Jabatan</th>
                                    <th>Status</th>
                                    <th>Riwayat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($admin['username']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($admin['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                        <td>
                                            <span class="jabatan-badge <?php echo $admin['jabatan']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $admin['jabatan'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $admin['status']; ?>">
                                                <?php echo ucfirst($admin['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $admin['history_count']; ?> riwayat
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editAdmin(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['nama_lengkap']); ?>', '<?php echo htmlspecialchars($admin['email']); ?>', '<?php echo htmlspecialchars($admin['no_telepon']); ?>', '<?php echo htmlspecialchars($admin['alamat']); ?>', '<?php echo $admin['jabatan']; ?>', '<?php echo $admin['status']; ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" 
                                                        onclick="changePassword(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['nama_lengkap']); ?>')">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                                <?php if ($admin['history_count'] == 0): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteAdmin(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['nama_lengkap']); ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Tidak dapat dihapus karena masih ada riwayat">
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

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>Tambah Admin Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addAdminForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           maxlength="50" required>
                                    <div class="form-text">Maksimal 50 karakter</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" required>
                                    <div class="form-text">Minimal 6 karakter</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                           maxlength="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           maxlength="100" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="no_telepon" class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" id="no_telepon" name="no_telepon" 
                                           maxlength="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jabatan" class="form-label">Jabatan *</label>
                                    <select class="form-select" id="jabatan" name="jabatan" required>
                                        <option value="admin">Admin</option>
                                        <option value="operator">Operator</option>
                                        <option value="super_admin">Super Admin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_admin" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>Tambah Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i>Edit Admin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editAdminForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="admin_id" id="edit_admin_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nama_lengkap" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="edit_nama_lengkap" name="nama_lengkap" 
                                           maxlength="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="edit_email" name="email" 
                                           maxlength="100" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_no_telepon" class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" id="edit_no_telepon" name="no_telepon" 
                                           maxlength="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_jabatan" class="form-label">Jabatan *</label>
                                    <select class="form-select" id="edit_jabatan" name="jabatan" required>
                                        <option value="admin">Admin</option>
                                        <option value="operator">Operator</option>
                                        <option value="super_admin">Super Admin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status *</label>
                                    <select class="form-select" id="edit_status" name="status" required>
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_admin" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-key me-2"></i>Ubah Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="changePasswordForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="admin_id" id="change_password_admin_id">
                        
                        <p id="changePasswordMessage"></p>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   minlength="6" required>
                            <div class="form-text">Minimal 6 karakter</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="bi bi-key me-2"></i>Ubah Password
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
        // Form submission handling
        document.addEventListener('DOMContentLoaded', function() {
            const forms = ['addAdminForm', 'editAdminForm', 'changePasswordForm'];
            
            forms.forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        
                        // Add loading state
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Memproses...';
                        form.classList.add('loading');
                        
                        // Re-enable after 3 seconds if still on page
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            form.classList.remove('loading');
                        }, 3000);
                    });
                }
            });
        });

        // Edit admin function
        function editAdmin(id, nama, email, telepon, alamat, jabatan, status) {
            document.getElementById('edit_admin_id').value = id;
            document.getElementById('edit_nama_lengkap').value = nama;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_no_telepon').value = telepon;
            document.getElementById('edit_alamat').value = alamat;
            document.getElementById('edit_jabatan').value = jabatan;
            document.getElementById('edit_status').value = status;
            
            new bootstrap.Modal(document.getElementById('editAdminModal')).show();
        }

        // Change password function
        function changePassword(id, nama) {
            document.getElementById('change_password_admin_id').value = id;
            document.getElementById('changePasswordMessage').innerHTML = 
                `Ubah password untuk admin <strong>"${nama}"</strong>`;
            
            new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
        }

        // Delete admin function
        function deleteAdmin(id, nama) {
            document.getElementById('deleteMessage').innerHTML = 
                `Apakah Anda yakin ingin menghapus admin <strong>"${nama}"</strong>?`;
            
            const form = document.getElementById('deleteForm');
            form.innerHTML = `
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="admin_id" value="${id}">
                <button type="submit" name="delete_admin" class="btn btn-danger">
                    <i class="bi bi-trash me-2"></i>Hapus Admin
                </button>
            `;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html> 