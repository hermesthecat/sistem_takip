<?php
/**
 * @author A. Kerem Gök
 */
session_start();

// Giriş yapılmamışsa login sayfasına yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    // Şu anki sayfanın yolunu al
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Eğer login sayfasında değilsek login'e yönlendir
    if ($current_page != 'login.php') {
        header('Location: login.php');
        exit;
    }
}

// Admin gerektiren sayfalar
$admin_pages = array('admin.php');

// Eğer admin sayfasına erişilmeye çalışılıyorsa ve kullanıcı admin değilse ana sayfaya yönlendir
if (in_array(basename($_SERVER['PHP_SELF']), $admin_pages) && $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?hata=' . urlencode('Bu sayfaya erişim yetkiniz yok!'));
    exit;
} 