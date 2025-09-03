<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

$admins = [];
try {
    $stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY created_at DESC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Gagal memuat data admin.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_admin'])) {
            $username = htmlspecialchars(trim($_POST['username']));
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $password]);

            $success = "Admin berhasil ditambahkan.";
        }

        if (isset($_POST['edit_admin'])) {
            $id = $_POST['admin_id'];
            $username = htmlspecialchars(trim($_POST['username']));

            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $password, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                $stmt->execute([$username, $id]);
            }

            $success = "Admin berhasil diperbarui.";
        }

        if (isset($_POST['delete_admin'])) {
            $id = $_POST['admin_id'];
            if ($_SESSION['admin_id'] == $id) {
                throw new Exception("Tidak bisa menghapus akun Anda sendiri.");
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            $success = "Admin berhasil dihapus.";
        }

        header("Location: kelola_profil.php");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Profil - CepatCopy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #3730a3;
            --text-color: #1e293b;
            --bg-light: #f8fafc;
            --gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            --accent-color: #06b6d4;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 32px rgba(0, 0, 0, 0.12);
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: var(--text-color);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Sidebar tetap sama */
        .sidebar {
            min-height: 100vh;
            background: var(--gradient);
            color: white;
            padding: 2rem 1rem;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            margin-bottom: 0.5rem;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }

        .main-content {
            padding: 2rem;
            background: transparent;
        }

        /* Modern Header */
        .page-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-title {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            font-size: 2.5rem;
            margin: 0;
        }

        .page-subtitle {
            color: #64748b;
            margin-top: 0.5rem;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Modern Add Button */
        .add-admin-btn {
            background: linear-gradient(135deg, var(--accent-color), #0891b2);
            border: none;
            border-radius: 16px;
            padding: 1rem 2rem;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .add-admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.4);
            color: white;
        }

        .add-admin-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .add-admin-btn:hover::before {
            left: 100%;
        }

        /* Modern Admin Cards */
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .admin-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .admin-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        .admin-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-medium);
        }

        .admin-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .admin-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .admin-date {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .admin-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--accent-color), #0891b2);
            color: white;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #0891b2, #0e7490);
            transform: translateY(-2px);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
            color: white;
        }

        /* Modern Modals */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: var(--shadow-medium);
            backdrop-filter: blur(20px);
        }

        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 2rem 2rem 1rem;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .modal-body {
            padding: 1rem 2rem;
        }

        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem 2rem;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Alert Styles */
        .alert {
            border-radius: 16px;
            border: none;
            padding: 1.25rem;
            margin-bottom: 2rem;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: var(--danger-color);
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .admin-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .admin-card:nth-child(1) { animation-delay: 0.1s; }
        .admin-card:nth-child(2) { animation-delay: 0.2s; }
        .admin-card:nth-child(3) { animation-delay: 0.3s; }
        .admin-card:nth-child(4) { animation-delay: 0.4s; }
        .admin-card:nth-child(5) { animation-delay: 0.5s; }
        .admin-card:nth-child(6) { animation-delay: 0.6s; }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .admin-grid {
                grid-template-columns: 1fr;
            }
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
                        <a href="kelola_profil.php" class="nav-link active">
                            <i class="bi bi-gear me-2"></i>Kelola Profil
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
            <!-- Modern Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Kelola Admin</h1>
                        <p class="page-subtitle">Manage admin accounts and permissions</p>
                    </div>
                    <button class="btn add-admin-btn" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Admin
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="stat-number"><?= count($admins) ?></h3>
                    <p class="stat-label">Total Admin</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--success-color), #059669);">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h3 class="stat-number"><?= count($admins) ?></h3>
                    <p class="stat-label">Active Admin</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--accent-color), #0891b2);">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3 class="stat-number"><?= min(count($admins), 5) ?></h3>
                    <p class="stat-label">Recent Activity</p>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Admin Cards Grid -->
            <div class="admin-grid">
                <?php foreach ($admins as $admin): ?>
                    <div class="admin-card">
                        <div class="admin-avatar">
                            <?= strtoupper(substr($admin['username'], 0, 2)) ?>
                        </div>
                        <h5 class="admin-name"><?= htmlspecialchars($admin['username']) ?></h5>
                        <p class="admin-date">
                            <i class="bi bi-calendar3 me-1"></i>
                            Dibuat: <?= $admin['created_at'] ?>
                        </p>
                        <div class="admin-actions">
                            <button class="action-btn btn-edit" data-bs-toggle="modal" data-bs-target="#editAdminModal<?= $admin['id'] ?>">
                                <i class="bi bi-pencil-square"></i>
                                Edit
                            </button>
                            <button class="action-btn btn-delete" data-bs-toggle="modal" data-bs-target="#deleteAdminModal<?= $admin['id'] ?>">
                                <i class="bi bi-trash3"></i>
                                Hapus
                            </button>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editAdminModal<?= $admin['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <form class="modal-content" method="POST">
                                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="bi bi-pencil-square me-2"></i>
                                        Edit Admin
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-person me-1"></i>
                                            Username
                                        </label>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-lock me-1"></i>
                                            Password Baru
                                            <small class="text-muted">(opsional)</small>
                                        </label>
                                        <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak diubah">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button name="edit_admin" class="btn add-admin-btn">
                                        <i class="bi bi-check-lg me-1"></i>
                                        Update Admin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteAdminModal<?= $admin['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <form class="modal-content" method="POST">
                                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title text-danger">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Konfirmasi Hapus
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="bi bi-person-x" style="font-size: 3rem; color: var(--danger-color);"></i>
                                        </div>
                                        <p class="mb-2">Yakin ingin menghapus admin <strong><?= htmlspecialchars($admin['username']) ?></strong>?</p>
                                        <p class="text-danger small">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            Tindakan ini tidak dapat dibatalkan
                                        </p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button name="delete_admin" class="btn btn-delete">
                                        <i class="bi bi-trash3 me-1"></i>
                                        Hapus Admin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>
                    Tambah Admin Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-person me-1"></i>
                        Username
                    </label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-lock me-1"></i>
                        Password
                    </label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button name="add_admin" class="btn add-admin-btn">
                    <i class="bi bi-check-lg me-1"></i>
                    Simpan Admin
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to cards
        const cards = document.querySelectorAll('.admin-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add click animation to buttons
        const buttons = document.querySelectorAll('.action-btn');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });
    });
</script>
</body>
</html>