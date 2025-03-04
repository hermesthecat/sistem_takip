<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: hizmet_ekle.php?hata=' . urlencode('Hizmet ID belirtilmedi.'));
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Hizmetin kullanımda olup olmadığını kontrol et
$sql = "SELECT COUNT(*) as kullanim FROM sanal_sunucu_hizmetler WHERE hizmet_id = '$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row['kullanim'] > 0) {
    header('Location: hizmet_ekle.php?hata=' . urlencode('Bu hizmet ' . $row['kullanim'] . ' sanal sunucu tarafından kullanılıyor. Önce bu sunuculardan hizmeti kaldırın.'));
    exit;
}

// Hizmeti sil
$sql = "DELETE FROM hizmetler WHERE id = '$id'";
if (mysqli_query($conn, $sql)) {
    header('Location: hizmet_ekle.php?basari=' . urlencode('Hizmet başarıyla silindi.'));
} else {
    header('Location: hizmet_ekle.php?hata=' . urlencode('Hizmet silinirken bir hata oluştu: ' . mysqli_error($conn)));
}
exit; 