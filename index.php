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

$sql = "SELECT fs.*, l.lokasyon_adi, p.proje_adi, p.proje_kodu,
        (SELECT COUNT(*) FROM sanal_sunucular WHERE fiziksel_sunucu_id = fs.id) as sanal_sayi
        FROM fiziksel_sunucular fs 
        LEFT JOIN lokasyonlar l ON fs.lokasyon_id = l.id 
        LEFT JOIN projeler p ON fs.proje_id = p.id
        ORDER BY fs.sunucu_adi";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sunucu Takip Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['sunucu_adi'] . "</td>";
                            echo "<td>" . $row['ip_adresi'] . "</td>";
                            echo "<td>";
                            if ($row['cpu'] || $row['ram'] || $row['disk']) {
                                echo "<small class='d-block'><strong>CPU:</strong> " . ($row['cpu'] ?: '-') . "</small>";
                                echo "<small class='d-block'><strong>RAM:</strong> " . ($row['ram'] ?: '-') . "</small>";
                                echo "<small class='d-block'><strong>Disk:</strong> " . ($row['disk'] ?: '-') . "</small>";
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