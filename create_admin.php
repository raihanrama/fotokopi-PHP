<?php
require_once 'includes/db.php';

$username = 'admin';
$password = 'admin123';

// Hash password menggunakan password_hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Hapus admin lama jika ada
    $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    // Buat admin baru
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);
    echo "Admin berhasil dibuat!<br>";
    echo "Username: " . $username . "<br>";
    echo "Password: " . $password;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 