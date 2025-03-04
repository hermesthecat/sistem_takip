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
$sql = "SELECT * FROM sanal_sunucular WHERE id = '$id'";
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
    $ram = mysqli_real_escape_string($conn, $_POST['ram']);
    $cpu = mysqli_real_escape_string($conn, $_POST['cpu']);
    $disk = mysqli_real_escape_string($conn, $_POST['disk']);

    $sql = "UPDATE sanal_sunucular SET 
            sunucu_adi = '$sunucu_adi',
            ip_adresi = '$ip_adresi',
            ram = '$ram',
            cpu = '$cpu',
            disk = '$disk'
            WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: sanal_sunucular.php?fiziksel_id=" . $sunucu['fiziksel_sunucu_id']);
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
    <title>Sanal Sunucu Düzenle - <?php echo $sunucu['sunucu_adi']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="mb-3">
            <a href="sanal_sunucular.php?fiziksel_id=<?php echo $sunucu['fiziksel_sunucu_id']; ?>" class="btn btn-secondary">← Sanal Sunuculara Dön</a>
        </div>
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Sanal Sunucu Düzenle - <?php echo $sunucu['sunucu_adi']; ?></h1>
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
                        <label for="ram" class="form-label">RAM</label>
                        <input type="text" class="form-control" id="ram" name="ram" value="<?php echo $sunucu['ram']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="cpu" class="form-label">CPU</label>
                        <input type="text" class="form-control" id="cpu" name="cpu" value="<?php echo $sunucu['cpu']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="disk" class="form-label">Disk</label>
                        <input type="text" class="form-control" id="disk" name="disk" value="<?php echo $sunucu['disk']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 