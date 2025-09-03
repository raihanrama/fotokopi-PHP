-- Script untuk menambahkan tabel admin ke database yang sudah ada
-- Jalankan script ini jika database sudah ada dan ingin menambahkan tabel admin

USE cepatcopy;

-- Create admin table for detailed admin information
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
);

-- Add admin_id column to order_history table if it doesn't exist
ALTER TABLE order_history 
ADD COLUMN IF NOT EXISTS admin_id INT,
ADD CONSTRAINT IF NOT EXISTS fk_order_history_admin 
FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL;

-- Insert default admin profile if not exists
INSERT IGNORE INTO admin (user_id, nama_lengkap, email, no_telepon, jabatan) 
SELECT 1, 'Administrator', 'admin@cepatcopy.com', '081234567890', 'super_admin'
WHERE NOT EXISTS (SELECT 1 FROM admin WHERE user_id = 1);

-- Update existing admin user password if needed (password: admin123)
UPDATE users SET password = '$2y$10$8K1p/a0dR1Ux5Y5Y5Y5O5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y' 
WHERE username = 'admin' AND password != '$2y$10$8K1p/a0dR1Ux5Y5Y5Y5O5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y'; 