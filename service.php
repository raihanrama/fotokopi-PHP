<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT nama_layanan, deskripsi, harga_dasar FROM services ORDER BY nama_layanan");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data layanan: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Layanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Daftar Layanan</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nama Layanan</th>
                <th>Deskripsi</th>
                <th>Harga Dasar</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($services as $service): ?>
            <tr>
                <td><?= htmlspecialchars($service['nama_layanan']) ?></td>
                <td><?= nl2br(htmlspecialchars($service['deskripsi'])) ?></td>
                <td>Rp <?= number_format($service['harga_dasar'], 0, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html> 