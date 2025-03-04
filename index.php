<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

$mesaj = '';

// URL'den gelen mesajları kontrol et
if (isset($_GET['hata'])) {
    $mesaj = "<div class='alert alert-danger'>" . htmlspecialchars($_GET['hata']) . "</div>";
} elseif (isset($_GET['basari'])) {
    $mesaj = "<div class='alert alert-success'>" . htmlspecialchars($_GET['basari']) . "</div>";
}

$sql = "SELECT 
        fs.*, 
        l.lokasyon_adi, 
        p.proje_adi, 
        p.proje_kodu,
        (SELECT COUNT(*) FROM sanal_sunucular WHERE fiziksel_sunucu_id = fs.id) as sanal_sayi,
        (
            SELECT GROUP_CONCAT(
                CONCAT(cpu, ':', ram, ':', disk)
                SEPARATOR '|'
            )
            FROM sanal_sunucular 
            WHERE fiziksel_sunucu_id = fs.id
        ) as sanal_kaynaklar
        FROM fiziksel_sunucular fs 
        LEFT JOIN lokasyonlar l ON fs.lokasyon_id = l.id 
        LEFT JOIN projeler p ON fs.proje_id = p.id
        ORDER BY fs.sunucu_adi";
$result = mysqli_query($conn, $sql);

// CPU, RAM ve Disk değerlerinden sayısal değerleri çıkaran fonksiyon
function kaynak_deger_al($str, $tip) {
    if (empty($str)) return 0;
    
    if ($tip == 'cpu') {
        // CPU'dan core sayısını çıkar
        preg_match('/(\d+)\s*(?:core|cores|cpu|işlemci|çekirdek)/i', $str, $matches);
        return isset($matches[1]) ? intval($matches[1]) : 0;
    } elseif ($tip == 'ram') {
        // RAM'den GB değerini çıkar
        preg_match('/(\d+)\s*(?:gb|g|gigabyte)/i', $str, $matches);
        return isset($matches[1]) ? intval($matches[1]) : 0;
    } else { // disk
        // Disk'ten GB/TB değerini çıkar ve GB'a çevir
        preg_match('/(\d+)\s*(tb|gb|g|t)/i', $str, $matches);
        if (isset($matches[1]) && isset($matches[2])) {
            $sayi = intval($matches[1]);
            $birim = strtolower($matches[2]);
            return ($birim == 'tb' || $birim == 't') ? $sayi * 1024 : $sayi;
        }
        return 0;
    }
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sunucu Takip Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .kaynak-kullanim {
            font-size: 0.85rem;
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-top: 0.5rem;
        }
        .progress {
            height: 0.5rem;
        }
        .kaynak-baslik {
            font-size: 0.75rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <?php echo $mesaj; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Fiziksel Sunucular</h1>
            <div>
                <a href="proje_ekle.php" class="btn btn-outline-success me-2">Yeni Proje Ekle</a>
                <a href="lokasyon_ekle.php" class="btn btn-outline-primary me-2">Yeni Lokasyon Ekle</a>
                <a href="fiziksel_sunucu_ekle.php" class="btn btn-primary">Yeni Fiziksel Sunucu Ekle</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sunucu Adı</th>
                        <th>IP Adresi</th>
                        <th>Donanım</th>
                        <th>Lokasyon</th>
                        <th>Proje</th>
                        <th>Sanal Sunucular</th>
                        <th>Oluşturma Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Fiziksel sunucu kaynakları
                            $fiziksel_cpu = kaynak_deger_al($row['cpu'], 'cpu');
                            $fiziksel_ram = kaynak_deger_al($row['ram'], 'ram');
                            $fiziksel_disk = kaynak_deger_al($row['disk'], 'disk');
                            
                            // Sanal sunucuların toplam kaynak kullanımı
                            $sanal_cpu = 0;
                            $sanal_ram = 0;
                            $sanal_disk = 0;
                            
                            if ($row['sanal_kaynaklar']) {
                                $sanal_sunucular = explode('|', $row['sanal_kaynaklar']);
                                foreach ($sanal_sunucular as $sanal) {
                                    list($cpu, $ram, $disk) = explode(':', $sanal);
                                    $sanal_cpu += kaynak_deger_al($cpu, 'cpu');
                                    $sanal_ram += kaynak_deger_al($ram, 'ram');
                                    $sanal_disk += kaynak_deger_al($disk, 'disk');
                                }
                            }
                            
                            echo "<tr class='align-middle'>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['sunucu_adi'] . "</td>";
                            echo "<td>" . $row['ip_adresi'] . "</td>";
                            echo "<td>";
                            if ($row['cpu'] || $row['ram'] || $row['disk']) {
                                echo "<small class='d-block'><strong>CPU:</strong> " . ($row['cpu'] ?: '-') . "</small>";
                                echo "<small class='d-block'><strong>RAM:</strong> " . ($row['ram'] ?: '-') . "</small>";
                                echo "<small class='d-block'><strong>Disk:</strong> " . ($row['disk'] ?: '-') . "</small>";
                                
                                // Kaynak kullanım bilgileri
                                if ($fiziksel_cpu > 0 || $fiziksel_ram > 0 || $fiziksel_disk > 0) {
                                    echo "<div class='kaynak-kullanim'>";
                                    if ($fiziksel_cpu > 0) {
                                        $cpu_yuzde = min(100, ($sanal_cpu / $fiziksel_cpu) * 100);
                                        echo "<div class='kaynak-baslik'>CPU Kullanımı: {$sanal_cpu}/{$fiziksel_cpu} Core (" . number_format($cpu_yuzde, 1) . "%)</div>";
                                        echo "<div class='progress mb-2'>";
                                        echo "<div class='progress-bar" . ($cpu_yuzde > 90 ? " bg-danger" : ($cpu_yuzde > 70 ? " bg-warning" : "")) . "' 
                                              role='progressbar' style='width: {$cpu_yuzde}%'></div>";
                                        echo "</div>";
                                    }
                                    
                                    if ($fiziksel_ram > 0) {
                                        $ram_yuzde = min(100, ($sanal_ram / $fiziksel_ram) * 100);
                                        echo "<div class='kaynak-baslik'>RAM Kullanımı: {$sanal_ram}/{$fiziksel_ram} GB (" . number_format($ram_yuzde, 1) . "%)</div>";
                                        echo "<div class='progress mb-2'>";
                                        echo "<div class='progress-bar" . ($ram_yuzde > 90 ? " bg-danger" : ($ram_yuzde > 70 ? " bg-warning" : "")) . "' 
                                              role='progressbar' style='width: {$ram_yuzde}%'></div>";
                                        echo "</div>";
                                    }
                                    
                                    if ($fiziksel_disk > 0) {
                                        $disk_yuzde = min(100, ($sanal_disk / $fiziksel_disk) * 100);
                                        echo "<div class='kaynak-baslik'>Disk Kullanımı: {$sanal_disk}/{$fiziksel_disk} GB (" . number_format($disk_yuzde, 1) . "%)</div>";
                                        echo "<div class='progress'>";
                                        echo "<div class='progress-bar" . ($disk_yuzde > 90 ? " bg-danger" : ($disk_yuzde > 70 ? " bg-warning" : "")) . "' 
                                              role='progressbar' style='width: {$disk_yuzde}%'></div>";
                                        echo "</div>";
                                    }
                                    echo "</div>";
                                }
                            } else {
                                echo "<span class='text-muted'>-</span>";
                            }
                            echo "</td>";
                            echo "<td>" . $row['lokasyon_adi'] . "</td>";
                            echo "<td>";
                            if ($row['proje_adi']) {
                                echo $row['proje_adi'] . " <small class='text-muted'>(" . $row['proje_kodu'] . ")</small>";
                            } else {
                                echo "<span class='text-muted'>-</span>";
                            }
                            echo "</td>";
                            echo "<td>";
                            if ($row['sanal_sayi'] > 0) {
                                echo "<span class='badge bg-info'>" . $row['sanal_sayi'] . " sanal sunucu</span>";
                            } else {
                                echo "<span class='badge bg-secondary'>Yok</span>";
                            }
                            echo "</td>";
                            echo "<td>" . date('d.m.Y H:i', strtotime($row['olusturma_tarihi'])) . "</td>";
                            echo "<td>
                                    <a href='sanal_sunucular.php?fiziksel_id=" . $row['id'] . "' class='btn btn-info btn-sm'>Sanal Sunucular</a>
                                    <a href='fiziksel_sunucu_duzenle.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Düzenle</a>";
                            if ($row['sanal_sayi'] == 0) {
                                echo " <a href='fiziksel_sunucu_sil.php?id=" . $row['id'] . "' 
                                         class='btn btn-danger btn-sm' 
                                         onclick='return confirm(\"Bu fiziksel sunucuyu silmek istediğinize emin misiniz?\")'>Sil</a>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>Henüz fiziksel sunucu eklenmemiş.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 