<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/config/database.php';

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data && isset($data['virtual_machines'])) {

    $fiziksel_id = $data['physical_machine_id'];
    $token = $data['post_token'];

    $log_file = __DIR__ . '/import_proxmox-' . $fiziksel_id . '.log';

    // if file exists, delete it
    if (file_exists($log_file)) {
        unlink($log_file);
    }

    // get physical machine post token and check if it is valid
    $sql = "SELECT * FROM fiziksel_sunucular WHERE post_token = '$token' AND id = '$fiziksel_id'";
    $result = mysqli_query($conn, $sql);
    $fiziksel_sunucu = mysqli_fetch_assoc($result);
    if (!$fiziksel_sunucu) {
        $log = date('Y-m-d H:i:s') . " - token veya fiziksel sunucu id geçersiz\n";
        file_put_contents($log_file, $log, FILE_APPEND);
        die('token or physical machine id is invalid');
    }

    foreach ($data['virtual_machines'] as $vm) {

        $sunucu_adi = mysqli_real_escape_string($conn, $vm['name']);
        $ram = mysqli_real_escape_string($conn, $vm['memory_mb']);
        $cpu = mysqli_real_escape_string($conn, $vm['num_cpu']);
        $disk = mysqli_real_escape_string($conn, $vm['total_disk_size_gb']);
        $vm_id = mysqli_real_escape_string($conn, $vm['id']);

        // write log
        $log = date('Y-m-d H:i:s') . " - $sunucu_adi - $ram MB - $cpu CPU - $disk GB\n";
        file_put_contents($log_file, $log, FILE_APPEND);

        // sanal sunucu var mı kontrol et
        $sql = "SELECT * FROM sanal_sunucular WHERE vm_id = '$vm_id' AND fiziksel_sunucu_id = '$fiziksel_id'";
        $result = mysqli_query($conn, $sql);
        $sanal_sunucu = mysqli_fetch_assoc($result);
        if ($sanal_sunucu) {
            // Sanal sunucu varsa güncelle
            $sql = "UPDATE sanal_sunucular SET sunucu_adi = '$sunucu_adi', ram = '$ram', cpu = '$cpu', disk = '$disk' WHERE vm_id = '$vm_id' AND fiziksel_sunucu_id = '$fiziksel_id'";
            $log = date('Y-m-d H:i:s') . " - $sunucu_adi - Sanal sunucu güncellendi\n";
            file_put_contents($log_file, $log, FILE_APPEND);
        } else {
            // Sanal sunucu yoksa ekle
            $sql = "INSERT INTO sanal_sunucular (fiziksel_sunucu_id, sunucu_adi, ram, cpu, disk, vm_id) 
                 VALUES ('$fiziksel_id', '$sunucu_adi', '$ram', '$cpu', '$disk', '$vm_id')";
            $log = date('Y-m-d H:i:s') . " - $sunucu_adi - Sanal sunucu başarıyla eklendi\n";
            file_put_contents($log_file, $log, FILE_APPEND);
        }

        // veritababanından sanal sunucu listesini al ve post'tan gelende olmayanların durumunu 0 yap
        $sql = "SELECT * FROM sanal_sunucular WHERE fiziksel_sunucu_id = '$fiziksel_id'";
        $result = mysqli_query($conn, $sql);
        $sanal_sunucular = mysqli_fetch_all($result, MYSQLI_ASSOC);
        foreach ($sanal_sunucular as $sanal_sunucu) {
            if (!in_array($sanal_sunucu['vm_id'], $data['virtual_machines'])) {
                $sql = "UPDATE sanal_sunucular SET durum = 0 WHERE vm_id = '$sanal_sunucu[vm_id]' AND fiziksel_sunucu_id = '$fiziksel_id'";
                mysqli_query($conn, $sql);
                $log = date('Y-m-d H:i:s') . " - $sanal_sunucu[sunucu_adi] - Sanal sunucu durumu 0 yapıldı\n";
                file_put_contents($log_file, $log, FILE_APPEND);
            }
        }

        if (mysqli_query($conn, $sql)) {
            echo "$sunucu_adi - Sanal sunucu başarıyla eklendi";
            $log = date('Y-m-d H:i:s') . " - $sunucu_adi - Sanal sunucu başarıyla eklendi\n";
            file_put_contents($log_file, $log, FILE_APPEND);
        } else {
            echo "$sunucu_adi - Sanal sunucu eklenirken hata oluştu: " . mysqli_error($conn);
            $log = date('Y-m-d H:i:s') . " - $sunucu_adi - Sanal sunucu eklenirken hata oluştu: " . mysqli_error($conn) . "\n";
            file_put_contents($log_file, $log, FILE_APPEND);
        }
    }
} else {
    // Display error message if no data received
    // Veri alınamazsa hata mesajı göster
    $log = date('Y-m-d H:i:s') . " - Veri alınamadı veya hatalı format!\n";
    file_put_contents($log_file, $log, FILE_APPEND);
    die('Veri alınamadı veya hatalı format!');
}
