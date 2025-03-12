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
    header('Location: proje_ekle.php?hata=' . urlencode("Admin yetkiniz olmadığından silme işlemini yapamazsınız."));
    exit;
}

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
        if ($row['fiziksel_sayi'] > 0 && $row['sanal_sayi'] > 0) {
            $mesaj = str_replace(
                ['{fiziksel}', '{sanal}'],
                [$row['fiziksel_sayi'], $row['sanal_sayi']],
                $language->get('error_project_has_servers')
            );
        } elseif ($row['fiziksel_sayi'] > 0) {
            $mesaj = str_replace('{count}', $row['fiziksel_sayi'], $language->get('error_project_has_physical_servers'));
        } else {
            $mesaj = str_replace('{count}', $row['sanal_sayi'], $language->get('error_project_has_virtual_servers'));
        }

        header('Location: proje_ekle.php?hata=' . urlencode($mesaj));
    } else {
        // Bağlı sunucu yoksa sil
        $sql = "DELETE FROM projeler WHERE id = '$id'";
        if (mysqli_query($conn, $sql)) {
            header('Location: proje_ekle.php?basari=' . urlencode($language->get('success_project_deleted')));
        } else {
            $mesaj = str_replace('{error}', mysqli_error($conn), $language->get('error_deleting_project'));
            header('Location: proje_ekle.php?hata=' . urlencode($mesaj));
        }
    }
} else {
    header('Location: proje_ekle.php');
}
exit;
