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

// Veritabanını seç
mysqli_select_db($conn, DB_NAME);
