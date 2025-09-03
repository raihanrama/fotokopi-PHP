<?php require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch available services
$stmt = $pdo->query("SELECT * FROM services ORDER BY nama_layanan");
$services = $stmt->fetchAll();

// Fetch service options
$stmt = $pdo->query("SELECT so.*, s.nama_layanan 
                     FROM service_options so 
                     JOIN services s ON so.service_id = s.id 
                     ORDER BY s.nama_layanan, so.nama_opsi");
$service_options = $stmt->fetchAll();

// Get selected service ID from URL parameter
$selected_service_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">Form Pemesanan</h2>
                        <a href="katalog.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Kembali ke Katalog
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($selected_service_id): ?>
                        <?php
                        // Get selected service details
                        $selected_service = null;
                        foreach ($services as $service) {
                            if ($service['id'] == $selected_service_id) {
                                $selected_service = $service;
                                break;
                            }
                        }
                        ?>
                        <?php if ($selected_service): ?>
                            <div class="alert alert-success mb-4" id="package-selected-alert">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-check-circle-fill me-3 fs-4 mt-1 text-success"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-2">✅ Paket Berhasil Dipilih!</h6>
                                        <h5 class="mb-2"><?php echo htmlspecialchars($selected_service['nama_layanan']); ?></h5>
                                        <?php if (!empty($selected_service['deskripsi'])): ?>
                                            <p class="mb-2 text-muted"><?php echo nl2br(htmlspecialchars($selected_service['deskripsi'])); ?></p>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Harga Dasar: <strong>Rp <?php echo number_format($selected_service['harga_dasar'], 0, ',', '.'); ?></strong></small>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.location.href='katalog.php'">
                                                <i class="bi bi-arrow-left me-1"></i>Ganti Paket
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <form method="POST" action="order_process.php" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="nama_pemesan" class="form-label">
                                Nama Lengkap
                                <i class="bi bi-question-circle-fill text-muted ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Masukkan nama lengkap sesuai KTP"></i>
                            </label>
                            <input type="text" class="form-control" id="nama_pemesan" name="nama_pemesan" required>
                        </div>

                        <div class="mb-4">
                            <label for="kontak" class="form-label">
                                Nomor Telepon
                                <i class="bi bi-question-circle-fill text-muted ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Nomor yang dapat dihubungi untuk konfirmasi pesanan"></i>
                            </label>
                            <input type="tel" class="form-control" id="kontak" name="kontak" required>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">
                                Email
                                <i class="bi bi-question-circle-fill text-muted ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Email untuk menerima konfirmasi dan invoice (opsional)"></i>
                            </label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>

                        <div class="mb-4">
                            <label for="service_id" class="form-label">Layanan</label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="">Pilih Layanan</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>" 
                                            data-harga="<?php echo $service['harga_dasar']; ?>"
                                            <?php echo ($selected_service_id == $service['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($service['nama_layanan']); ?> 
                                        (Rp <?php echo number_format($service['harga_dasar'], 0, ',', '.'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="service_option_id" class="form-label">Opsi Layanan</label>
                            <select class="form-select" id="service_option_id" name="service_option_id" required>
                                <option value="">Pilih Opsi Layanan</option>
                                <?php foreach ($service_options as $option): ?>
                                    <option value="<?php echo $option['id']; ?>" 
                                            data-service="<?php echo $option['service_id']; ?>"
                                            data-harga="<?php echo $option['harga_tambahan']; ?>">
                                        <?php echo htmlspecialchars($option['nama_opsi']); ?> 
                                        (Rp <?php echo number_format($option['harga_tambahan'], 0, ',', '.'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="jumlah_halaman" class="form-label">Jumlah Halaman</label>
                            <input type="number" class="form-control" id="jumlah_halaman" name="jumlah_halaman" min="1" value="1" required>
                        </div>

                        <div class="mb-4">
                            <label for="jumlah_copy" class="form-label">Jumlah Copy</label>
                            <input type="number" class="form-control" id="jumlah_copy" name="jumlah_copy" min="1" value="1" required>
                        </div>

                        <div class="mb-4">
                            <label for="metode_pengambilan" class="form-label">Metode Pengambilan</label>
                            <select class="form-select" id="metode_pengambilan" name="metode_pengambilan" required>
                                <option value="">Pilih Metode Pengambilan</option>
                                <option value="ambil">Ambil Sendiri</option>
                                <option value="ojek_online">Ojek Online</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="document" class="form-label">Upload Dokumen</label>
                            <input type="file" class="form-control" id="document" name="document" required>
                            <div class="form-text">Format yang didukung: PDF, DOC, DOCX, JPG, JPEG, PNG (Maks. 5MB)</div>
                        </div>

                        <div class="mb-4">
                            <label for="catatan" class="form-label">Catatan Tambahan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="alert alert-info">
                                <h5 class="mb-3">Estimasi Total Harga:</h5>
                                <div id="harga_detail">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Harga Dasar:</small>
                                            <div id="harga_dasar">Rp 0</div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Harga Opsi:</small>
                                            <div id="harga_opsi">Rp 0</div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Jumlah Halaman:</small>
                                            <div id="detail_halaman">0 halaman</div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Jumlah Copy:</small>
                                            <div id="detail_copy">0 copy</div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Total Harga:</strong>
                                        <strong class="fs-5 text-primary" id="total_harga">Rp 0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="bi bi-send-fill me-2"></i>Kirim Pesanan
                            </button>
                            <div class="progress mt-2" id="submit-progress" style="display: none;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service_id');
    const optionSelect = document.getElementById('service_option_id');
    const jumlahHalaman = document.getElementById('jumlah_halaman');
    const jumlahCopy = document.getElementById('jumlah_copy');
    const totalHargaElement = document.getElementById('total_harga');
    const namaPemesan = document.getElementById('nama_pemesan');
    const kontak = document.getElementById('kontak');
    const email = document.getElementById('email');
    const metodePengambilan = document.getElementById('metode_pengambilan');
    const catatan = document.getElementById('catatan');

    // Auto-fill form if service is pre-selected from catalog
    const selectedServiceId = <?php echo $selected_service_id ?: 'null'; ?>;
    
    // Hide package selected alert after 5 seconds
    const packageAlert = document.getElementById('package-selected-alert');
    if (packageAlert) {
        setTimeout(() => {
            packageAlert.style.transition = 'opacity 0.5s ease-out';
            packageAlert.style.opacity = '0';
            setTimeout(() => {
                packageAlert.style.display = 'none';
            }, 500);
        }, 5000);
    }
    
    // Load saved form data from localStorage
    function loadSavedFormData() {
        const savedData = localStorage.getItem('orderFormData');
        if (savedData) {
            const data = JSON.parse(savedData);
            namaPemesan.value = data.nama_pemesan || '';
            kontak.value = data.kontak || '';
            email.value = data.email || '';
            metodePengambilan.value = data.metode_pengambilan || '';
            catatan.value = data.catatan || '';
            
            // Show notification if data was loaded
            if (data.nama_pemesan || data.kontak) {
                showToast('Data form sebelumnya berhasil dimuat', 'info');
            }
        }
    }

    // Save form data to localStorage
    function saveFormData() {
        const formData = {
            nama_pemesan: namaPemesan.value,
            kontak: kontak.value,
            email: email.value,
            metode_pengambilan: metodePengambilan.value,
            catatan: catatan.value
        };
        localStorage.setItem('orderFormData', JSON.stringify(formData));
        
        // Show save notification
        showToast('Data form berhasil disimpan', 'success');
    }
    
    // Show toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `position-fixed top-0 end-0 p-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <i class="bi bi-${type === 'success' ? 'check-circle-fill text-success' : 'info-circle-fill text-primary'} me-2"></i>
                    <strong class="me-auto">Notifikasi</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Save frequently used service
    function saveFrequentService(serviceId) {
        const frequentServices = JSON.parse(localStorage.getItem('frequentServices') || '[]');
        const existingIndex = frequentServices.indexOf(serviceId);
        
        if (existingIndex > -1) {
            frequentServices.splice(existingIndex, 1);
        }
        frequentServices.unshift(serviceId);
        
        // Keep only last 5 services
        if (frequentServices.length > 5) {
            frequentServices.pop();
        }
        
        localStorage.setItem('frequentServices', JSON.stringify(frequentServices));
    }

    // Load saved data on page load
    loadSavedFormData();
    
    if (selectedServiceId) {
        // Show loading indicator
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'alert alert-info mb-4';
        loadingDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>Mengisi form otomatis...</span>
            </div>
        `;
        document.querySelector('.card-body').insertBefore(loadingDiv, document.querySelector('form'));
        
        // Trigger change event to filter options and update total
        serviceSelect.dispatchEvent(new Event('change'));
        
        // Auto-select first available option for the selected service
        setTimeout(() => {
            const availableOptions = Array.from(optionSelect.options).filter(option => 
                option.dataset.service == selectedServiceId && option.value !== ''
            );
            if (availableOptions.length > 0) {
                optionSelect.value = availableOptions[0].value;
                optionSelect.dispatchEvent(new Event('change'));
            }
            
            // Remove loading indicator
            loadingDiv.remove();
            
            // Show success notification
            showToast('Paket berhasil dipilih! Form telah diisi otomatis.', 'success');
        }, 1000);
    }

    function updateTotalHarga() {
        const selectedService = serviceSelect.options[serviceSelect.selectedIndex];
        const selectedOption = optionSelect.options[optionSelect.selectedIndex];
        const halaman = parseInt(jumlahHalaman.value) || 0;
        const copy = parseInt(jumlahCopy.value) || 0;

        const hargaDasarElement = document.getElementById('harga_dasar');
        const hargaOpsiElement = document.getElementById('harga_opsi');
        const detailHalamanElement = document.getElementById('detail_halaman');
        const detailCopyElement = document.getElementById('detail_copy');

        if (selectedService && selectedOption) {
            const hargaDasar = parseFloat(selectedService.dataset.harga);
            const hargaTambahan = parseFloat(selectedOption.dataset.harga);
            const total = (hargaDasar + hargaTambahan) * halaman * copy;
            
            // Update detail harga
            hargaDasarElement.textContent = 'Rp ' + hargaDasar.toLocaleString('id-ID');
            hargaOpsiElement.textContent = 'Rp ' + hargaTambahan.toLocaleString('id-ID');
            detailHalamanElement.textContent = halaman + ' halaman';
            detailCopyElement.textContent = copy + ' copy';
            totalHargaElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
        } else {
            // Reset all price elements
            hargaDasarElement.textContent = 'Rp 0';
            hargaOpsiElement.textContent = 'Rp 0';
            detailHalamanElement.textContent = '0 halaman';
            detailCopyElement.textContent = '0 copy';
            totalHargaElement.textContent = 'Rp 0';
        }
    }

    serviceSelect.addEventListener('change', function() {
        // Filter options based on selected service
        const serviceId = this.value;
        Array.from(optionSelect.options).forEach(option => {
            if (option.value === '') return; // Skip the default option
            option.style.display = option.dataset.service === serviceId ? '' : 'none';
        });
        optionSelect.value = ''; // Reset option selection
        updateTotalHarga();
        
        // Save frequently used service
        if (serviceId) {
            saveFrequentService(serviceId);
            showToast('Layanan berhasil disimpan sebagai favorit', 'success');
        }
    });

    optionSelect.addEventListener('change', updateTotalHarga);
    jumlahHalaman.addEventListener('input', updateTotalHarga);
    jumlahCopy.addEventListener('input', updateTotalHarga);
    
    // Save form data when user types
    namaPemesan.addEventListener('input', saveFormData);
    kontak.addEventListener('input', saveFormData);
    email.addEventListener('input', saveFormData);
    metodePengambilan.addEventListener('change', saveFormData);
    catatan.addEventListener('input', saveFormData);
    
    // Add event listener for change package button
    document.querySelectorAll('button[onclick*="katalog.php"]').forEach(button => {
        button.addEventListener('click', function() {
            showToast('Mengalihkan ke katalog untuk memilih paket lain...', 'info');
        });
    });
    
    // Clear saved data when form is submitted successfully
    document.querySelector('form').addEventListener('submit', function() {
        // Show progress bar
        const submitBtn = document.getElementById('submit-btn');
        const progressBar = document.getElementById('submit-progress');
        const progressBarInner = progressBar.querySelector('.progress-bar');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Memproses...';
        progressBar.style.display = 'block';
        
        // Animate progress bar
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBarInner.style.width = progress + '%';
        }, 200);
        
        // Clear saved data after successful submission
        setTimeout(() => {
            localStorage.removeItem('orderFormData');
            clearInterval(progressInterval);
            progressBarInner.style.width = '100%';
        }, 1000);
    });
    
    // Initial calculation
    updateTotalHarga();
    
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 