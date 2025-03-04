<?php
/**
 * @author A. Kerem Gök
 */
require_once 'config/database.php';

$sql = "SELECT * FROM fiziksel_sunucular ORDER BY sunucu_adi";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sunucu Takip Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Fiziksel Sunucular</h1>
        <div class="mb-3">
            <a href="fiziksel_sunucu_ekle.php" class="btn btn-primary">Yeni Fiziksel Sunucu Ekle</a>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sunucu Adı</th>
                    <th>IP Adresi</th>
                    <th>Lokasyon</th>
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
                        echo "<td>" . $row['lokasyon'] . "</td>";
                        echo "<td>" . $row['olusturma_tarihi'] . "</td>";
                        echo "<td>
                                <a href='sanal_sunucular.php?fiziksel_id=" . $row['id'] . "' class='btn btn-info btn-sm'>Sanal Sunucular</a>
                                <a href='fiziksel_sunucu_duzenle.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Düzenle</a>
                                <a href='fiziksel_sunucu_sil.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Emin misiniz?\")'>Sil</a>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>Henüz fiziksel sunucu eklenmemiş.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 