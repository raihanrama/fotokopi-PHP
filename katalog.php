<?php
require_once 'includes/db.php'; // koneksi ke database
require_once 'includes/header.php'; // header HTML (jika ada)

// Ambil semua layanan dari database
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Gagal memuat layanan: " . $e->getMessage() . "</div>";
    $services = [];
}
?>

<div class="container my-5">
    <h2 class="mb-4 text-center">Katalog Layanan</h2>
    
    <!-- Frequently Used Services Section -->
    <div id="frequent-services" class="mb-4" style="display: none;">
        <h5 class="mb-3">
            <i class="bi bi-star-fill text-warning me-2"></i>
            Paket yang Sering Dipilih
        </h5>
        <div class="row g-3" id="frequent-services-list">
            <!-- Will be populated by JavaScript -->
        </div>
        <hr class="my-4">
    </div>
    
    <div class="row g-4">
        <?php if (count($services) > 0): ?>
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($service['nama_layanan']); ?></h5>
                            <p class="card-text text-muted">
                                <?php echo nl2br(htmlspecialchars($service['deskripsi'] ?? 'Tidak ada deskripsi.')); ?>
                            </p>
                            <h6 class="mt-auto">Mulai dari <strong>Rp <?php echo number_format($service['harga_dasar'], 0, ',', '.'); ?></strong></h6>
                            <a href="order.php?id=<?php echo $service['id']; ?>" class="btn btn-primary mt-3">
                                <i class="bi bi-cart-plus"></i> Pesan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">Belum ada layanan tersedia.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load and display frequently used services
    const frequentServices = JSON.parse(localStorage.getItem('frequentServices') || '[]');
    const frequentServicesSection = document.getElementById('frequent-services');
    const frequentServicesList = document.getElementById('frequent-services-list');
    
    if (frequentServices.length > 0) {
        // Get all service cards
        const serviceCards = document.querySelectorAll('.col-md-6.col-lg-4');
        
        // Filter and display frequent services
        frequentServices.forEach(serviceId => {
            serviceCards.forEach(card => {
                const orderLink = card.querySelector('a[href*="order.php?id="]');
                if (orderLink && orderLink.href.includes('id=' + serviceId)) {
                    const clone = card.cloneNode(true);
                    clone.classList.remove('col-md-6', 'col-lg-4');
                    clone.classList.add('col-md-4', 'col-lg-3');
                    
                    // Add "Frequently Used" badge
                    const cardBody = clone.querySelector('.card-body');
                    const badge = document.createElement('div');
                    badge.className = 'position-absolute top-0 end-0 m-2';
                    badge.innerHTML = '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Sering</span>';
                    clone.querySelector('.card').style.position = 'relative';
                    clone.querySelector('.card').appendChild(badge);
                    
                    frequentServicesList.appendChild(clone);
                }
            });
        });
        
        // Show frequent services section if there are any
        if (frequentServicesList.children.length > 0) {
            frequentServicesSection.style.display = 'block';
        }
    }
    
    // Add click event to all order buttons
    document.querySelectorAll('a[href*="order.php?id="]').forEach(link => {
        link.addEventListener('click', function(e) {
            // Show loading notification
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-header">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                        <strong class="me-auto">Memproses...</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        Mengalihkan ke form pemesanan...
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Remove toast after 2 seconds
            setTimeout(() => {
                toast.remove();
            }, 2000);
        });
    });
});
</script>
