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

if (!$result_fiziksel) {
    die($language->get('physical_server_query_error', ['error' => mysqli_error($conn)]));
}

// Sanal sunucuları al
$sql_sanal = "SELECT ss.*, p.proje_adi, fs.lokasyon_id 
              FROM sanal_sunucular ss 
              LEFT JOIN projeler p ON ss.proje_id = p.id
              LEFT JOIN fiziksel_sunucular fs ON ss.fiziksel_sunucu_id = fs.id
              WHERE (fs.lokasyon_id = '$lokasyon_id' OR ss.fiziksel_sunucu_id IS NULL) AND ss.durum = '1'";
$result_sanal = mysqli_query($conn, $sql_sanal);

if (!$result_sanal) {
    die($language->get('virtual_server_query_error', ['error' => mysqli_error($conn)]));
}

// Fiziksel sunucu bilgilerini ayrıca al (tüm fiziksel sunucular)
$sql_fiziksel_bilgi = "SELECT id, sunucu_adi, lokasyon_id FROM fiziksel_sunucular";
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
    <title><?php echo $lokasyon['lokasyon_adi'] . ' - ' . $language->get('servers'); ?></title>
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
                <h2><?php echo $language->get('location'); ?>: <b><?php echo $lokasyon['lokasyon_adi']; ?></b> <?php echo $language->get('server_list_for'); ?></h2>
                <hr>

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-responsive">
                        <thead>
                            <tr>
                                <th style="width: 30%"><?php echo $language->get('server_name'); ?></th>
                                <th><?php echo $language->get('ip_address'); ?></th>
                                <th><?php echo $language->get('project'); ?></th>
                                <th><?php echo $language->get('properties'); ?></th>
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
                                            <?php if ($fiziksel['proje_adi']): ?>
                                                <a href="proje_sunucu.php?id=<?php echo $fiziksel['proje_id']; ?>">
                                                    <?php echo htmlspecialchars($fiziksel['proje_adi']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $language->get('physical_server'); ?></span>
                                        </td>
                                    </tr>
                                    <?php
                                    // Her fiziksel sunucuya bağlı sanal sunucuları göster (aynı lokasyonda olanlar)
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
                                                        <?php if ($sanal['proje_adi']): ?>
                                                            <a href="proje_sunucu.php?id=<?php echo $sanal['proje_id']; ?>">
                                                                <?php echo htmlspecialchars($sanal['proje_adi']); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $language->get('virtual_server'); ?></span>
                                                        <span class="badge bg-secondary ms-1">
                                                            <?php
                                                            $fiziksel_sunucu = $fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]['sunucu_adi'];
                                                            $fiziksel_sunucu_adi = str_replace('{server_name}', $fiziksel_sunucu, $language->get('on_server'));
                                                            echo htmlspecialchars($fiziksel_sunucu_adi);
                                                            ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                    <?php
                                            endif;
                                        endwhile;
                                    }
                                endwhile;
                            endif;

                            // Bağımsız sanal sunucuları göster (farklı lokasyondaki fiziksel sunucuya bağlı olanlar)
                            if ($sanal_count > 0) {
                                mysqli_data_seek($result_sanal, 0);
                                $standalone_exists = false;

                                // Önce bağımsız sanal sunucu var mı kontrol et
                                while ($sanal = mysqli_fetch_assoc($result_sanal)) {
                                    if (
                                        !$sanal['fiziksel_sunucu_id'] ||
                                        (isset($fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]) &&
                                            $fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]['lokasyon_id'] != $lokasyon_id)
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
                                            <strong><?php echo $language->get('standalone_virtual_servers'); ?></strong>
                                        </td>
                                    </tr>
                                    <?php
                                    while ($sanal = mysqli_fetch_assoc($result_sanal)):
                                        if (
                                            !$sanal['fiziksel_sunucu_id'] ||
                                            (isset($fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]) &&
                                                $fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]['lokasyon_id'] != $lokasyon_id)
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
                                                    <?php if ($sanal['proje_adi']): ?>
                                                        <a href="proje_sunucu.php?id=<?php echo $sanal['proje_id']; ?>">
                                                            <?php echo htmlspecialchars($sanal['proje_adi']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $language->get('virtual_server'); ?></span>
                                                    <?php if (isset($fiziksel_sunucular[$sanal['fiziksel_sunucu_id']])): ?>
                                                        <span class="badge bg-secondary ms-1">
                                                            <?php
                                                            $fiziksel_sunucu = $fiziksel_sunucular[$sanal['fiziksel_sunucu_id']]['sunucu_adi'];
                                                            $fiziksel_sunucu_adi = str_replace('{server_name}', $fiziksel_sunucu, $language->get('on_server'));
                                                            echo htmlspecialchars($fiziksel_sunucu_adi); ?>
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
                                    <td colspan="4" class="text-center">
                                        <?php echo $language->get('no_servers_for_location'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="lokasyonlar.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> <?php echo $language->get('back_to_locations'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>