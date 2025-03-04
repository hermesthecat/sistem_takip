<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Önce bağlı sanal sunucuları sil
$sql_sanal = "DELETE FROM sanal_sunucular WHERE fiziksel_sunucu_id = '$id'";
mysqli_query($conn, $sql_sanal);

// Sonra fiziksel sunucuyu sil
$sql = "DELETE FROM fiziksel_sunucular WHERE id = '$id'";
mysqli_query($conn, $sql);

header('Location: index.php');
exit; 