<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

// Admin kontrolü
if ($_SESSION['rol'] !== 'admin') {
    header('Location: lokasyon_ekle.php?hata=' . urlencode("Admin yetkiniz olmadığından silme işlemini yapamazsınız."));
    exit;
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Önce bu lokasyona bağlı sunucu var mı kontrol et
    $sql_kontrol = "SELECT COUNT(*) as sayi FROM fiziksel_sunucular WHERE lokasyon_id = '$id'";
    $result_kontrol = mysqli_query($conn, $sql_kontrol);
    $row = mysqli_fetch_assoc($result_kontrol);

    if ($row['sayi'] > 0) {
        // Eğer bağlı sunucu varsa silme
        header('Location: lokasyon_ekle.php?hata=' . urlencode($language->get('error_location_has_servers')));
    } else {
        // Bağlı sunucu yoksa sil
        $sql = "DELETE FROM lokasyonlar WHERE id = '$id'";
        if (mysqli_query($conn, $sql)) {
            header('Location: lokasyon_ekle.php?basari=' . urlencode($language->get('success_location_deleted')));
        } else {
            header('Location: lokasyon_ekle.php?hata=' . urlencode($language->get('error') . ": " . mysqli_error($conn)));
        }
    }
} else {
    header('Location: lokasyon_ekle.php');
}
exit;
