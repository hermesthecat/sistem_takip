<?php
/**
 * @author A. Kerem Gök
 */
require_once 'auth.php';
require_once 'config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: hizmet_ekle.php?hata=' . urlencode($language->get('error_service_id_missing')));
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Hizmetin kullanımda olup olmadığını kontrol et
$sql = "SELECT COUNT(*) as kullanim FROM sanal_sunucu_hizmetler WHERE hizmet_id = '$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row['kullanim'] > 0) {
    $error_message = str_replace('{count}', $row['kullanim'], $language->get('error_service_in_use'));
    header('Location: hizmet_ekle.php?hata=' . urlencode($error_message));
    exit;
}

// Hizmeti sil
$sql = "DELETE FROM hizmetler WHERE id = '$id'";
if (mysqli_query($conn, $sql)) {
    header('Location: hizmet_ekle.php?basari=' . urlencode($language->get('success_service_deleted')));
} else {
    $error_message = str_replace('{error}', mysqli_error($conn), $language->get('error_deleting_service'));
    header('Location: hizmet_ekle.php?hata=' . urlencode($error_message));
}
exit; 