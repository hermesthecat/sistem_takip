<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

if (!isset($_GET['fiziksel_id']) || empty($_GET['fiziksel_id'])) {
    header('Location: index.php?hata=' . urlencode('Fiziksel sunucu ID belirtilmedi.'));
    exit;
}

$fiziksel_id = mysqli_real_escape_string($conn, $_GET['fiziksel_id']);
$mesaj = '';

// Fiziksel sunucu bilgilerini al
$sql = "SELECT fs.*, p.id as varsayilan_proje_id 
        FROM fiziksel_sunucular fs 
        LEFT JOIN projeler p ON fs.proje_id = p.id 
        WHERE fs.id = '$fiziksel_id'";
$result = mysqli_query($conn, $sql);
$fiziksel_sunucu = mysqli_fetch_assoc($result);

if (!$fiziksel_sunucu) {
    header('Location: index.php?hata=' . urlencode('Fiziksel sunucu bulunamadı.'));
    exit;
}

// Fiziksel sunucunun kaynak kullanımını kontrol et
$sql = "SELECT 
        COALESCE(SUM(CAST(REGEXP_REPLACE(cpu, '[^0-9]', '') AS UNSIGNED)), 0) as toplam_cpu,
        COALESCE(SUM(CAST(REGEXP_REPLACE(ram, '[^0-9]', '') AS UNSIGNED)), 0) as toplam_ram,
        COALESCE(SUM(CASE 
            WHEN disk LIKE '%TB%' THEN CAST(REGEXP_REPLACE(disk, '[^0-9]', '') AS UNSIGNED) * 1024
            ELSE CAST(REGEXP_REPLACE(disk, '[^0-9]', '') AS UNSIGNED)
        END), 0) as toplam_disk
        FROM sanal_sunucular 
        WHERE fiziksel_sunucu_id = '$fiziksel_id'";
$result = mysqli_query($conn, $sql);
$kaynak_kullanim = mysqli_fetch_assoc($result);

// Fiziksel sunucunun toplam kaynaklarını hesapla
$fiziksel_cpu = intval(preg_replace('/[^0-9]/', '', $fiziksel_sunucu['cpu']));
$fiziksel_ram = intval(preg_replace('/[^0-9]/', '', $fiziksel_sunucu['ram']));
$fiziksel_disk = $fiziksel_sunucu['disk'];
if (stripos($fiziksel_disk, 'TB') !== false) {
    $fiziksel_disk = intval(preg_replace('/[^0-9]/', '', $fiziksel_disk)) * 1024;
} else {
    $fiziksel_disk = intval(preg_replace('/[^0-9]/', '', $fiziksel_disk));
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sunucu_adi = mysqli_real_escape_string($conn, $_POST['sunucu_adi']);
    $ip_adresi = mysqli_real_escape_string($conn, $_POST['ip_adresi']);
    $ram = mysqli_real_escape_string($conn, $_POST['ram']);
    $cpu = mysqli_real_escape_string($conn, $_POST['cpu']);
    $disk = mysqli_real_escape_string($conn, $_POST['disk']);
    $proje_id = isset($_POST['proje_id']) ? mysqli_real_escape_string($conn, $_POST['proje_id']) : 'NULL';
    
    // IP adresi kontrolü
    $ip_kontrol = "SELECT COUNT(*) as sayi FROM sanal_sunucular WHERE ip_adresi = '$ip_adresi'";
    $ip_result = mysqli_query($conn, $ip_kontrol);
    $ip_row = mysqli_fetch_assoc($ip_result);
    
    if ($ip_row['sayi'] > 0) {
        $mesaj = "<div class='alert alert-danger'>Bu IP adresi başka bir sunucu tarafından kullanılıyor.</div>";
    } else {
        $sql = "INSERT INTO sanal_sunucular (fiziksel_sunucu_id, sunucu_adi, ip_adresi, ram, cpu, disk, proje_id) 
                VALUES ('$fiziksel_id', '$sunucu_adi', '$ip_adresi', '$ram', '$cpu', '$disk', $proje_id)";
        
        if (mysqli_query($conn, $sql)) {
            header('Location: sanal_sunucular.php?fiziksel_id=' . $fiziksel_id . '&basari=' . urlencode('Sanal sunucu başarıyla eklendi.'));
            exit;
        } else {
            $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Projeleri getir
$sql = "SELECT * FROM projeler WHERE durum = 'Aktif' ORDER BY proje_adi";
$projeler = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Sanal Sunucu Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="mb-3">
            <a href="sanal_sunucular.php?fiziksel_id=<?php echo $fiziksel_id; ?>" class="btn btn-secondary">← Sanal Sunuculara Dön</a>
        </div>
        
        <?php echo $mesaj; ?>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title h5 mb-0">
                    Yeni Sanal Sunucu Ekle
                    <small class="text-muted">(<?php echo $fiziksel_sunucu['sunucu_adi']; ?>)</small>
                </h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Kaynak Kullanımı:</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <small>CPU: <?php echo $kaynak_kullanim['toplam_cpu']; ?>/<?php echo $fiziksel_cpu; ?> Core</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar <?php echo ($kaynak_kullanim['toplam_cpu'] / $fiziksel_cpu * 100 > 80) ? 'bg-danger' : ''; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo ($fiziksel_cpu > 0) ? ($kaynak_kullanim['toplam_cpu'] / $fiziksel_cpu * 100) : 0; ?>%">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small>RAM: <?php echo $kaynak_kullanim['toplam_ram']; ?>/<?php echo $fiziksel_ram; ?> GB</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar <?php echo ($kaynak_kullanim['toplam_ram'] / $fiziksel_ram * 100 > 80) ? 'bg-danger' : ''; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo ($fiziksel_ram > 0) ? ($kaynak_kullanim['toplam_ram'] / $fiziksel_ram * 100) : 0; ?>%">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small>Disk: <?php echo $kaynak_kullanim['toplam_disk']; ?>/<?php echo $fiziksel_disk; ?> GB</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar <?php echo ($kaynak_kullanim['toplam_disk'] / $fiziksel_disk * 100 > 80) ? 'bg-danger' : ''; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo ($fiziksel_disk > 0) ? ($kaynak_kullanim['toplam_disk'] / $fiziksel_disk * 100) : 0; ?>%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" class="row" id="sanal_sunucu_form">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sunucu_adi" class="form-label">Sunucu Adı</label>
                            <input type="text" class="form-control" id="sunucu_adi" name="sunucu_adi" required>
                        </div>
                        <div class="mb-3">
                            <label for="ip_adresi" class="form-label">IP Adresi</label>
                            <input type="text" class="form-control" id="ip_adresi" name="ip_adresi" 
                                   pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" 
                                   title="Lütfen geçerli bir IPv4 adresi girin" required>
                        </div>
                        <div class="mb-3">
                            <label for="proje_id" class="form-label">Proje</label>
                            <select class="form-select" id="proje_id" name="proje_id">
                                <option value="">Proje Seçin</option>
                                <?php while ($proje = mysqli_fetch_assoc($projeler)): ?>
                                    <option value="<?php echo $proje['id']; ?>" 
                                            <?php echo ($proje['id'] == $fiziksel_sunucu['varsayilan_proje_id']) ? 'selected' : ''; ?>>
                                        <?php echo $proje['proje_adi']; ?> (<?php echo $proje['proje_kodu']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text">Fiziksel sunucunun projesi varsayılan olarak seçilir.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="cpu" class="form-label">CPU (Core)</label>
                            <input type="text" class="form-control" id="cpu" name="cpu" 
                                   placeholder="Örn: 4 Core" required
                                   pattern="^\d+\s*(?:core|cores|cpu|işlemci|çekirdek)?$"
                                   title="Lütfen sadece sayı girin (örn: 4 veya 4 Core)">
                            <div class="form-text">Kalan: <?php echo $fiziksel_cpu - $kaynak_kullanim['toplam_cpu']; ?> Core</div>
                        </div>
                        <div class="mb-3">
                            <label for="ram" class="form-label">RAM (GB)</label>
                            <input type="text" class="form-control" id="ram" name="ram" 
                                   placeholder="Örn: 8GB" required
                                   pattern="^\d+\s*(?:gb|g|gigabyte)?$"
                                   title="Lütfen sadece sayı girin (örn: 8 veya 8GB)">
                            <div class="form-text">Kalan: <?php echo $fiziksel_ram - $kaynak_kullanim['toplam_ram']; ?> GB</div>
                        </div>
                        <div class="mb-3">
                            <label for="disk" class="form-label">Disk (GB)</label>
                            <input type="text" class="form-control" id="disk" name="disk" 
                                   placeholder="Örn: 100GB" required
                                   pattern="^\d+\s*(?:gb|g|tb|t)?$"
                                   title="Lütfen sadece sayı girin (örn: 100 veya 100GB)">
                            <div class="form-text">Kalan: <?php echo $fiziksel_disk - $kaynak_kullanim['toplam_disk']; ?> GB</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Sanal Sunucu Ekle</button>
                    </div>
                </form>

                <script>
                document.getElementById('sanal_sunucu_form').addEventListener('submit', function(e) {
                    var cpu = parseInt(document.getElementById('cpu').value.replace(/[^0-9]/g, ''));
                    var ram = parseInt(document.getElementById('ram').value.replace(/[^0-9]/g, ''));
                    var disk = parseInt(document.getElementById('disk').value.replace(/[^0-9]/g, ''));
                    
                    var kalanCpu = <?php echo $fiziksel_cpu - $kaynak_kullanim['toplam_cpu']; ?>;
                    var kalanRam = <?php echo $fiziksel_ram - $kaynak_kullanim['toplam_ram']; ?>;
                    var kalanDisk = <?php echo $fiziksel_disk - $kaynak_kullanim['toplam_disk']; ?>;
                    
                    var hatalar = [];
                    
                    if (cpu > kalanCpu) {
                        hatalar.push('CPU değeri kalan kapasiteden (' + kalanCpu + ' Core) fazla olamaz.');
                    }
                    
                    if (ram > kalanRam) {
                        hatalar.push('RAM değeri kalan kapasiteden (' + kalanRam + ' GB) fazla olamaz.');
                    }
                    
                    if (disk > kalanDisk) {
                        hatalar.push('Disk değeri kalan kapasiteden (' + kalanDisk + ' GB) fazla olamaz.');
                    }
                    
                    if (hatalar.length > 0) {
                        e.preventDefault();
                        alert('Lütfen aşağıdaki hataları düzeltin:\n\n' + hatalar.join('\n'));
                    }
                });
                </script>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 