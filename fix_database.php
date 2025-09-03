<?php
// File untuk memperbaiki database yang sudah ada
// Jalankan file ini sekali saja untuk memperbaiki struktur database

require_once 'includes/db.php';

echo "<h2>Memperbaiki Database...</h2>";

try {
    // 1. Perbaiki ukuran field nama_layanan
    echo "<p>1. Memperbaiki field nama_layanan...</p>";
    $pdo->exec("ALTER TABLE services MODIFY COLUMN nama_layanan VARCHAR(100) NOT NULL");
    echo "<p style='color: green;'>✓ Field nama_layanan berhasil diperbaiki</p>";
    
    // 2. Perbaiki ukuran field nama_opsi
    echo "<p>2. Memperbaiki field nama_opsi...</p>";
    $pdo->exec("ALTER TABLE service_options MODIFY COLUMN nama_opsi VARCHAR(100) NOT NULL");
    echo "<p style='color: green;'>✓ Field nama_opsi berhasil diperbaiki</p>";
    
    // 3. Hapus foreign key constraints lama
    echo "<p>3. Menghapus foreign key constraints lama...</p>";
    try {
        $pdo->exec("ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_2");
        echo "<p style='color: green;'>✓ Foreign key service_id berhasil dihapus</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Foreign key service_id tidak ditemukan atau sudah dihapus</p>";
    }
    
    try {
        $pdo->exec("ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_3");
        echo "<p style='color: green;'>✓ Foreign key service_option_id berhasil dihapus</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Foreign key service_option_id tidak ditemukan atau sudah dihapus</p>";
    }
    
    // 4. Tambah foreign key constraints baru dengan CASCADE
    echo "<p>4. Menambahkan foreign key constraints baru...</p>";
    $pdo->exec("ALTER TABLE orders ADD CONSTRAINT orders_ibfk_2 FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE");
    echo "<p style='color: green;'>✓ Foreign key service_id dengan CASCADE berhasil ditambahkan</p>";
    
    $pdo->exec("ALTER TABLE orders ADD CONSTRAINT orders_ibfk_3 FOREIGN KEY (service_option_id) REFERENCES service_options(id) ON DELETE CASCADE");
    echo "<p style='color: green;'>✓ Foreign key service_option_id dengan CASCADE berhasil ditambahkan</p>";
    
    // 5. Tambah field deskripsi jika belum ada
    echo "<p>5. Memeriksa field deskripsi...</p>";
    $stmt = $pdo->query("SHOW COLUMNS FROM services LIKE 'deskripsi'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE services ADD COLUMN deskripsi TEXT AFTER nama_layanan");
        echo "<p style='color: green;'>✓ Field deskripsi berhasil ditambahkan</p>";
    } else {
        echo "<p style='color: green;'>✓ Field deskripsi sudah ada</p>";
    }
    
    echo "<h3 style='color: green;'>🎉 Database berhasil diperbaiki!</h3>";
    echo "<p>Sekarang Anda dapat:</p>";
    echo "<ul>";
    echo "<li>Menambah layanan dengan nama hingga 100 karakter</li>";
    echo "<li>Menambah opsi dengan nama hingga 100 karakter</li>";
    echo "<li>Menghapus layanan/opsi yang memiliki pesanan (CASCADE)</li>";
    echo "<li>Menggunakan field deskripsi untuk layanan</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Silakan cek error log untuk detail lebih lanjut.</p>";
}

echo "<p><a href='admin/services.php'>Kembali ke Admin Panel</a></p>";
?> 