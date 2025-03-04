<?php
/**
 * @author A. Kerem Gök
 */
require_once 'auth.php';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    <?php require_once 'header.php'; ?>
    
    <div class="container">
        <?php echo $mesaj; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Fiziksel Sunucular</h1>
            <div>
                <a href="fiziksel_sunucu_ekle.php" class="btn btn-primary">Fiziksel Sunucu Ekle</a>
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
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['sunucu_adi']; ?></td>
                                <td><?php echo $row['ip_adresi'] ?: '<span class="text-muted">-</span>'; ?></td>
                                <td>
                                    <?php if ($row['cpu'] || $row['ram'] || $row['disk']): ?>
                                        <ul class="list-unstyled mb-0">
                                            <?php if ($row['cpu']): ?>
                                                <li><strong>Çekirdek:</strong> <?php echo $row['cpu']; ?></li>
                                            <?php endif; ?>
                                            <?php if ($row['ram']): ?>
                                                <li><strong>Bellek:</strong> <?php echo $row['ram']; ?></li>
                                            <?php endif; ?>
                                            <?php if ($row['disk']): ?>
                                                <li><strong>Disk:</strong> <?php echo $row['disk']; ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['lokasyon_adi'] ?: '<span class="text-muted">-</span>'; ?></td>
                                <td>
                                    <?php
                                    if ($row['proje_adi']) {
                                        echo $row['proje_adi'] . " <small class='text-muted'>(" . $row['proje_kodu'] . ")</small>";
                                    } else {
                                        echo "<span class='text-muted'>-</span>";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($row['sanal_sayi'] > 0) {
                                        echo "<span class='badge bg-info'>" . $row['sanal_sayi'] . " sanal sunucu</span>";
                                    } else {
                                        echo "<span class='badge bg-secondary'>Yok</span>";
                                    }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <a href='sanal_sunucular.php?fiziksel_id=<?php echo $row['id']; ?>' class='btn btn-info btn-sm'>Sanal Sunucular</a>
                                    <a href='fiziksel_sunucu_duzenle.php?id=<?php echo $row['id']; ?>' class='btn btn-warning btn-sm'>Düzenle</a>
                                    <?php if ($row['sanal_sayi'] == 0): ?>
                                        <a href='fiziksel_sunucu_sil.php?id=<?php echo $row['id']; ?>' 
                                           class='btn btn-danger btn-sm' 
                                           onclick='return confirm("Bu fiziksel sunucuyu silmek istediğinize emin misiniz?")'>Sil</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($row['sanal_sayi'] > 0 && $row['sanal_kaynaklar']): ?>
                            <tr class="table-light">
                                <td colspan="9">
                                    <div class="small">
                                        <strong>Fiziksel Sunucu Kaynakları:</strong>
                                        <table class="table table-sm table-bordered mt-1 mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width: 33%">Çekirdek Kullanımı</th>
                                                    <th style="width: 33%">Bellek Kullanımı</th>
                                                    <th style="width: 33%">Disk Kullanımı</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <?php
                                                    $sanal_kaynaklar = explode('|', $row['sanal_kaynaklar']);
                                                    
                                                    // Toplam değerleri tutacak değişkenler
                                                    $toplam_cpu = 0;
                                                    $toplam_ram = 0;
                                                    $toplam_disk = 0;
                                                    
                                                    foreach ($sanal_kaynaklar as $kaynak) {
                                                        if (!empty($kaynak)) {
                                                            list($cpu, $ram, $disk) = explode(':', $kaynak);
                                                            // Sayısal değerleri çıkar
                                                            $cpu_sayi = intval(preg_replace('/[^0-9]/', '', $cpu));
                                                            $ram_sayi = intval(preg_replace('/[^0-9]/', '', $ram));
                                                            $disk_sayi = intval(preg_replace('/[^0-9]/', '', $disk));
                                                            
                                                            // TB to GB dönüşümü
                                                            if (stripos($disk, 'TB') !== false) {
                                                                $disk_sayi *= 1024;
                                                            }
                                                            
                                                            $toplam_cpu += $cpu_sayi;
                                                            $toplam_ram += $ram_sayi;
                                                            $toplam_disk += $disk_sayi;
                                                        }
                                                    }
                                                    
                                                    // Fiziksel sunucunun toplam kaynaklarını al
                                                    $fiziksel_cpu = intval(preg_replace('/[^0-9]/', '', $row['cpu']));
                                                    $fiziksel_ram = intval(preg_replace('/[^0-9]/', '', $row['ram']));
                                                    $fiziksel_disk = $row['disk'];
                                                    if (stripos($fiziksel_disk, 'TB') !== false) {
                                                        $fiziksel_disk = intval(preg_replace('/[^0-9]/', '', $fiziksel_disk)) * 1024;
                                                    } else {
                                                        $fiziksel_disk = intval(preg_replace('/[^0-9]/', '', $fiziksel_disk));
                                                    }
                                                    
                                                    // Kullanım yüzdelerini hesapla
                                                    $cpu_yuzde = round(($toplam_cpu / $fiziksel_cpu) * 100);
                                                    $ram_yuzde = round(($toplam_ram / $fiziksel_ram) * 100);
                                                    $disk_yuzde = round(($toplam_disk / $fiziksel_disk) * 100);
                                                    
                                                    // Progress bar renkleri için class belirle
                                                    function get_progress_class($yuzde) {
                                                        if ($yuzde >= 90) return 'bg-danger';
                                                        if ($yuzde >= 75) return 'bg-warning';
                                                        return 'bg-success';
                                                    }
                                                    ?>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar <?php echo get_progress_class($cpu_yuzde); ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo $cpu_yuzde; ?>%;" 
                                                                 aria-valuenow="<?php echo $cpu_yuzde; ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                                <?php echo $toplam_cpu . "/" . $fiziksel_cpu . " Çekirdek"; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar <?php echo get_progress_class($ram_yuzde); ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo $ram_yuzde; ?>%;" 
                                                                 aria-valuenow="<?php echo $ram_yuzde; ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                                <?php echo $toplam_ram . "/" . $fiziksel_ram . " GB"; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar <?php echo get_progress_class($disk_yuzde); ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo $disk_yuzde; ?>%;" 
                                                                 aria-valuenow="<?php echo $disk_yuzde; ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                                <?php echo $toplam_disk . "/" . $fiziksel_disk . " GB"; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Henüz fiziksel sunucu eklenmemiş.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 