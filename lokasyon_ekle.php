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

$duzenle_mod = false;
$lokasyon = null;

// Düzenleme modu kontrolü
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM lokasyonlar WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $lokasyon = mysqli_fetch_assoc($result);
    
    if ($lokasyon) {
        $duzenle_mod = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lokasyon_adi = mysqli_real_escape_string($conn, $_POST['lokasyon_adi']);

    if ($duzenle_mod) {
        // Güncelleme işlemi
        $id = mysqli_real_escape_string($conn, $_GET['id']);
        
        // Aynı isimde lokasyon var mı kontrolü (kendi adı hariç)
        $sql_kontrol = "SELECT id FROM lokasyonlar WHERE lokasyon_adi = '$lokasyon_adi' AND id != '$id'";
        $result_kontrol = mysqli_query($conn, $sql_kontrol);

        if (mysqli_num_rows($result_kontrol) > 0) {
            $mesaj = "<div class='alert alert-danger'>Hata: Bu lokasyon adı zaten kullanılıyor!</div>";
        } else {
            $sql = "UPDATE lokasyonlar SET lokasyon_adi = '$lokasyon_adi' WHERE id = '$id'";
            
            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>Lokasyon başarıyla güncellendi.</div>";
                // Güncel veriyi al
                $sql = "SELECT * FROM lokasyonlar WHERE id = '$id'";
                $result = mysqli_query($conn, $sql);
                $lokasyon = mysqli_fetch_assoc($result);
            } else {
                $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
            }
        }
    } else {
        // Yeni lokasyon ekleme
        $sql_kontrol = "SELECT id FROM lokasyonlar WHERE lokasyon_adi = '$lokasyon_adi'";
        $result_kontrol = mysqli_query($conn, $sql_kontrol);

        if (mysqli_num_rows($result_kontrol) > 0) {
            $mesaj = "<div class='alert alert-danger'>Hata: Bu lokasyon adı zaten kullanılıyor!</div>";
        } else {
            $sql = "INSERT INTO lokasyonlar (lokasyon_adi) VALUES ('$lokasyon_adi')";
            
            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>Yeni lokasyon başarıyla eklendi.</div>";
                $_POST = array(); // Formu temizle
            } else {
                $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

// Mevcut lokasyonları listele
$sql_lokasyonlar = "SELECT l.*, 
                    (SELECT COUNT(*) FROM fiziksel_sunucular WHERE lokasyon_id = l.id) as sunucu_sayisi 
                    FROM lokasyonlar l 
                    ORDER BY l.lokasyon_adi";
$result_lokasyonlar = mysqli_query($conn, $sql_lokasyonlar);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Lokasyon Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary">← Ana Sayfaya Dön</a>
            <?php if ($duzenle_mod): ?>
                <a href="lokasyon_ekle.php" class="btn btn-primary">Yeni Lokasyon Ekle</a>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title h3"><?php echo $duzenle_mod ? 'Lokasyon Düzenle' : 'Yeni Lokasyon Ekle'; ?></h1>
                    </div>
                    <div class="card-body">
                        <?php echo $mesaj; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="lokasyon_adi" class="form-label">Lokasyon Adı</label>
                                <input type="text" class="form-control" id="lokasyon_adi" name="lokasyon_adi" 
                                    value="<?php echo $duzenle_mod ? $lokasyon['lokasyon_adi'] : (isset($_POST['lokasyon_adi']) ? $_POST['lokasyon_adi'] : ''); ?>" 
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $duzenle_mod ? 'Güncelle' : 'Kaydet'; ?>
                            </button>
                            <?php if ($duzenle_mod): ?>
                                <a href="lokasyon_ekle.php" class="btn btn-secondary">İptal</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h3">Mevcut Lokasyonlar</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Lokasyon Adı</th>
                                        <th>Sunucu Sayısı</th>
                                        <th>Oluşturma Tarihi</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    mysqli_data_seek($result_lokasyonlar, 0);
                                    while ($row = mysqli_fetch_assoc($result_lokasyonlar)): 
                                    ?>
                                        <tr>
                                            <td><?php echo $row['lokasyon_adi']; ?></td>
                                            <td>
                                                <?php if ($row['sunucu_sayisi'] > 0): ?>
                                                    <span class="badge bg-info"><?php echo $row['sunucu_sayisi']; ?> sunucu</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sunucu yok</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($row['olusturma_tarihi'])); ?></td>
                                            <td>
                                                <a href="?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                                <?php if ($row['sunucu_sayisi'] == 0): ?>
                                                    <a href="lokasyon_sil.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Bu lokasyonu silmek istediğinize emin misiniz?')">Sil</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 