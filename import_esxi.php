<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$log_file = __DIR__ . '/import_esxi.log';

function log_message($message)
{
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

log_message("Date: " . date('Y-m-d H:i:s'));

if ($data && isset($data['virtual_machines'])) {

    log_message("--------------------------------");

    $fiziksel_id = $data['physical_machine_id'];
    log_message("Physical machine ID: $fiziksel_id");
    $token = $data['post_token'];
    log_message("Post token: $token");

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

        log_message("Virtual machine ID: $vm_id");
        log_message("Virtual machine name: $sunucu_adi");
        log_message("Virtual machine RAM: $ram");
        log_message("Virtual machine CPU: $cpu");
        log_message("Virtual machine disk: $disk");

        // sanal sunucu varsa güncelle
        $sql = "SELECT * FROM sanal_sunucular WHERE vm_id = '$vm_id' AND fiziksel_sunucu_id = '$fiziksel_id'";
        $result = mysqli_query($conn, $sql);
        $sanal_sunucu = mysqli_fetch_assoc($result);
        if ($sanal_sunucu) {
            // Sanal sunucu varsa güncelle
            $sql = "UPDATE sanal_sunucular SET sunucu_adi = '$sunucu_adi', ram = '$ram', cpu = '$cpu', disk = '$disk' WHERE vm_id = '$vm_id' AND fiziksel_sunucu_id = '$fiziksel_id'";
            log_message("Updating virtual machine: $sunucu_adi");
        } else {
            // Sanal sunucu yoksa ekle
            $sql = "INSERT INTO sanal_sunucular (fiziksel_sunucu_id, sunucu_adi, ram, cpu, disk, vm_id) 
                 VALUES ('$fiziksel_id', '$sunucu_adi', '$ram', '$cpu', '$disk', '$vm_id')";
            log_message("Inserting virtual machine: $sunucu_adi");
        }

        if (mysqli_query($conn, $sql)) {
            echo "$sunucu_adi - Sanal sunucu başarıyla eklendi";
            log_message("Virtual machine updated: $sunucu_adi");
        } else {
            echo "$sunucu_adi - Sanal sunucu eklenirken hata oluştu: " . mysqli_error($conn);
            log_message("Virtual machine insertion error: $sunucu_adi");
        }
    }
} else {
    // Display error message if no data received
    // Veri alınamazsa hata mesajı göster
    log_message("No data received");
    die('Veri alınamadı veya hatalı format!');
}
