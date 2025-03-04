<?php
/**
 * @author A. Kerem Gök
 */
require_once 'auth.php';
require_once 'config/database.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Önce bu fiziksel sunucuya bağlı sanal sunucu var mı kontrol et
    $sql_kontrol = "SELECT COUNT(*) as sayi FROM sanal_sunucular WHERE fiziksel_sunucu_id = '$id'";
    $result_kontrol = mysqli_query($conn, $sql_kontrol);
    $row = mysqli_fetch_assoc($result_kontrol);
    
    if ($row['sayi'] > 0) {
        // Eğer bağlı sanal sunucu varsa silme
        $mesaj = str_replace('{sayi}', $row['sayi'], $language->get('error_has_virtual_servers'));
        header('Location: index.php?hata=' . urlencode($mesaj));
    } else {
        // Bağlı sanal sunucu yoksa sil
        $sql = "DELETE FROM fiziksel_sunucular WHERE id = '$id'";
        if (mysqli_query($conn, $sql)) {
            header('Location: index.php?basari=' . urlencode($language->get('success_physical_server_deleted')));
        } else {
            header('Location: index.php?hata=' . urlencode(mysqli_error($conn)));
        }
    }
} else {
    header('Location: index.php');
}
exit; 