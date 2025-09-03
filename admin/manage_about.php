<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';

// Get current about info
$stmt = $pdo->prepare("SELECT * FROM about_info LIMIT 1");
$stmt->execute();
$about_info = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $vision = trim($_POST['vision']);
    $mission = trim($_POST['mission']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    // Validate input
    if (empty($title) || empty($description)) {
        $message = '<div class="alert alert-danger">Judul dan deskripsi harus diisi!</div>';
    } else {
        if ($about_info) {
            // Update existing about info
            $stmt = $pdo->prepare("UPDATE about_info SET title = ?, description = ?, vision = ?, mission = ?, address = ?, phone = ?, email = ? WHERE id = ?");
            $stmt->execute([$title, $description, $vision, $mission, $address, $phone, $email, $about_info['id']]);
        } else {
            // Insert new about info
            $stmt = $pdo->prepare("INSERT INTO about_info (title, description, vision, mission, address, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $vision, $mission, $address, $phone, $email]);
        }
        
        if ($stmt->rowCount() > 0) {
            $message = '<div class="alert alert-success">Informasi about berhasil diperbarui!</div>';
            // Refresh about info
            $stmt = $pdo->prepare("SELECT * FROM about_info LIMIT 1");
            $stmt->execute();
            $about_info = $stmt->fetch();
        } else {
            $message = '<div class="alert alert-danger">Gagal memperbarui informasi about!</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola About - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #3730a3;
            --accent-color: #6366f1;
            --text-color: #1e293b;
            --light-bg: #f8fafc;
            --gradient-primary: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            --gradient-secondary: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="d-flex flex-column p-3">
                    <h4 class="text-white mb-4">Admin Dashboard</h4>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="services.php">
                            <i class="fas fa-cogs me-2"></i> Kelola Layanan
                        </a>
                        <a class="nav-link" href="manage_admin.php">
                            <i class="fas fa-users me-2"></i> Kelola Admin
                        </a>
                        <a class="nav-link" href="manage_news.php">
                            <i class="fas fa-newspaper me-2"></i> Kelola Berita
                        </a>
                        <a class="nav-link active" href="manage_about.php">
                            <i class="fas fa-info-circle me-2"></i> Kelola About
                        </a>
                        <a class="nav-link" href="edit_order.php">
                            <i class="fas fa-shopping-cart me-2"></i> Kelola Pesanan
                        </a>
                        <a class="nav-link" href="kelola_profil.php">
                            <i class="fas fa-user me-2"></i> Kelola Profil
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-info-circle me-2"></i>Kelola Informasi About</h2>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                    </a>
                </div>

                <?= $message ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Judul Website *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= htmlspecialchars($about_info['title'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Deskripsi *</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" 
                                                  required><?= htmlspecialchars($about_info['description'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="vision" class="form-label">Visi</label>
                                        <textarea class="form-control" id="vision" name="vision" rows="3"><?= htmlspecialchars($about_info['vision'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mission" class="form-label">Misi</label>
                                        <textarea class="form-control" id="mission" name="mission" rows="3"><?= htmlspecialchars($about_info['mission'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($about_info['address'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Nomor Telepon</label>
                                        <input type="text" class="form-control" id="phone" name="phone" 
                                               value="<?= htmlspecialchars($about_info['phone'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($about_info['email'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                                        </button>
                                        <a href="dashboard.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 