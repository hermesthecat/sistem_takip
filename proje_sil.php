<?php
/**
 * @author A. Kerem Gök
 */
require_once 'auth.php';
require_once 'config/database.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Projeye bağlı fiziksel ve sanal sunucuları kontrol et
    $sql_kontrol = "SELECT 
        (SELECT COUNT(*) FROM fiziksel_sunucular WHERE proje_id = '$id') as fiziksel_sayi,
        (SELECT COUNT(*) FROM sanal_sunucular WHERE proje_id = '$id') as sanal_sayi";
    
    $result_kontrol = mysqli_query($conn, $sql_kontrol);
    $row = mysqli_fetch_assoc($result_kontrol);
    
    if ($row['fiziksel_sayi'] > 0 || $row['sanal_sayi'] > 0) {
        // Eğer bağlı sunucu varsa silme
        $mesaj = "Bu projeye bağlı ";
        $bagli_sunucular = array();
        
        if ($row['fiziksel_sayi'] > 0) {
            $bagli_sunucular[] = $row['fiziksel_sayi'] . " fiziksel sunucu";
        }
        if ($row['sanal_sayi'] > 0) {
            $bagli_sunucular[] = $row['sanal_sayi'] . " sanal sunucu";
        }
        
        $mesaj .= implode(" ve ", $bagli_sunucular) . " olduğu için silinemez.";
        header('Location: proje_ekle.php?hata=' . urlencode($mesaj));
    } else {
        // Bağlı sunucu yoksa sil
        $sql = "DELETE FROM projeler WHERE id = '$id'";
        if (mysqli_query($conn, $sql)) {
            header('Location: proje_ekle.php?basari=Proje başarıyla silindi.');
        } else {
            header('Location: proje_ekle.php?hata=' . urlencode(mysqli_error($conn)));
        }
    }
} else {
    header('Location: proje_ekle.php');
}
exit; 