<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

$lokasyon_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;

if (!$lokasyon_id) {
    header('Location: lokasyon_ekle.php');
    exit;
}

// Lokasyon bilgilerini al
$sql_lokasyon = "SELECT * FROM lokasyonlar WHERE id = '$lokasyon_id'";
$result_lokasyon = mysqli_query($conn, $sql_lokasyon);
$lokasyon = mysqli_fetch_assoc($result_lokasyon);

if (!$lokasyon) {
    header('Location: lokasyon_ekle.php');
    exit;
}

// Fiziksel sunucuları al
$sql_fiziksel = "SELECT fs.*, p.proje_adi 
                 FROM fiziksel_sunucular fs 
                 LEFT JOIN projeler p ON fs.proje_id = p.id 
                 WHERE fs.lokasyon_id = '$lokasyon_id'";
$result_fiziksel = mysqli_query($conn, $sql_fiziksel);

// Sanal sunucuları al
$sql_sanal = "SELECT ss.*, fs.sunucu_adi as fiziksel_sunucu_adi, p.proje_adi 
              FROM sanal_sunucular ss 
              LEFT JOIN fiziksel_sunucular fs ON ss.fiziksel_sunucu_id = fs.id 
              LEFT JOIN projeler p ON ss.proje_id = p.id 
              WHERE fs.lokasyon_id = '$lokasyon_id' OR ss.lokasyon_id = '$lokasyon_id'";
$result_sanal = mysqli_query($conn, $sql_sanal);
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $lokasyon['lokasyon_adi'] . ' - Sunucular'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .tree-line {
            position: relative;
        }

        .tree-line::before {
            content: '';
            position: absolute;
            left: -15px;
            top: -14px;
            border-left: 2px solid #dee2e6;
            height: 100%;
            width: 1px;
        }

        .tree-line::after {
            content: '';
            position: absolute;
            left: -15px;
            top: 50%;
            width: 15px;
            height: 2px;
            background-color: #dee2e6;
        }

        .tree-indent {
            position: relative;
            margin-left: 30px;
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <h2><?php echo $lokasyon['lokasyon_adi']; ?> - Sunucular</h2>
                <hr>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30%">Sunucu Adı</th>
                                <th>IP Adresi</th>
                                <th>Proje</th>
                                <th>Özellikler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result_fiziksel) > 0): ?>
                                <?php while ($fiziksel = mysqli_fetch_assoc($result_fiziksel)): ?>
                                    <tr class="table-primary bg-opacity-10">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-hdd-fill text-primary me-2"></i>
                                                <strong><?php echo htmlspecialchars($fiziksel['sunucu_adi']); ?></strong>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($fiziksel['ip_adresi']); ?></td>
                                        <td>
                                            <?php if ($fiziksel['proje_adi']): ?>
                                                <a href="proje_sunucu.php?id=<?php echo $fiziksel['proje_id']; ?>">
                                                    <?php echo htmlspecialchars($fiziksel['proje_adi']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">Fiziksel Sunucu</span>
                                        </td>
                                    </tr>
                                    <?php
                                    // Her fiziksel sunucuya bağlı sanal sunucuları göster
                                    mysqli_data_seek($result_sanal, 0);
                                    while ($sanal = mysqli_fetch_assoc($result_sanal)):
                                        if ($sanal['fiziksel_sunucu_id'] == $fiziksel['id']):
                                    ?>
                                            <tr class="table-light">
                                                <td>
                                                    <div class="tree-indent">
                                                        <div class="d-flex align-items-center tree-line">
                                                            <i class="bi bi-pc text-info me-2"></i>
                                                            <?php echo htmlspecialchars($sanal['sunucu_adi']); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($sanal['ip_adresi']); ?></td>
                                                <td>
                                                    <?php if ($sanal['proje_adi']): ?>
                                                        <a href="proje_sunucu.php?id=<?php echo $sanal['proje_id']; ?>">
                                                            <?php echo htmlspecialchars($sanal['proje_adi']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">Sanal Sunucu</span>
                                                </td>
                                            </tr>
                                    <?php
                                        endif;
                                    endwhile;
                                    ?>
                                <?php endwhile; ?>
                            <?php endif; ?>

                            <?php
                            // Bağımsız sanal sunucuları göster
                            mysqli_data_seek($result_sanal, 0);
                            $standalone_shown = false;
                            while ($sanal = mysqli_fetch_assoc($result_sanal)):
                                if (!$sanal['fiziksel_sunucu_id']):
                                    if (!$standalone_shown):
                                        $standalone_shown = true;
                            ?>
                                        <tr>
                                            <td colspan="4" class="table-secondary">
                                                <strong>Bağımsız Sanal Sunucular</strong>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-pc text-info me-2"></i>
                                                <?php echo htmlspecialchars($sanal['sunucu_adi']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($sanal['ip_adresi']); ?></td>
                                        <td>
                                            <?php if ($sanal['proje_adi']): ?>
                                                <a href="proje_sunucu.php?id=<?php echo $sanal['proje_id']; ?>">
                                                    <?php echo htmlspecialchars($sanal['proje_adi']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">Sanal Sunucu</span>
                                        </td>
                                    </tr>
                            <?php endif;
                            endwhile; ?>

                            <?php if (mysqli_num_rows($result_fiziksel) == 0 && mysqli_num_rows($result_sanal) == 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        Bu lokasyona ait sunucu bulunmamaktadır.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="lokasyonlar.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Lokasyonlara Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>