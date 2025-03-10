-- Lokasyonlar tablosu
CREATE TABLE IF NOT EXISTS lokasyonlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lokasyon_adi VARCHAR(100) NOT NULL,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;

-- Projeler tablosu
CREATE TABLE IF NOT EXISTS projeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proje_adi VARCHAR(100) NOT NULL,
    proje_kodu VARCHAR(50),
    aciklama TEXT,
    durum ENUM('Aktif', 'Pasif', 'Tamamlandı') DEFAULT 'Aktif',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;

-- Fiziksel sunucular tablosu
CREATE TABLE IF NOT EXISTS fiziksel_sunucular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sunucu_adi VARCHAR(100) NOT NULL,
    ip_adresi VARCHAR(15),
    ram VARCHAR(50),
    cpu VARCHAR(50),
    disk VARCHAR(50),
    lokasyon_id INT,
    proje_id INT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lokasyon_id) REFERENCES lokasyonlar(id),
    FOREIGN KEY (proje_id) REFERENCES projeler(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;

-- Sanal sunucular tablosu
CREATE TABLE IF NOT EXISTS sanal_sunucular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiziksel_sunucu_id INT,
    sunucu_adi VARCHAR(100) NOT NULL,
    ip_adresi VARCHAR(15),
    ram VARCHAR(50),
    cpu VARCHAR(50),
    disk VARCHAR(50),
    proje_id INT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fiziksel_sunucu_id) REFERENCES fiziksel_sunucular(id),
    FOREIGN KEY (proje_id) REFERENCES projeler(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;

-- Hizmetler tablosu
CREATE TABLE IF NOT EXISTS hizmetler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hizmet_adi VARCHAR(100) NOT NULL,
    aciklama TEXT,
    port VARCHAR(10),
    durum ENUM('Aktif', 'Pasif') DEFAULT 'Aktif',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;

-- Sanal sunucu hizmetleri tablosu
CREATE TABLE IF NOT EXISTS sanal_sunucu_hizmetler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sanal_sunucu_id INT,
    hizmet_id INT,
    ozel_port VARCHAR(10),
    notlar TEXT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sanal_sunucu_id) REFERENCES sanal_sunucular(id) ON DELETE CASCADE,
    FOREIGN KEY (hizmet_id) REFERENCES hizmetler(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;

-- Web Siteler tablosu
CREATE TABLE IF NOT EXISTS websiteler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alan_adi VARCHAR(100) NOT NULL,
    aciklama TEXT,
    durum ENUM('Aktif', 'Pasif') DEFAULT 'Aktif',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;

-- Sanal sunucu web siteleri tablosu
CREATE TABLE IF NOT EXISTS sanal_sunucu_web_siteler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sanal_sunucu_id INT,
    website_id INT,
    notlar TEXT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sanal_sunucu_id) REFERENCES sanal_sunucular(id) ON DELETE CASCADE,
    FOREIGN KEY (website_id) REFERENCES websiteler(id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'kullanici') DEFAULT 'kullanici',
    durum ENUM('Aktif', 'Pasif') DEFAULT 'Aktif',
    son_giris DATETIME,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;

-- Örnek lokasyonlar
INSERT
    IGNORE INTO lokasyonlar (lokasyon_adi)
VALUES
    ('İstanbul'),
    ('Ankara'),
    ('İzmir'),
    ('Bursa'),
    ('Antalya');

-- Örnek projeler
INSERT
    IGNORE INTO projeler (proje_adi, proje_kodu, aciklama, durum)
VALUES
    (
        'Web Hosting',
        'WH-2024',
        'Web hosting hizmetleri için ayrılmış sunucular',
        'Aktif'
    ),
    (
        'E-Ticaret',
        'EC-2024',
        'E-ticaret platformu sunucuları',
        'Aktif'
    ),
    (
        'Test Ortamı',
        'TEST-2024',
        'Test ve geliştirme ortamı sunucuları',
        'Aktif'
    ),
    (
        'Veritabanı Cluster',
        'DB-2024',
        'Veritabanı cluster sunucuları',
        'Aktif'
    ),
    (
        'Yedekleme Sistemi',
        'BKP-2024',
        'Yedekleme ve arşiv sunucuları',
        'Aktif'
    );

-- Örnek hizmetler
INSERT
    IGNORE INTO hizmetler (hizmet_adi, aciklama, port)
VALUES
    ('HTTP Web Sunucu', 'Web sunucu hizmeti', '80'),
    (
        'HTTPS Web Sunucu',
        'Güvenli web sunucu hizmeti',
        '443'
    ),
    (
        'MySQL Database',
        'MySQL veritabanı hizmeti',
        '3306'
    ),
    (
        'PostgreSQL Database',
        'PostgreSQL veritabanı hizmeti',
        '5432'
    ),
    ('FTP Sunucu', 'FTP dosya transfer hizmeti', '21'),
    ('SSH', 'Güvenli kabuk erişimi', '22'),
    ('Redis', 'Redis önbellek sunucusu', '6379'),
    ('MongoDB', 'MongoDB NoSQL veritabanı', '27017'),
    (
        'Elasticsearch',
        'Elasticsearch arama motoru',
        '9200'
    ),
    ('RabbitMQ', 'RabbitMQ mesaj kuyruğu', '5672');

-- Varsayılan admin kullanıcısı (şifre: admin123)
INSERT
    IGNORE INTO kullanicilar (kullanici_adi, ad_soyad, email, sifre, rol)
VALUES
    (
        'admin',
        'Sistem Yöneticisi',
        'admin@sistem.local',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin'
    );