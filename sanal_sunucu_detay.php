<?php
/**
 * @author A. Kerem Gök
 */
require_once 'auth.php';
require_once 'config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$mesaj = '';

// Sanal sunucu bilgilerini al
$sql = "SELECT ss.*, fs.sunucu_adi as fiziksel_sunucu_adi, p.proje_adi, p.proje_kodu 
        FROM sanal_sunucular ss
        LEFT JOIN fiziksel_sunucular fs ON ss.fiziksel_sunucu_id = fs.id
        LEFT JOIN projeler p ON ss.proje_id = p.id
        WHERE ss.id = '$id'";
$result = mysqli_query($conn, $sql);
$sunucu = mysqli_fetch_assoc($result);

if (!$sunucu) {
    header('Location: index.php');
    exit;
}

// Hizmet ekleme/güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['yeni_hizmet_ekle'])) {
        $yeni_hizmet_adi = mysqli_real_escape_string($conn, $_POST['yeni_hizmet_adi']);
        $yeni_aciklama = mysqli_real_escape_string($conn, $_POST['yeni_aciklama']);
        $yeni_port = mysqli_real_escape_string($conn, $_POST['yeni_port']);
        
        // Önce yeni hizmeti ekle
        $sql = "INSERT INTO hizmetler (hizmet_adi, aciklama, port, durum) 
                VALUES ('$yeni_hizmet_adi', '$yeni_aciklama', '$yeni_port', 'Aktif')";
        
        if (mysqli_query($conn, $sql)) {
            $yeni_hizmet_id = mysqli_insert_id($conn);
            
            // Hizmeti direkt olarak sanal sunucuya ekle
            $sql = "INSERT INTO sanal_sunucu_hizmetler (sanal_sunucu_id, hizmet_id, ozel_port) 
                    VALUES ('$id', '$yeni_hizmet_id', '$yeni_port')";
            
            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>Yeni hizmet başarıyla eklendi ve sunucuya tanımlandı.</div>";
            } else {
                $mesaj = "<div class='alert alert-danger'>Hizmet eklendi fakat sunucuya tanımlanırken hata: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $mesaj = "<div class='alert alert-danger'>Hizmet eklenirken hata: " . mysqli_error($conn) . "</div>";
        }
    } elseif (isset($_POST['hizmet_ekle'])) {
        $hizmet_id = mysqli_real_escape_string($conn, $_POST['hizmet_id']);
        $ozel_port = mysqli_real_escape_string($conn, $_POST['ozel_port']);
        $notlar = mysqli_real_escape_string($conn, $_POST['notlar']);
        
        $sql = "INSERT INTO sanal_sunucu_hizmetler (sanal_sunucu_id, hizmet_id, ozel_port, notlar) 
                VALUES ('$id', '$hizmet_id', '$ozel_port', '$notlar')";
        
        if (mysqli_query($conn, $sql)) {
            $mesaj = "<div class='alert alert-success'>Hizmet başarıyla eklendi.</div>";
        } else {
            $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
        }
    } elseif (isset($_POST['hizmet_guncelle'])) {
        $hizmet_id = mysqli_real_escape_string($conn, $_POST['hizmet_id']);
        $ozel_port = mysqli_real_escape_string($conn, $_POST['ozel_port']);
        $notlar = mysqli_real_escape_string($conn, $_POST['notlar']);
        
        $sql = "UPDATE sanal_sunucu_hizmetler 
                SET ozel_port = '$ozel_port', 
                    notlar = '$notlar'
                WHERE sanal_sunucu_id = '$id' AND hizmet_id = '$hizmet_id'";
        
        if (mysqli_query($conn, $sql)) {
            $mesaj = "<div class='alert alert-success'>Hizmet başarıyla güncellendi.</div>";
        } else {
            $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Hizmet silme işlemi
if (isset($_GET['sil_hizmet'])) {
    $hizmet_id = mysqli_real_escape_string($conn, $_GET['sil_hizmet']);
    $sql = "DELETE FROM sanal_sunucu_hizmetler WHERE sanal_sunucu_id = '$id' AND hizmet_id = '$hizmet_id'";
    if (mysqli_query($conn, $sql)) {
        $mesaj = "<div class='alert alert-success'>Hizmet başarıyla kaldırıldı.</div>";
    } else {
        $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
    }
}

// Sunucunun mevcut hizmetlerini getir
$sql = "SELECT ssh.*, h.hizmet_adi, h.port as varsayilan_port 
        FROM sanal_sunucu_hizmetler ssh
        JOIN hizmetler h ON ssh.hizmet_id = h.id
        WHERE ssh.sanal_sunucu_id = '$id'
        ORDER BY h.hizmet_adi";
$mevcut_hizmetler = mysqli_query($conn, $sql);

// Eklenebilecek hizmetleri getir (henüz eklenmemiş ve aktif olanlar)
$sql = "SELECT * FROM hizmetler 
        WHERE durum = 'Aktif' 
        AND id NOT IN (
            SELECT hizmet_id 
            FROM sanal_sunucu_hizmetler 
            WHERE sanal_sunucu_id = '$id'
        )
        ORDER BY hizmet_adi";
$eklenebilir_hizmetler = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sanal Sunucu Detayı - <?php echo $sunucu['sunucu_adi']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <div class="container">
        <div class="mb-3">
            <a href="sanal_sunucular.php?fiziksel_id=<?php echo $sunucu['fiziksel_sunucu_id']; ?>" class="btn btn-secondary">← Sanal Sunuculara Dön</a>
        </div>
        
        <?php echo $mesaj; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h1 class="card-title h4 mb-0">
                    <?php echo $sunucu['sunucu_adi']; ?>
                    <small class="text-muted">(<?php echo $sunucu['ip_adresi']; ?>)</small>
                </h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fiziksel Sunucu:</strong> <?php echo $sunucu['fiziksel_sunucu_adi']; ?></p>
                        <p><strong>IP Adresi:</strong> <?php echo $sunucu['ip_adresi']; ?></p>
                        <p>
                            <strong>Proje:</strong> 
                            <?php 
                            if ($sunucu['proje_adi']) {
                                echo $sunucu['proje_adi'] . " (" . $sunucu['proje_kodu'] . ")";
                            } else {
                                echo "<span class='text-muted'>-</span>";
                            }
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Çekirdek:</strong> <?php echo $sunucu['cpu'] ?: '-'; ?></p>
                        <p><strong>Bellek:</strong> <?php echo $sunucu['ram'] ?: '-'; ?></p>
                        <p><strong>Disk:</strong> <?php echo $sunucu['disk'] ?: '-'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h5 mb-0">Çalışan Hizmetler</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Hizmet</th>
                                        <th>Port</th>
                                        <th>Notlar</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($mevcut_hizmetler) > 0): ?>
                                        <?php while ($hizmet = mysqli_fetch_assoc($mevcut_hizmetler)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($hizmet['hizmet_adi']); ?></td>
                                                <td>
                                                    <?php 
                                                    if ($hizmet['ozel_port']) {
                                                        echo $hizmet['ozel_port'];
                                                        if ($hizmet['ozel_port'] != $hizmet['varsayilan_port']) {
                                                            echo " <small class='text-muted'>(Varsayılan: " . $hizmet['varsayilan_port'] . ")</small>";
                                                        }
                                                    } else {
                                                        echo $hizmet['varsayilan_port'];
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo $hizmet['notlar'] ? nl2br(htmlspecialchars($hizmet['notlar'])) : '<span class="text-muted">-</span>'; ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#duzenleModal<?php echo $hizmet['hizmet_id']; ?>">
                                                        Düzenle
                                                    </button>
                                                    <a href="?id=<?php echo $id; ?>&sil_hizmet=<?php echo $hizmet['hizmet_id']; ?>" 
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Bu hizmeti kaldırmak istediğinize emin misiniz?')">Kaldır</a>
                                                </td>
                                            </tr>
                                            
                                            <!-- Düzenleme Modal -->
                                            <div class="modal fade" id="duzenleModal<?php echo $hizmet['hizmet_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Hizmet Düzenle - <?php echo $hizmet['hizmet_adi']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="hizmet_id" value="<?php echo $hizmet['hizmet_id']; ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Port</label>
                                                                    <input type="text" class="form-control" name="ozel_port" 
                                                                           value="<?php echo $hizmet['ozel_port'] ?: $hizmet['varsayilan_port']; ?>">
                                                                    <div class="form-text">Varsayılan port: <?php echo $hizmet['varsayilan_port']; ?></div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Notlar</label>
                                                                    <textarea class="form-control" name="notlar" rows="3"><?php echo $hizmet['notlar']; ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                <button type="submit" name="hizmet_guncelle" class="btn btn-primary">Güncelle</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Henüz hizmet eklenmemiş.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h5 mb-0">Yeni Hizmet Ekle</h2>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($eklenebilir_hizmetler) > 0): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="hizmet_id" class="form-label">Hizmet</label>
                                    <select class="form-select" id="hizmet_id" name="hizmet_id" required>
                                        <option value="">Hizmet Seçin</option>
                                        <?php while ($hizmet = mysqli_fetch_assoc($eklenebilir_hizmetler)): ?>
                                            <option value="<?php echo $hizmet['id']; ?>" data-port="<?php echo $hizmet['port']; ?>">
                                                <?php echo $hizmet['hizmet_adi']; ?>
                                                <?php echo $hizmet['port'] ? " (Port: " . $hizmet['port'] . ")" : ""; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="form-text">Listede görmek istediğiniz hizmet yok mu?</div>
                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#yeniHizmetModal">
                                            Yeni Hizmet Ekle
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="ozel_port" class="form-label">Özel Port (Opsiyonel)</label>
                                    <input type="text" class="form-control" id="ozel_port" name="ozel_port">
                                    <div class="form-text">Boş bırakılırsa varsayılan port kullanılır.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="notlar" class="form-label">Notlar</label>
                                    <textarea class="form-control" id="notlar" name="notlar" rows="3"></textarea>
                                </div>
                                <button type="submit" name="hizmet_ekle" class="btn btn-primary">Hizmet Ekle</button>
                            </form>
                            
                            <script>
                            document.getElementById('hizmet_id').addEventListener('change', function() {
                                var selectedOption = this.options[this.selectedIndex];
                                var defaultPort = selectedOption.getAttribute('data-port');
                                document.getElementById('ozel_port').placeholder = defaultPort ? 'Varsayılan: ' + defaultPort : '';
                            });
                            </script>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                Eklenebilecek aktif hizmet bulunmuyor.
                                <button type="button" class="btn btn-success btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#yeniHizmetModal">
                                    Yeni Hizmet Ekle
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni Hizmet Ekleme Modal -->
    <div class="modal fade" id="yeniHizmetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Hizmet Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="yeniHizmetForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="yeni_hizmet_adi" class="form-label">Hizmet Adı</label>
                            <input type="text" class="form-control" id="yeni_hizmet_adi" name="yeni_hizmet_adi" required>
                        </div>
                        <div class="mb-3">
                            <label for="yeni_aciklama" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="yeni_aciklama" name="yeni_aciklama" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="yeni_port" class="form-label">Varsayılan Port</label>
                            <input type="text" class="form-control" id="yeni_port" name="yeni_port">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" name="yeni_hizmet_ekle" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>