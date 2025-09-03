<?php
require_once 'includes/db.php';

// Get about info
$stmt = $pdo->prepare("SELECT * FROM about_info LIMIT 1");
$stmt->execute();
$about_info = $stmt->fetch();

// Get latest published news
$stmt = $pdo->prepare("SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$latest_news = $stmt->fetchAll();
?>

<?php require_once 'includes/header.php'; ?>

<style>
    /* FIX: memastikan tombol dapat diklik */
    .hero-section .btn {
        position: relative;
        z-index: 10;
        pointer-events: auto;
    }

    /* FIX: gambar tidak menghalangi klik */
    .floating {
        pointer-events: none;
    }
    
    .about-section {
        padding: 80px 0;
        background: #f8f9fa;
    }
    .news-section {
        padding: 80px 0;
    }
    .news-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
    }
    .news-card:hover {
        transform: translateY(-5px);
    }
    .news-image {
        height: 200px;
        object-fit: cover;
        border-radius: 15px 15px 0 0;
    }
    .feature-card {
        text-align: center;
        padding: 30px 20px;
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        background: white;
        height: 100%;
    }
    .feature-icon {
        font-size: 3rem;
        color: #667eea;
        margin-bottom: 20px;
    }
    .section-title {
        position: relative;
        margin-bottom: 50px;
    }
    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Tentang CepatCopy JWP</h1>
                    <p class="lead mb-4">Kami adalah penyedia layanan fotokopi dan digital printing terpercaya yang telah melayani pelanggan dengan dedikasi tinggi sejak lama.</p>
                    <a href="order.php" class="btn btn-light btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i>Pesan Sekarang
                    </a>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/hero-image.svg" alt="About Us" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">Tentang Kami</h2>
                    <p class="lead mb-5"><?= htmlspecialchars($about_info['description']) ?></p>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4>Visi</h4>
                        <p class="text-muted"><?= htmlspecialchars($about_info['vision']) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h4>Misi</h4>
                        <p class="text-muted"><?= htmlspecialchars($about_info['mission']) ?></p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5>Alamat</h5>
                        <p class="text-muted"><?= htmlspecialchars($about_info['address']) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h5>Telepon</h5>
                        <p class="text-muted"><?= htmlspecialchars($about_info['phone']) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5>Email</h5>
                        <p class="text-muted"><?= htmlspecialchars($about_info['email']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest News Section -->
    <section class="news-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">Berita Terbaru</h2>
                    <p class="lead mb-5">Dapatkan informasi terbaru tentang layanan dan promosi kami</p>
                </div>
            </div>

            <div class="row g-4">
                <?php if (empty($latest_news)): ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Belum ada berita terbaru saat ini.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($latest_news as $news): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card news-card">
                                <?php if ($news['image']): ?>
                                    <img src="uploads/<?= $news['image'] ?>" class="card-img-top news-image" alt="<?= htmlspecialchars($news['title']) ?>">
                                <?php else: ?>
                                    <div class="card-img-top news-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-newspaper text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($news['title']) ?></h5>
                                    <p class="card-text text-muted">
                                        <?= substr(strip_tags($news['content']), 0, 150) ?>...
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($news['created_at'])) ?>
                                        </small>
                                                                                 <button class="btn btn-sm btn-outline-primary" 
                                                 onclick="showNewsDetail(<?= $news['id'] ?>, '<?= addslashes(htmlspecialchars($news['title'])) ?>', '<?= addslashes(htmlspecialchars($news['content'])) ?>')">
                                             Baca Selengkapnya
                                         </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- News Detail Modal -->
    <div class="modal fade" id="newsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="newsModalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showNewsDetail(id, title, content) {
            document.getElementById('newsModalTitle').textContent = title;
            document.getElementById('newsModalBody').innerHTML = content.replace(/\n/g, '<br>');
            new bootstrap.Modal(document.getElementById('newsModal')).show();
        }
    </script>

<?php require_once 'includes/footer.php'; ?> 