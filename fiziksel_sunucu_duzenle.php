<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Sunucu bilgilerini al
$sql = "SELECT * FROM fiziksel_sunucular WHERE id = '$id'";
$result = mysqli_query($conn, $sql);
$sunucu = mysqli_fetch_assoc($result);

if (!$sunucu) {
    header('Location: index.php');
    exit;
}

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sunucu_adi = mysqli_real_escape_string($conn, $_POST['sunucu_adi']);
    $ip_adresi = mysqli_real_escape_string($conn, $_POST['ip_adresi']);
    $lokasyon = mysqli_real_escape_string($conn, $_POST['lokasyon']);

    $sql = "UPDATE fiziksel_sunucular SET 
            sunucu_adi = '$sunucu_adi',
            ip_adresi = '$ip_adresi',
            lokasyon = '$lokasyon'
            WHERE id = '$id'";
    
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
    <title>Fiziksel Sunucu Düzenle - <?php echo $sunucu['sunucu_adi']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary">← Fiziksel Sunuculara Dön</a>
        </div>
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Fiziksel Sunucu Düzenle - <?php echo $sunucu['sunucu_adi']; ?></h1>
            </div>
            <div class="card-body">
                <?php if ($mesaj): ?>
                    <div class="alert alert-danger"><?php echo $mesaj; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="sunucu_adi" class="form-label">Sunucu Adı</label>
                        <input type="text" class="form-control" id="sunucu_adi" name="sunucu_adi" value="<?php echo $sunucu['sunucu_adi']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ip_adresi" class="form-label">IP Adresi</label>
                        <input type="text" class="form-control" id="ip_adresi" name="ip_adresi" value="<?php echo $sunucu['ip_adresi']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="lokasyon" class="form-label">Lokasyon</label>
                        <input type="text" class="form-control" id="lokasyon" name="lokasyon" value="<?php echo $sunucu['lokasyon']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 