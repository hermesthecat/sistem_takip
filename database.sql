-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS sunucu_takip;
USE sunucu_takip;

-- Lokasyonlar tablosu
CREATE TABLE IF NOT EXISTS lokasyonlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lokasyon_adi VARCHAR(100) NOT NULL,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Fiziksel sunucular tablosu
CREATE TABLE IF NOT EXISTS fiziksel_sunucular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sunucu_adi VARCHAR(100) NOT NULL,
    ip_adresi VARCHAR(15),
    lokasyon_id INT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lokasyon_id) REFERENCES lokasyonlar(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Sanal sunucular tablosu
CREATE TABLE IF NOT EXISTS sanal_sunucular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiziksel_sunucu_id INT,
    sunucu_adi VARCHAR(100) NOT NULL,
    ip_adresi VARCHAR(15),
    ram VARCHAR(50),
    cpu VARCHAR(50),
    disk VARCHAR(50),
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fiziksel_sunucu_id) REFERENCES fiziksel_sunucular(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Örnek lokasyonlar
INSERT IGNORE INTO lokasyonlar (lokasyon_adi) VALUES 
('İstanbul'),
('Ankara'),
('İzmir'),
('Bursa'),
('Antalya');