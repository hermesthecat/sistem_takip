<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

if (!isset($_GET['fiziksel_id']) || empty($_GET['fiziksel_id'])) {
    header('Location: index.php');
    exit;
}

$fiziksel_id = mysqli_real_escape_string($conn, $_GET['fiziksel_id']);

// Fiziksel sunucu bilgilerini al
$sql_fiziksel = "SELECT * FROM fiziksel_sunucular WHERE id = '$fiziksel_id'";
$result_fiziksel = mysqli_query($conn, $sql_fiziksel);
$fiziksel_sunucu = mysqli_fetch_assoc($result_fiziksel);

if (!$fiziksel_sunucu) {
    header('Location: index.php');
    exit;
}

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sunucu_adi = mysqli_real_escape_string($conn, $_POST['sunucu_adi']);
    $ip_adresi = mysqli_real_escape_string($conn, $_POST['ip_adresi']);
    $ram = mysqli_real_escape_string($conn, $_POST['ram']);
    $cpu = mysqli_real_escape_string($conn, $_POST['cpu']);
    $disk = mysqli_real_escape_string($conn, $_POST['disk']);

    $sql = "INSERT INTO sanal_sunucular (fiziksel_sunucu_id, sunucu_adi, ip_adresi, ram, cpu, disk) 
            VALUES ('$fiziksel_id', '$sunucu_adi', '$ip_adresi', '$ram', '$cpu', '$disk')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: sanal_sunucular.php?fiziksel_id=$fiziksel_id");
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
    <title>Yeni Sanal Sunucu Ekle - <?php echo $fiziksel_sunucu['sunucu_adi']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="mb-3">
            <a href="sanal_sunucular.php?fiziksel_id=<?php echo $fiziksel_id; ?>" class="btn btn-secondary">← Sanal Sunuculara Dön</a>
        </div>
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Yeni Sanal Sunucu Ekle - <?php echo $fiziksel_sunucu['sunucu_adi']; ?></h1>
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
                        <label for="ram" class="form-label">RAM</label>
                        <input type="text" class="form-control" id="ram" name="ram" placeholder="Örn: 8GB" required>
                    </div>
                    <div class="mb-3">
                        <label for="cpu" class="form-label">CPU</label>
                        <input type="text" class="form-control" id="cpu" name="cpu" placeholder="Örn: 4 Core" required>
                    </div>
                    <div class="mb-3">
                        <label for="disk" class="form-label">Disk</label>
                        <input type="text" class="form-control" id="disk" name="disk" placeholder="Örn: 500GB" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 