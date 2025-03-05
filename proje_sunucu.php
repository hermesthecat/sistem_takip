<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

$proje_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;

if (!$proje_id) {
    header('Location: proje_ekle.php');
    exit;
}

// Proje bilgilerini al
$sql_proje = "SELECT * FROM projeler WHERE id = '$proje_id'";
$result_proje = mysqli_query($conn, $sql_proje);
$proje = mysqli_fetch_assoc($result_proje);

if (!$proje) {
    header('Location: proje_ekle.php');
    exit;
}

// Fiziksel sunucuları al
$sql_fiziksel = "SELECT fs.*, l.lokasyon_adi 
                 FROM fiziksel_sunucular fs 
                 LEFT JOIN lokasyonlar l ON fs.lokasyon_id = l.id
                 WHERE fs.proje_id = '$proje_id'";
$result_fiziksel = mysqli_query($conn, $sql_fiziksel);

if (!$result_fiziksel) {
    die("Fiziksel sunucu sorgusu hatası: " . mysqli_error($conn));
}

// Sanal sunucuları al (projeye ait olanlar)
$sql_sanal = "SELECT ss.*, l.lokasyon_adi, l.id as lokasyon_id
              FROM sanal_sunucular ss 
              LEFT JOIN fiziksel_sunucular fs ON ss.fiziksel_sunucu_id = fs.id
              LEFT JOIN lokasyonlar l ON fs.lokasyon_id = l.id
              WHERE ss.proje_id = '$proje_id'";
$result_sanal = mysqli_query($conn, $sql_sanal);

if (!$result_sanal) {
    die("Sanal sunucu sorgusu hatası: " . mysqli_error($conn));
}

// Fiziksel sunucu bilgilerini ayrıca al (tüm fiziksel sunucular)
$sql_fiziksel_bilgi = "SELECT id, sunucu_adi, proje_id, lokasyon_id FROM fiziksel_sunucular";
$result_fiziksel_bilgi = mysqli_query($conn, $sql_fiziksel_bilgi);
$fiziksel_sunucular = [];
if ($result_fiziksel_bilgi) {
    while ($row = mysqli_fetch_assoc($result_fiziksel_bilgi)) {
        $fiziksel_sunucular[$row['id']] = $row;
    }
}

// Sunucu sayılarını kontrol et
$sanal_count = mysqli_num_rows($result_sanal);
$fiziksel_count = mysqli_num_rows($result_fiziksel);
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $proje['proje_adi'] . ' - Sunucular'; ?></title>
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
                <h2>Proje: <b><?php echo $proje['proje_adi']; ?></b> için Sunucu Listesi</h2>
                <hr>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30%">Sunucu Adı</th>
                                <th>IP Adresi</th>
                                <th>Lokasyon</th>
                                <th>Özellikler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $has_servers = false;

                            // Önce fiziksel sunucuları ve bağlı sanal sunucuları göster
                            if ($fiziksel_count > 0):
                                $has_servers = true;
                                while ($fiziksel = mysqli_fetch_assoc($result_fiziksel)):
                            ?>
                                    <tr class="table-primary bg-opacity-10">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-hdd-fill text-primary me-2"></i>
                                                <strong><?php echo htmlspecialchars($fiziksel['sunucu_adi']); ?></strong>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($fiziksel['ip_adresi']); ?></td>
                                        <td>
                                            <?php if ($fiziksel['lokasyon_adi']): ?>
                                                <a href="lokasyon_sunucu.php?id=<?php echo $fiziksel['lokasyon_id']; ?>">
                                                    <?php echo htmlspecialchars($fiziksel['lokasyon_adi']); ?>
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
                                    // Her fiziksel sunucuya bağlı sanal sunucuları göster (aynı projede olanlar)
                                    if ($sanal_count > 0) {
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
                                                        <?php if ($sanal['lokasyon_adi']): ?>
                                                            <a href="lokasyon_sunucu.php?id=<?php echo $sanal['lokasyon_id']; ?>">
                                                                <?php echo htmlspecialchars($sanal['lokasyon_adi']); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">Sanal Sunucu</span>
                                                        <span class="badge bg-secondary ms-1">
                                                            <?php echo htmlspecialchars($fiziksel['sunucu_adi']); ?> üzerinde
                                                        </span>
                                                    </td>
                                                </tr>
                                    <?php
                                            endif;
                                        endwhile;
                                    }
                                endwhile;
                            endif;

                            // Bağımsız sanal sunucuları göster (farklı projedeki fiziksel sunucuya bağlı olanlar)
                            if ($sanal_count > 0) {
                                mysqli_data_seek($result_sanal, 0);
                                $standalone_exists = false;

                                // Önce bağımsız sanal sunucu var mı kontrol et
                                while ($sanal = mysqli_fetch_assoc($result_sanal)) {
                                    if (
                                        !$sanal['fiziksel_sunucu_id'] ||
                                        (isset($fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]) &&
                                            $fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]['proje_id'] != $proje_id)
                                    ) {
                                        $standalone_exists = true;
                                        $has_servers = true;
                                        break;
                                    }
                                }

                                if ($standalone_exists) {
                                    mysqli_data_seek($result_sanal, 0);
                                    ?>
                                    <tr>
                                        <td colspan="4" class="table-secondary">
                                            <strong>Bağımsız Sanal Sunucular</strong>
                                        </td>
                                    </tr>
                                    <?php
                                    while ($sanal = mysqli_fetch_assoc($result_sanal)):
                                        if (
                                            !$sanal['fiziksel_sunucu_id'] ||
                                            (isset($fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]) &&
                                                $fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]['proje_id'] != $proje_id)
                                        ):
                                    ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-pc text-info me-2"></i>
                                                        <?php echo htmlspecialchars($sanal['sunucu_adi']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($sanal['ip_adresi']); ?></td>
                                                <td>
                                                    <?php if ($sanal['lokasyon_adi']): ?>
                                                        <a href="lokasyon_sunucu.php?id=<?php echo $sanal['lokasyon_id']; ?>">
                                                            <?php echo htmlspecialchars($sanal['lokasyon_adi']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">Sanal Sunucu</span>
                                                    <?php if (isset($fiziksel_sunucular[$sanal['fiziksel_sunucu_id']])): ?>
                                                        <span class="badge bg-secondary ms-1">
                                                            <?php echo htmlspecialchars($fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]['sunucu_adi']); ?> üzerinde
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                <?php
                                        endif;
                                    endwhile;
                                }
                            }

                            if (!$has_servers): ?>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        Bu projeye ait sunucu bulunmamaktadır.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="proje_ekle.php?id=<?php echo $proje_id; ?>" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Projeye Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>