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

// Sanal sunucuları listele
$sql = "SELECT * FROM sanal_sunucular WHERE fiziksel_sunucu_id = '$fiziksel_id' ORDER BY sunucu_adi";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sanal Sunucular - <?php echo $fiziksel_sunucu['sunucu_adi']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary">← Fiziksel Sunuculara Dön</a>
        </div>
        <h1 class="mb-4">Sanal Sunucular - <?php echo $fiziksel_sunucu['sunucu_adi']; ?></h1>
        <div class="mb-3">
            <a href="sanal_sunucu_ekle.php?fiziksel_id=<?php echo $fiziksel_id; ?>" class="btn btn-primary">Yeni Sanal Sunucu Ekle</a>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sunucu Adı</th>
                    <th>IP Adresi</th>
                    <th>RAM</th>
                    <th>CPU</th>
                    <th>Disk</th>
                    <th>Oluşturma Tarihi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['sunucu_adi'] . "</td>";
                        echo "<td>" . $row['ip_adresi'] . "</td>";
                        echo "<td>" . $row['ram'] . "</td>";
                        echo "<td>" . $row['cpu'] . "</td>";
                        echo "<td>" . $row['disk'] . "</td>";
                        echo "<td>" . $row['olusturma_tarihi'] . "</td>";
                        echo "<td>
                                <a href='sanal_sunucu_detay.php?id=" . $row['id'] . "' class='btn btn-info btn-sm'>Detay</a>
                                <a href='sanal_sunucu_duzenle.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Düzenle</a>
                                <a href='sanal_sunucu_sil.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Emin misiniz?\")'>Sil</a>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>Bu fiziksel sunucuya bağlı sanal sunucu bulunmamaktadır.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 