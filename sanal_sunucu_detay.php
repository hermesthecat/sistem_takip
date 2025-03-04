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

// Sanal sunucu ve bağlı olduğu fiziksel sunucu bilgilerini al
$sql = "SELECT ss.*, fs.sunucu_adi as fiziksel_sunucu_adi, fs.ip_adresi as fiziksel_ip_adresi 
        FROM sanal_sunucular ss 
        LEFT JOIN fiziksel_sunucular fs ON ss.fiziksel_sunucu_id = fs.id 
        WHERE ss.id = '$id'";
$result = mysqli_query($conn, $sql);
$sunucu = mysqli_fetch_assoc($result);

if (!$sunucu) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sanal Sunucu Detayı - <?php echo $sunucu['sunucu_adi']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="mb-3">
            <a href="sanal_sunucular.php?fiziksel_id=<?php echo $sunucu['fiziksel_sunucu_id']; ?>" class="btn btn-secondary">← Sanal Sunuculara Dön</a>
        </div>
        <div class="card">
            <div class="card-header">
                <h1 class="card-title"><?php echo $sunucu['sunucu_adi']; ?> - Detaylar</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Sanal Sunucu Bilgileri</h3>
                        <table class="table">
                            <tr>
                                <th>Sunucu ID:</th>
                                <td><?php echo $sunucu['id']; ?></td>
                            </tr>
                            <tr>
                                <th>Sunucu Adı:</th>
                                <td><?php echo $sunucu['sunucu_adi']; ?></td>
                            </tr>
                            <tr>
                                <th>IP Adresi:</th>
                                <td><?php echo $sunucu['ip_adresi']; ?></td>
                            </tr>
                            <tr>
                                <th>RAM:</th>
                                <td><?php echo $sunucu['ram']; ?></td>
                            </tr>
                            <tr>
                                <th>CPU:</th>
                                <td><?php echo $sunucu['cpu']; ?></td>
                            </tr>
                            <tr>
                                <th>Disk:</th>
                                <td><?php echo $sunucu['disk']; ?></td>
                            </tr>
                            <tr>
                                <th>Oluşturma Tarihi:</th>
                                <td><?php echo $sunucu['olusturma_tarihi']; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h3>Fiziksel Sunucu Bilgileri</h3>
                        <table class="table">
                            <tr>
                                <th>Sunucu Adı:</th>
                                <td><?php echo $sunucu['fiziksel_sunucu_adi']; ?></td>
                            </tr>
                            <tr>
                                <th>IP Adresi:</th>
                                <td><?php echo $sunucu['fiziksel_ip_adresi']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="sanal_sunucu_duzenle.php?id=<?php echo $id; ?>" class="btn btn-warning">Düzenle</a>
                <a href="sanal_sunucu_sil.php?id=<?php echo $id; ?>" class="btn btn-danger" onclick="return confirm('Emin misiniz?')">Sil</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 