<?php

/**
 * @author A. Kerem Gök
 */
require_once 'auth.php';
require_once 'config/database.php';

$mesaj = '';

// Lokasyonları getir
$sql_lokasyonlar = "SELECT * FROM lokasyonlar ORDER BY lokasyon_adi";
$result_lokasyonlar = mysqli_query($conn, $sql_lokasyonlar);

// Projeleri getir
$sql_projeler = "SELECT * FROM projeler WHERE durum = 'Aktif' ORDER BY proje_adi";
$result_projeler = mysqli_query($conn, $sql_projeler);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sunucu_adi = mysqli_real_escape_string($conn, $_POST['sunucu_adi']);
    $ip_adresi = mysqli_real_escape_string($conn, $_POST['ip_adresi']);
    $lokasyon_id = mysqli_real_escape_string($conn, $_POST['lokasyon_id']);
    $proje_id = isset($_POST['proje_id']) ? mysqli_real_escape_string($conn, $_POST['proje_id']) : 'NULL';
    $ram = mysqli_real_escape_string($conn, $_POST['ram']);
    $cpu = mysqli_real_escape_string($conn, $_POST['cpu']);
    $disk = mysqli_real_escape_string($conn, $_POST['disk']);

    $sql = "INSERT INTO fiziksel_sunucular (sunucu_adi, ip_adresi, lokasyon_id, proje_id, ram, cpu, disk) 
            VALUES ('$sunucu_adi', '$ip_adresi', '$lokasyon_id', $proje_id, '$ram', '$cpu', '$disk')";

    if (mysqli_query($conn, $sql)) {
        header('Location: index.php');
        exit;
    } else {
        $mesaj = "Hata: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Yeni Fiziksel Sunucu Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php require_once 'header.php'; ?>
    
    <div class="container">
        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary">← Fiziksel Sunuculara Dön</a>
        </div>
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Yeni Fiziksel Sunucu Ekle</h1>
            </div>
            <div class="card-body">
                <?php if ($mesaj): ?>
                    <div class="alert alert-danger"><?php echo $mesaj; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sunucu_adi" class="form-label">Sunucu Adı</label>
                                <input type="text" class="form-control" id="sunucu_adi" name="sunucu_adi" required>
                            </div>
                            <div class="mb-3">
                                <label for="ip_adresi" class="form-label">IP Adresi</label>
                                <input type="text" class="form-control" id="ip_adresi" name="ip_adresi" required>
                            </div>
                            <div class="mb-3">
                                <label for="lokasyon_id" class="form-label">Lokasyon</label>
                                <select class="form-select" id="lokasyon_id" name="lokasyon_id" required>
                                    <option value="">Lokasyon Seçin</option>
                                    <?php while ($lokasyon = mysqli_fetch_assoc($result_lokasyonlar)): ?>
                                        <option value="<?php echo $lokasyon['id']; ?>"><?php echo $lokasyon['lokasyon_adi']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="mt-2">
                                    <a href="lokasyon_ekle.php" class="btn btn-sm btn-outline-primary">Yeni Lokasyon Ekle</a>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="proje_id" class="form-label">Proje</label>
                                <select class="form-select" id="proje_id" name="proje_id">
                                    <option value="">Proje Seçin (Opsiyonel)</option>
                                    <?php while ($proje = mysqli_fetch_assoc($result_projeler)): ?>
                                        <option value="<?php echo $proje['id']; ?>">
                                            <?php echo $proje['proje_adi']; ?> (<?php echo $proje['proje_kodu']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="mt-2">
                                    <a href="proje_ekle.php" class="btn btn-sm btn-outline-success">Yeni Proje Ekle</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cpu" class="form-label">Çekirdek</label>
                                <input type="text" class="form-control" id="cpu" name="cpu" placeholder="Toplam Çekirdek Sayısı" required>
                            </div>
                            <div class="mb-3">
                                <label for="ram" class="form-label">Bellek</label>
                                <input type="text" class="form-control" id="ram" name="ram" placeholder="Bellek Kapasitesi" required>
                            </div>
                            <div class="mb-3">
                                <label for="disk" class="form-label">Disk</label>
                                <input type="text" class="form-control" id="disk" name="disk" placeholder="Toplam Disk Kapasitesi" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>