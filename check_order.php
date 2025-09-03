<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

$phone = '';
$orders = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    
    if (empty($phone)) {
        $error = 'Nomor telepon harus diisi';
    } else {
        $stmt = $pdo->prepare("SELECT o.*, c.nama_pemesan, s.nama_layanan, so.nama_opsi 
                              FROM orders o 
                              JOIN customers c ON o.customer_id = c.id 
                              JOIN services s ON o.service_id = s.id 
                              JOIN service_options so ON o.service_option_id = so.id 
                              WHERE c.kontak = ? 
                              ORDER BY o.tanggal_pesan DESC");
        $stmt->execute([$phone]);
        $orders = $stmt->fetchAll();
        
        if (empty($orders)) {
            $error = 'Tidak ada pesanan ditemukan untuk nomor telepon ini';
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Cek Status Pesanan</h2>
                    
                    <form method="POST" class="mb-4">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($phone); ?>" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Cek Status</button>
                        </div>
                    </form>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($orders)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Layanan</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['tanggal_pesan'])); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($order['nama_layanan']); ?>
                                                (<?php echo htmlspecialchars($order['nama_opsi']); ?>)
                                            </td>
                                            <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $status = $order['status'];
                                                $class = $statusClass[$status] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $class; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                                </span>
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
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 