<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

$mesaj = '';
$duzenle_mod = false;
$proje = null;

// Düzenleme modu kontrolü
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM projeler WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $proje = mysqli_fetch_assoc($result);
    
    if ($proje) {
        $duzenle_mod = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $proje_adi = mysqli_real_escape_string($conn, $_POST['proje_adi']);
    $proje_kodu = mysqli_real_escape_string($conn, $_POST['proje_kodu']);
    $aciklama = mysqli_real_escape_string($conn, $_POST['aciklama']);
    $durum = mysqli_real_escape_string($conn, $_POST['durum']);

    if ($duzenle_mod) {
        // Güncelleme işlemi
        $id = mysqli_real_escape_string($conn, $_GET['id']);
        
        // Proje kodu kontrolü (kendi kodu hariç)
        $sql_kontrol = "SELECT id FROM projeler WHERE proje_kodu = '$proje_kodu' AND id != '$id'";
        $result_kontrol = mysqli_query($conn, $sql_kontrol);

        if (mysqli_num_rows($result_kontrol) > 0) {
            $mesaj = "Hata: Bu proje kodu zaten kullanılıyor!";
        } else {
            $sql = "UPDATE projeler SET 
                    proje_adi = '$proje_adi',
                    proje_kodu = '$proje_kodu',
                    aciklama = '$aciklama',
                    durum = '$durum'
                    WHERE id = '$id'";
            
            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>Proje başarıyla güncellendi.</div>";
                // Güncel veriyi al
                $sql = "SELECT * FROM projeler WHERE id = '$id'";
                $result = mysqli_query($conn, $sql);
                $proje = mysqli_fetch_assoc($result);
            } else {
                $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
            }
        }
    } else {
        // Yeni proje ekleme
        $sql_kontrol = "SELECT id FROM projeler WHERE proje_kodu = '$proje_kodu'";
        $result_kontrol = mysqli_query($conn, $sql_kontrol);

        if (mysqli_num_rows($result_kontrol) > 0) {
            $mesaj = "<div class='alert alert-danger'>Hata: Bu proje kodu zaten kullanılıyor!</div>";
        } else {
            $sql = "INSERT INTO projeler (proje_adi, proje_kodu, aciklama, durum) VALUES ('$proje_adi', '$proje_kodu', '$aciklama', '$durum')";
            
            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>Yeni proje başarıyla eklendi.</div>";
                $_POST = array(); // Formu temizle
            } else {
                $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

// Mevcut projeleri listele
$sql_projeler = "SELECT p.*, 
                (SELECT COUNT(*) FROM fiziksel_sunucular WHERE proje_id = p.id) as fiziksel_sayi,
                (SELECT COUNT(*) FROM sanal_sunucular WHERE proje_id = p.id) as sanal_sayi
                FROM projeler p 
                ORDER BY p.proje_adi";
$result_projeler = mysqli_query($conn, $sql_projeler);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Proje Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary">← Ana Sayfaya Dön</a>
            <?php if ($duzenle_mod): ?>
                <a href="proje_ekle.php" class="btn btn-primary">Yeni Proje Ekle</a>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title h3"><?php echo $duzenle_mod ? 'Proje Düzenle' : 'Yeni Proje Ekle'; ?></h1>
                    </div>
                    <div class="card-body">
                        <?php echo $mesaj; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="proje_adi" class="form-label">Proje Adı</label>
                                <input type="text" class="form-control" id="proje_adi" name="proje_adi" 
                                    value="<?php echo $duzenle_mod ? $proje['proje_adi'] : (isset($_POST['proje_adi']) ? $_POST['proje_adi'] : ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="proje_kodu" class="form-label">Proje Kodu</label>
                                <input type="text" class="form-control" id="proje_kodu" name="proje_kodu" 
                                    value="<?php echo $duzenle_mod ? $proje['proje_kodu'] : (isset($_POST['proje_kodu']) ? $_POST['proje_kodu'] : ''); ?>"
                                    placeholder="Örn: PRJ-2024" required>
                            </div>
                            <div class="mb-3">
                                <label for="aciklama" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?php 
                                    echo $duzenle_mod ? $proje['aciklama'] : (isset($_POST['aciklama']) ? $_POST['aciklama'] : ''); 
                                ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="durum" class="form-label">Durum</label>
                                <select class="form-select" id="durum" name="durum" required>
                                    <?php 
                                    $durumlar = array('Aktif', 'Pasif', 'Tamamlandı');
                                    foreach ($durumlar as $d) {
                                        $selected = '';
                                        if ($duzenle_mod && $proje['durum'] == $d) {
                                            $selected = 'selected';
                                        } elseif (!$duzenle_mod && isset($_POST['durum']) && $_POST['durum'] == $d) {
                                            $selected = 'selected';
                                        } elseif (!$duzenle_mod && !isset($_POST['durum']) && $d == 'Aktif') {
                                            $selected = 'selected';
                                        }
                                        echo "<option value='$d' $selected>$d</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $duzenle_mod ? 'Güncelle' : 'Kaydet'; ?>
                            </button>
                            <?php if ($duzenle_mod): ?>
                                <a href="proje_ekle.php" class="btn btn-secondary">İptal</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h3">Mevcut Projeler</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Proje Adı</th>
                                        <th>Proje Kodu</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    mysqli_data_seek($result_projeler, 0);
                                    while ($row = mysqli_fetch_assoc($result_projeler)): 
                                    ?>
                                        <tr>
                                            <td>
                                                <?php echo $row['proje_adi']; ?>
                                                <?php if ($row['aciklama']): ?>
                                                    <small class="d-block text-muted"><?php echo $row['aciklama']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><code><?php echo $row['proje_kodu']; ?></code></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $row['durum'] == 'Aktif' ? 'success' : 
                                                        ($row['durum'] == 'Pasif' ? 'warning' : 'secondary'); 
                                                ?>"><?php echo $row['durum']; ?></span>
                                                <?php if ($row['fiziksel_sayi'] > 0 || $row['sanal_sayi'] > 0): ?>
                                                    <div class="mt-1">
                                                        <?php if ($row['fiziksel_sayi'] > 0): ?>
                                                            <span class="badge bg-info"><?php echo $row['fiziksel_sayi']; ?> fiziksel</span>
                                                        <?php endif; ?>
                                                        <?php if ($row['sanal_sayi'] > 0): ?>
                                                            <span class="badge bg-info"><?php echo $row['sanal_sayi']; ?> sanal</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                                <?php if ($row['fiziksel_sayi'] == 0 && $row['sanal_sayi'] == 0): ?>
                                                    <a href="proje_sil.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Bu projeyi silmek istediğinize emin misiniz?')">Sil</a>
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