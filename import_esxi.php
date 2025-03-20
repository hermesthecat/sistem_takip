<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data && isset($data['virtual_machines'])) {

    $fiziksel_id = $data['physical_machine_id'];
    $token = $data['post_token'];

    // get physical machine post token and check if it is valid
    $sql = "SELECT * FROM fiziksel_sunucular WHERE post_token = '$token' AND id = '$fiziksel_id'";
    $result = mysqli_query($conn, $sql);
    $fiziksel_sunucu = mysqli_fetch_assoc($result);
    if (!$fiziksel_sunucu) {
        die('token or physical machine id is invalid');
    }

    foreach ($data['virtual_machines'] as $vm) {

        $sunucu_adi = mysqli_real_escape_string($conn, $vm['name']);
        $ram = mysqli_real_escape_string($conn, $vm['memory_mb']);
        $cpu = mysqli_real_escape_string($conn, $vm['num_cpu']);
        $disk = mysqli_real_escape_string($conn, $vm['total_disk_size_gb']);
        $vm_id = mysqli_real_escape_string($conn, $vm['id']);

        // sanal sunucu varsa güncelle
        $sql = "SELECT * FROM sanal_sunucular WHERE vm_id = '$vm_id' AND fiziksel_sunucu_id = '$fiziksel_id'";
        $result = mysqli_query($conn, $sql);
        $sanal_sunucu = mysqli_fetch_assoc($result);
        if ($sanal_sunucu) {
            // Sanal sunucu varsa güncelle
            $sql = "UPDATE sanal_sunucular SET sunucu_adi = '$sunucu_adi', ram = '$ram', cpu = '$cpu', disk = '$disk' WHERE vm_id = '$vm_id' AND fiziksel_sunucu_id = '$fiziksel_id'";
        } else {
            // Sanal sunucu yoksa ekle
            $sql = "INSERT INTO sanal_sunucular (fiziksel_sunucu_id, sunucu_adi, ram, cpu, disk, vm_id) 
                 VALUES ('$fiziksel_id', '$sunucu_adi', '$ram', '$cpu', '$disk', '$vm_id')";
        }

        if (mysqli_query($conn, $sql)) {
            echo "$sunucu_adi - Sanal sunucu başarıyla eklendi";
        } else {
            echo "$sunucu_adi - Sanal sunucu eklenirken hata oluştu: " . mysqli_error($conn);
        }
    }
} else {
    // Display error message if no data received
    // Veri alınamazsa hata mesajı göster
    die('Veri alınamadı veya hatalı format!');
}
