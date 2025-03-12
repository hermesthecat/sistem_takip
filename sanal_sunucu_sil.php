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
    header('Location: sanal_sunucular.php?hata=' . urlencode("Admin yetkiniz olmadığından silme işlemini yapamazsınız."));
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: sanal_sunucular.php');
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Sanal sunucunun fiziksel sunucu ID'sini al
$sql_get = "SELECT fiziksel_sunucu_id FROM sanal_sunucular WHERE id = '$id'";
$result = mysqli_query($conn, $sql_get);
$row = mysqli_fetch_assoc($result);
$fiziksel_id = $row['fiziksel_sunucu_id'];

// Sanal sunucuyu sil
$sql = "DELETE FROM sanal_sunucular WHERE id = '$id'";
mysqli_query($conn, $sql);

header("Location: sanal_sunucular.php?fiziksel_id=$fiziksel_id");
exit;
