-- Tabel untuk menyimpan berita
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

-- Tabel untuk menyimpan informasi about
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

-- Insert default about info
INSERT INTO `about_info` (`title`, `description`, `vision`, `mission`, `address`, `phone`, `email`) VALUES
('CepatCopy JWP', 'Kami adalah penyedia layanan fotokopi dan digital printing terpercaya yang telah melayani pelanggan sejak lama. Dengan pengalaman dan dedikasi tinggi, kami berkomitmen memberikan layanan terbaik dengan kualitas tinggi dan harga terjangkau.', 'Menjadi penyedia layanan fotokopi dan digital printing terdepan yang dipercaya oleh masyarakat.', 'Memberikan layanan fotokopi dan digital printing berkualitas tinggi dengan harga terjangkau, didukung teknologi modern dan pelayanan yang ramah.', 'Jl. Contoh No. 123, Kota, Provinsi', '+62 812-3456-7890', 'info@cepatcopyjwp.com'); 