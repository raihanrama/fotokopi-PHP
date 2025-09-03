<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id > 0) {
    $stmt = $pdo->prepare("SELECT o.*, c.nama_pemesan, c.kontak, s.nama_layanan, so.nama_opsi 
                          FROM orders o 
                          JOIN customers c ON o.customer_id = c.id 
                          JOIN services s ON o.service_id = s.id 
                          JOIN service_options so ON o.service_option_id = so.id 
                          WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
} else {
    header('Location: order.php');
    exit();
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-success">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="card-title text-success mb-3">Pesanan Berhasil Dibuat!</h2>
                    <p class="card-text mb-4">
                        Terima kasih telah mempercayai layanan kami. Pesanan Anda telah berhasil diterima dan sedang diproses.
                    </p>
                    
                    <?php if (isset($_GET['order_id'])): ?>
                        <div class="alert alert-info mb-4">
                            <strong>Nomor Pesanan:</strong> #<?php echo htmlspecialchars($_GET['order_id']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-clock-fill text-primary me-2"></i>
                                        Status Pesanan
                                    </h6>
                                    <p class="card-text text-success mb-0">
                                        <strong>Menunggu Konfirmasi</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-telephone-fill text-primary me-2"></i>
                                        Kontak Kami
                                    </h6>
                                    <p class="card-text mb-0">
                                        Kami akan menghubungi Anda segera untuk konfirmasi pesanan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="check_order.php" class="btn btn-outline-primary">
                            <i class="bi bi-search me-2"></i>Cek Status Pesanan
                        </a>
                        <a href="katalog.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Pesan Lagi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add success animation
    const successIcon = document.querySelector('.bi-check-circle-fill');
    successIcon.style.animation = 'bounce 1s ease-in-out';
    
    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Show confetti effect
    setTimeout(() => {
        const confetti = document.createElement('div');
        confetti.className = 'position-fixed top-0 start-0 w-100 h-100';
        confetti.style.pointerEvents = 'none';
        confetti.style.zIndex = '9999';
        document.body.appendChild(confetti);
        
        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                const particle = document.createElement('div');
                particle.style.position = 'absolute';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = '-10px';
                particle.style.width = '10px';
                particle.style.height = '10px';
                particle.style.backgroundColor = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57'][Math.floor(Math.random() * 5)];
                particle.style.borderRadius = '50%';
                particle.style.animation = 'fall 3s linear forwards';
                confetti.appendChild(particle);
                
                setTimeout(() => particle.remove(), 3000);
            }, i * 100);
        }
        
        const fallStyle = document.createElement('style');
        fallStyle.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                }
            }
        `;
        document.head.appendChild(fallStyle);
        
        setTimeout(() => confetti.remove(), 4000);
    }, 500);
});
</script>

<?php require_once 'includes/footer.php'; ?> 