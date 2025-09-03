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
</style>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Jasa Fotocopy Online Terpercaya</h1>
                <p class="lead mb-4">Solusi cepat dan mudah untuk kebutuhan fotocopy Anda. Kami menyediakan layanan fotocopy berkualitas dengan harga terjangkau.</p>
                <div class="d-flex gap-3">
                    <a href="order.php" class="btn btn-primary">
                        <i class="bi bi-file-earmark-plus me-2"></i>Pesan Sekarang
                    </a>
                    <a href="check_order.php" class="btn btn-outline-primary">
                        <i class="bi bi-search me-2"></i>Cek Status
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/hero-image.svg" alt="Fotocopy Service" class="img-fluid floating">
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-lightning-charge-fill text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="h4 mb-3">Cepat & Efisien</h3>
                    <p class="text-muted mb-0">Proses fotocopy cepat dengan hasil berkualitas tinggi dalam waktu singkat.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-currency-dollar text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="h4 mb-3">Harga Terjangkau</h3>
                    <p class="text-muted mb-0">Dapatkan layanan fotocopy dengan harga yang kompetitif dan transparan.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="h4 mb-3">Terpercaya</h3>
                    <p class="text-muted mb-0">Layanan fotocopy terpercaya dengan jaminan kualitas dan keamanan dokumen.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h2 class="display-5 fw-bold mb-4">Layanan Kami</h2>
            <p class="lead text-muted mb-5">Kami menyediakan berbagai layanan fotocopy untuk memenuhi kebutuhan Anda</p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-text text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="h5 mb-3">Fotocopy Dokumen</h4>
                    <p class="text-muted mb-0">Fotocopy dokumen penting dengan hasil yang jelas dan rapi.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-book text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="h5 mb-3">Fotocopy Buku</h4>
                    <p class="text-muted mb-0">Fotocopy buku dengan kualitas tinggi dan hasil yang rapi.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-pdf text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="h5 mb-3">Print PDF</h4>
                    <p class="text-muted mb-0">Print file PDF dengan hasil yang berkualitas dan cepat.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-scissors text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="h5 mb-3">Jilid & Laminasi</h4>
                    <p class="text-muted mb-0">Layanan jilid dan laminasi untuk hasil yang lebih rapi dan awet.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h2 class="display-5 fw-bold mb-4">Cara Kerja</h2>
            <p class="lead text-muted mb-5">Proses pemesanan yang mudah dan cepat</p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <span class="badge bg-primary rounded-circle p-3">1</span>
                    </div>
                    <h4 class="h5 mb-3">Upload Dokumen</h4>
                    <p class="text-muted mb-0">Upload dokumen yang ingin difotocopy melalui form pemesanan.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <span class="badge bg-primary rounded-circle p-3">2</span>
                    </div>
                    <h4 class="h5 mb-3">Isi Form Pemesanan</h4>
                    <p class="text-muted mb-0">Isi detail pemesanan seperti jumlah copy dan spesifikasi lainnya.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <span class="badge bg-primary rounded-circle p-3">3</span>
                    </div>
                    <h4 class="h5 mb-3">Ambil Hasil</h4>
                    <p class="text-muted mb-0">Ambil hasil fotocopy di lokasi kami atau gunakan layanan pengiriman.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h2 class="display-5 fw-bold mb-4">Hubungi Kami</h2>
            <p class="lead text-muted mb-5">Kami siap membantu Anda dengan layanan fotocopy terbaik</p>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-geo-alt-fill text-primary me-3" style="font-size: 2rem;"></i>
                        <div>
                            <h4 class="h5 mb-1">Lokasi</h4>
                            <p class="text-muted mb-0">Jl. Contoh No. 123, Kota</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-telephone-fill text-primary me-3" style="font-size: 2rem;"></i>
                        <div>
                            <h4 class="h5 mb-1">Telepon</h4>
                            <p class="text-muted mb-0">(021) 1234-5678</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-envelope-fill text-primary me-3" style="font-size: 2rem;"></i>
                        <div>
                            <h4 class="h5 mb-1">Email</h4>
                            <p class="text-muted mb-0">info@cepatcopy.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
