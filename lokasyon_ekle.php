<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lokasyon_adi = mysqli_real_escape_string($conn, $_POST['lokasyon_adi']);

    // Aynı isimde lokasyon var mı kontrol et
    $sql_kontrol = "SELECT id FROM lokasyonlar WHERE lokasyon_adi = '$lokasyon_adi'";
    $result_kontrol = mysqli_query($conn, $sql_kontrol);

    if (mysqli_num_rows($result_kontrol) > 0) {
        $mesaj = "Hata: Bu lokasyon zaten mevcut!";
    } else {
        $sql = "INSERT INTO lokasyonlar (lokasyon_adi) VALUES ('$lokasyon_adi')";
        
        if (mysqli_query($conn, $sql)) {
            header('Location: index.php');
            exit;
        } else {
            $mesaj = "Hata: " . mysqli_error($conn);
        }
    }
}

// Mevcut lokasyonları listele
$sql_lokasyonlar = "SELECT * FROM lokasyonlar ORDER BY lokasyon_adi";
$result_lokasyonlar = mysqli_query($conn, $sql_lokasyonlar);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Lokasyon Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary">← Ana Sayfaya Dön</a>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">Yeni Lokasyon Ekle</h1>
                    </div>
                    <div class="card-body">
                        <?php if ($mesaj): ?>
                            <div class="alert alert-danger"><?php echo $mesaj; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="lokasyon_adi" class="form-label">Lokasyon Adı</label>
                                <input type="text" class="form-control" id="lokasyon_adi" name="lokasyon_adi" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Kaydet</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Mevcut Lokasyonlar</h2>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php while ($lokasyon = mysqli_fetch_assoc($result_lokasyonlar)): ?>
                                <div class="list-group-item">
                                    <?php echo $lokasyon['lokasyon_adi']; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 