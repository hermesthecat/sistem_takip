CREATE TABLE IF NOT EXISTS fiziksel_sunucular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sunucu_adi VARCHAR(100) NOT NULL,
    ip_adresi VARCHAR(15),
    lokasyon VARCHAR(100),
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
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
);