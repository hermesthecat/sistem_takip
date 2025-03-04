<?php
/**
 * @author A. Kerem Gök
 */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'sunucu_takip');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

if (!$conn) {
    die("Bağlantı hatası: " . mysqli_connect_error());
}

if (!mysqli_select_db($conn, DB_NAME)) {
    // Veritabanı yoksa oluştur
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (mysqli_query($conn, $sql)) {
        mysqli_select_db($conn, DB_NAME);
    } else {
        die("Veritabanı oluşturma hatası: " . mysqli_error($conn));
    }
}

// Tablo oluşturma
$sql_fiziksel_sunucu = "CREATE TABLE IF NOT EXISTS fiziksel_sunucular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sunucu_adi VARCHAR(100) NOT NULL,
    ip_adresi VARCHAR(15),
    lokasyon VARCHAR(100),
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_sanal_sunucu = "CREATE TABLE IF NOT EXISTS sanal_sunucular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiziksel_sunucu_id INT,
    sunucu_adi VARCHAR(100) NOT NULL,
    ip_adresi VARCHAR(15),
    ram VARCHAR(50),
    cpu VARCHAR(50),
    disk VARCHAR(50),
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fiziksel_sunucu_id) REFERENCES fiziksel_sunucular(id)
)";

mysqli_query($conn, $sql_fiziksel_sunucu);
mysqli_query($conn, $sql_sanal_sunucu); 