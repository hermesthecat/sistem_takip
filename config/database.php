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
