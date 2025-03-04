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