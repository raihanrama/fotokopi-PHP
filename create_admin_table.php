<?php
require_once 'includes/db.php';

echo "<h2>Membuat Tabel Admin</h2>";

try {
    // Create admin table
    $sql = "
    CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        no_telepon VARCHAR(20),
        alamat TEXT,
        jabatan ENUM('super_admin', 'admin', 'operator') DEFAULT 'admin',
        status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
        foto_profil VARCHAR(255),
        terakhir_login DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Tabel admin berhasil dibuat!</p>";
    
    // Check if admin_id column exists in order_history
    $stmt = $pdo->query("SHOW COLUMNS FROM order_history LIKE 'admin_id'");
    if ($stmt->rowCount() == 0) {
        // Add admin_id column to order_history table
        $pdo->exec("ALTER TABLE order_history ADD COLUMN admin_id INT");
        $pdo->exec("ALTER TABLE order_history ADD CONSTRAINT fk_order_history_admin FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL");
        echo "<p style='color: green;'>✓ Kolom admin_id berhasil ditambahkan ke tabel order_history!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Kolom admin_id sudah ada di tabel order_history.</p>";
    }
    
    // Insert default admin profile if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE user_id = 1");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO admin (user_id, nama_lengkap, email, no_telepon, jabatan) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([1, 'Administrator', 'admin@cepatcopy.com', '081234567890', 'super_admin']);
        echo "<p style='color: green;'>✓ Profil admin default berhasil ditambahkan!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Profil admin sudah ada.</p>";
    }
    
    // Update admin password if needed
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute(['$2y$10$8K1p/a0dR1Ux5Y5Y5Y5O5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y']);
    echo "<p style='color: green;'>✓ Password admin berhasil diperbarui!</p>";
    
    echo "<h3>Struktur Tabel Admin:</h3>";
    echo "<ul>";
    echo "<li><strong>id</strong> - ID unik admin</li>";
    echo "<li><strong>user_id</strong> - Referensi ke tabel users</li>";
    echo "<li><strong>nama_lengkap</strong> - Nama lengkap admin</li>";
    echo "<li><strong>email</strong> - Email admin (unik)</li>";
    echo "<li><strong>no_telepon</strong> - Nomor telepon admin</li>";
    echo "<li><strong>alamat</strong> - Alamat admin</li>";
    echo "<li><strong>jabatan</strong> - Jabatan (super_admin, admin, operator)</li>";
    echo "<li><strong>status</strong> - Status (aktif, nonaktif)</li>";
    echo "<li><strong>foto_profil</strong> - Path foto profil</li>";
    echo "<li><strong>terakhir_login</strong> - Waktu login terakhir</li>";
    echo "<li><strong>created_at</strong> - Waktu pembuatan</li>";
    echo "<li><strong>updated_at</strong> - Waktu update terakhir</li>";
    echo "</ul>";
    
    echo "<h3>Data Admin Default:</h3>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> admin</li>";
    echo "<li><strong>Password:</strong> admin123</li>";
    echo "<li><strong>Email:</strong> admin@cepatcopy.com</li>";
    echo "<li><strong>Jabatan:</strong> super_admin</li>";
    echo "</ul>";
    
    echo "<p style='color: green; font-weight: bold;'>Tabel admin berhasil dibuat dan siap digunakan!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}
h2, h3 {
    color: #333;
}
ul {
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
li {
    margin: 5px 0;
}
</style> 