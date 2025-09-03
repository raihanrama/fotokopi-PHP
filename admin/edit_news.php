<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$news = null;

// Get news data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();
    
    if (!$news) {
        header('Location: manage_news.php');
        exit();
    }
} else {
    header('Location: manage_news.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $status = $_POST['status'];
    
    // Validate input
    if (empty($title) || empty($content)) {
        $message = '<div class="alert alert-danger">Judul dan konten harus diisi!</div>';
    } else {
        $image_name = $news['image']; // Keep existing image by default
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = uniqid() . '.' . $file_extension;
                $upload_path = '../uploads/' . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if exists
                    if ($news['image'] && file_exists('../uploads/' . $news['image'])) {
                        unlink('../uploads/' . $news['image']);
                    }
                } else {
                    $message = '<div class="alert alert-danger">Gagal mengupload gambar!</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Tipe file tidak didukung! Gunakan JPG, PNG, atau GIF.</div>';
            }
        }
        
        if (empty($message)) {
            // Update news in database
            $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ?, image = ?, status = ? WHERE id = ?");
            
            if ($stmt->execute([$title, $content, $image_name, $status, $id])) {
                header('Location: manage_news.php');
                exit();
            } else {
                $message = '<div class="alert alert-danger">Gagal mengupdate berita!</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berita - Admin Dashboard</title>
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

        .current-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 10px;
            margin-top: 10px;
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
                        <a class="nav-link active" href="manage_news.php">
                            <i class="fas fa-newspaper me-2"></i> Kelola Berita
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
                    <h2><i class="fas fa-edit me-2"></i>Edit Berita</h2>
                    <a href="manage_news.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>

                <?= $message ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Judul Berita *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= htmlspecialchars($news['title']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Konten Berita *</label>
                                        <textarea class="form-control" id="content" name="content" rows="15" 
                                                  required><?= htmlspecialchars($news['content']) ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Gambar Berita</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                                        
                                        <?php if ($news['image']): ?>
                                            <div class="mt-2">
                                                <label class="form-label">Gambar Saat Ini:</label>
                                                <img src="../uploads/<?= $news['image'] ?>" alt="Current Image" class="current-image">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="published" <?= $news['status'] == 'published' ? 'selected' : '' ?>>Published</option>
                                            <option value="draft" <?= $news['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                                        </select>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Berita
                                        </button>
                                        <a href="manage_news.php" class="btn btn-outline-secondary">
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