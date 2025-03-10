<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: site_ekle.php?hata=' . urlencode($language->get('error_service_id_missing')));
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Web sitesinin kullanımda olup olmadığını kontrol et
$sql = "SELECT COUNT(*) as kullanim FROM sanal_sunucu_websiteler WHERE website_id = '$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row['kullanim'] > 0) {
    $error_message = str_replace('{count}', $row['kullanim'], $language->get('error_web_site_in_use'));
    header('Location: site_ekle.php?hata=' . urlencode($error_message));
    exit;
}

// Web sitesini sil
$sql = "DELETE FROM websiteler WHERE id = '$id'";
if (mysqli_query($conn, $sql)) {
    header('Location: site_ekle.php?basari=' . urlencode($language->get('success_web_site_deleted')));
} else {
    $error_message = str_replace('{error}', mysqli_error($conn), $language->get('error_deleting_web_site'));
    header('Location: site_ekle.php?hata=' . urlencode($error_message));
}
exit;
