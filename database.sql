-- Create database
CREATE DATABASE IF NOT EXISTS cepatcopy;
USE cepatcopy;

-- Create users table for admin (basic authentication)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create admin table for detailed admin information
CREATE TABLE admin (
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

-- Create customers table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pemesan VARCHAR(100) NOT NULL,
    kontak VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create services table (updated with longer field lengths)
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga_dasar DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create service_options table (updated with longer field lengths)
CREATE TABLE service_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    nama_opsi VARCHAR(100) NOT NULL,
    harga_tambahan DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Create orders table (updated with CASCADE delete for better management)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    service_id INT NOT NULL,
    service_option_id INT NOT NULL,
    jumlah_halaman INT NOT NULL,
    jumlah_copy INT NOT NULL,
    catatan TEXT,
    file_path VARCHAR(255) NOT NULL,
    metode_pengambilan ENUM('ambil','ojek_online') NOT NULL,
    status ENUM('masuk','diproses','selesai','siap_diambil') DEFAULT 'masuk',
    total_harga DECIMAL(10,2) NOT NULL,
    tanggal_pesan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (service_option_id) REFERENCES service_options(id) ON DELETE CASCADE
);

-- Create order_history table
CREATE TABLE order_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status_lama VARCHAR(50),
    status_baru VARCHAR(50) NOT NULL,
    catatan TEXT,
    admin_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password) VALUES ('admin', '$2y$10$8K1p/a0dR1Ux5Y5Y5Y5O5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y');

-- Insert default admin profile
INSERT INTO admin (user_id, nama_lengkap, email, no_telepon, jabatan) VALUES 
(1, 'Administrator', 'admin@cepatcopy.com', '081234567890', 'super_admin');

-- Insert default services
INSERT INTO services (nama_layanan, deskripsi, harga_dasar) VALUES
('Print', 'Layanan print dokumen', 500.00),
('Fotocopy', 'Layanan fotocopy dokumen', 200.00),
('Scan', 'Layanan scan dokumen', 1000.00);

-- Insert default service options
INSERT INTO service_options (service_id, nama_opsi, harga_tambahan) VALUES
(1, 'Hitam Putih', 0.00),
(1, 'Warna', 1000.00),
(2, 'Hitam Putih', 0.00),
(2, 'Warna', 500.00),
(3, 'Hitam Putih', 0.00),
(3, 'Warna', 2000.00);

-- Script untuk memperbaiki database yang sudah ada (jalankan jika database sudah ada)
-- ALTER TABLE services MODIFY COLUMN nama_layanan VARCHAR(100) NOT NULL;
-- ALTER TABLE service_options MODIFY COLUMN nama_opsi VARCHAR(100) NOT NULL;
-- ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_2;
-- ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_3;
-- ALTER TABLE orders ADD CONSTRAINT orders_ibfk_2 FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE;
-- ALTER TABLE orders ADD CONSTRAINT orders_ibfk_3 FOREIGN KEY (service_option_id) REFERENCES service_options(id) ON DELETE CASCADE; 