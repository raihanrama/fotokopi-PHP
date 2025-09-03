<?php
require_once 'includes/db.php';

echo "<h2>Test Koneksi Database</h2>";

try {
    // Test koneksi PDO
    echo "<p>✅ Koneksi PDO berhasil</p>";
    
    // Test query sederhana
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p>✅ Query test berhasil</p>";
    
    // Test apakah tabel news ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'news'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabel 'news' ditemukan</p>";
        
        // Hitung jumlah berita
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM news");
        $count = $stmt->fetch()['count'];
        echo "<p>📰 Jumlah berita: $count</p>";
    } else {
        echo "<p>❌ Tabel 'news' tidak ditemukan</p>";
    }
    
    // Test apakah tabel about_info ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'about_info'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabel 'about_info' ditemukan</p>";
        
        // Hitung jumlah data about
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM about_info");
        $count = $stmt->fetch()['count'];
        echo "<p>ℹ️ Jumlah data about: $count</p>";
    } else {
        echo "<p>❌ Tabel 'about_info' tidak ditemukan</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<br><a href='setup_news_tables.php'>Setup Database</a> | <a href='admin/dashboard.php'>Dashboard Admin</a> | <a href='about.php'>Halaman About</a>";
?> 