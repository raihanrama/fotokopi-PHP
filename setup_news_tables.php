<?php
require_once 'includes/db.php';

try {
    // Execute SQL untuk membuat tabel
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `news` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `content` text NOT NULL,
          `image` varchar(255) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `status` enum('published','draft') NOT NULL DEFAULT 'published',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `about_info` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `description` text NOT NULL,
          `vision` text,
          `mission` text,
          `address` text,
          `phone` varchar(50),
          `email` varchar(100),
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // Insert default about info
    $stmt = $pdo->prepare("
        INSERT INTO `about_info` (`title`, `description`, `vision`, `mission`, `address`, `phone`, `email`) VALUES
        (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        'CepatCopy JWP',
        'Kami adalah penyedia layanan fotokopi dan digital printing terpercaya yang telah melayani pelanggan sejak lama. Dengan pengalaman dan dedikasi tinggi, kami berkomitmen memberikan layanan terbaik dengan kualitas tinggi dan harga terjangkau.',
        'Menjadi penyedia layanan fotokopi dan digital printing terdepan yang dipercaya oleh masyarakat.',
        'Memberikan layanan fotokopi dan digital printing berkualitas tinggi dengan harga terjangkau, didukung teknologi modern dan pelayanan yang ramah.',
        'Jl. Contoh No. 123, Kota, Provinsi',
        '+62 812-3456-7890',
        'info@cepatcopyjwp.com'
    ]);
    
    // Insert sample news (optional)
    $stmt = $pdo->prepare("
        INSERT INTO `news` (`title`, `content`, `status`) VALUES
        (?, ?, ?), (?, ?, ?), (?, ?, ?)
    ");
    $stmt->execute([
        'Layanan Fotokopi Berkualitas Tinggi',
        'Kami menyediakan layanan fotokopi dengan kualitas tinggi menggunakan mesin fotokopi terbaru. Hasil fotokopi yang jelas dan rapi untuk memenuhi kebutuhan dokumen Anda.',
        'published',
        'Promo Spesial Bulan Ini',
        'Dapatkan diskon 10% untuk fotokopi dokumen dalam jumlah besar. Promo berlaku hingga akhir bulan ini. Hubungi kami untuk informasi lebih lanjut.',
        'published',
        'Layanan Digital Printing',
        'Selain fotokopi, kami juga menyediakan layanan digital printing untuk banner, spanduk, dan media promosi lainnya. Kualitas terbaik dengan harga terjangkau.',
        'published'
    ]);
    
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f8f9fa; border-radius: 10px;'>";
    echo "<h2 style='color: #28a745;'>✅ Setup Berhasil!</h2>";
    echo "<p>Tabel berita dan about telah berhasil dibuat dengan data default.</p>";
    echo "<h3>Yang telah dibuat:</h3>";
    echo "<ul>";
    echo "<li>✅ Tabel <code>news</code> untuk menyimpan berita</li>";
    echo "<li>✅ Tabel <code>about_info</code> untuk menyimpan informasi about</li>";
    echo "<li>✅ Data default untuk informasi about</li>";
    echo "</ul>";
    echo "<h3>Fitur yang tersedia:</h3>";
    echo "<ul>";
    echo "<li>📝 Admin dapat menambah berita baru di <a href='admin/manage_news.php'>Kelola Berita</a></li>";
    echo "<li>📝 Admin dapat mengedit berita di <a href='admin/manage_news.php'>Kelola Berita</a></li>";
    echo "<li>📝 Admin dapat menghapus berita di <a href='admin/manage_news.php'>Kelola Berita</a></li>";
    echo "<li>📝 Admin dapat mengelola informasi about di <a href='admin/manage_about.php'>Kelola About</a></li>";
    echo "<li>👥 User dapat melihat berita terbaru di <a href='about.php'>Halaman About</a></li>";
    echo "</ul>";
    echo "<p><strong>Catatan:</strong> Pastikan folder <code>uploads/</code> memiliki permission write untuk upload gambar berita.</p>";
    echo "<a href='admin/dashboard.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Ke Dashboard Admin</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f8d7da; border-radius: 10px;'>";
    echo "<h2 style='color: #dc3545;'>❌ Error!</h2>";
    echo "<p>Terjadi kesalahan saat setup database:</p>";
    echo "<pre style='background: #f1f1f1; padding: 10px; border-radius: 5px;'>" . $e->getMessage() . "</pre>";
    echo "</div>";
}
?> 