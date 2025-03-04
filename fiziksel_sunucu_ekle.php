<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sunucu_adi = mysqli_real_escape_string($conn, $_POST['sunucu_adi']);
    $ip_adresi = mysqli_real_escape_string($conn, $_POST['ip_adresi']);
    $lokasyon = mysqli_real_escape_string($conn, $_POST['lokasyon']);

    $sql = "INSERT INTO fiziksel_sunucular (sunucu_adi, ip_adresi, lokasyon) VALUES ('$sunucu_adi', '$ip_adresi', '$lokasyon')";
    
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
</head>
<body>
    <div class="container mt-5">
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
                    <div class="mb-3">
                        <label for="sunucu_adi" class="form-label">Sunucu Adı</label>
                        <input type="text" class="form-control" id="sunucu_adi" name="sunucu_adi" required>
                    </div>
                    <div class="mb-3">
                        <label for="ip_adresi" class="form-label">IP Adresi</label>
                        <input type="text" class="form-control" id="ip_adresi" name="ip_adresi" required>
                    </div>
                    <div class="mb-3">
                        <label for="lokasyon" class="form-label">Lokasyon</label>
                        <input type="text" class="form-control" id="lokasyon" name="lokasyon" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 