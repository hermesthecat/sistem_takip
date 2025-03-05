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
    header('Location: projeler.php');
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
$sql_fiziksel = "SELECT * FROM fiziksel_sunucular WHERE proje_id = '$proje_id'";
$result_fiziksel = mysqli_query($conn, $sql_fiziksel);

// Sanal sunucuları al
$sql_sanal = "SELECT ss.*, fs.sunucu_adi as fiziksel_sunucu_adi 
              FROM sanal_sunucular ss 
              LEFT JOIN fiziksel_sunucular fs ON ss.fiziksel_sunucu_id = fs.id 
              WHERE ss.proje_id = '$proje_id'";
$result_sanal = mysqli_query($conn, $sql_sanal);
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $proje['proje_adi'] . ' - Sunucular'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .tree-container {
            margin-left: 20px;
        }

        .tree-item {
            margin: 10px 0;
        }

        .virtual-server {
            margin-left: 40px;
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <h2><?php echo $proje['proje_adi']; ?> - Sunucular</h2>
                <hr>

                <div class="tree-container">
                    <?php if (mysqli_num_rows($result_fiziksel) > 0): ?>
                        <?php while ($fiziksel = mysqli_fetch_assoc($result_fiziksel)): ?>
                            <div class="tree-item">
                                <i class="bi bi-hdd-fill"></i>
                                <strong><?php echo htmlspecialchars($fiziksel['sunucu_adi']); ?></strong>
                                (<?php echo htmlspecialchars($fiziksel['ip_adresi']); ?>)

                                <?php
                                // Her fiziksel sunucuya bağlı sanal sunucuları göster
                                mysqli_data_seek($result_sanal, 0);
                                $has_virtual = false;
                                while ($sanal = mysqli_fetch_assoc($result_sanal)) {
                                    if ($sanal['fiziksel_sunucu_id'] == $fiziksel['id']) {
                                        if (!$has_virtual) {
                                            echo '<div class="virtual-server">';
                                            $has_virtual = true;
                                        }
                                        echo '<div>';
                                        echo '<i class="bi bi-pc"></i> ';
                                        echo htmlspecialchars($sanal['sunucu_adi']);
                                        if ($sanal['ip_adresi']) {
                                            echo ' (' . htmlspecialchars($sanal['ip_adresi']) . ')';
                                        }
                                        echo '</div>';
                                    }
                                }
                                if ($has_virtual) {
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Bu projeye ait fiziksel sunucu bulunmamaktadır.</p>
                    <?php endif; ?>

                    <?php
                    // Herhangi bir fiziksel sunucuya bağlı olmayan sanal sunucuları göster
                    mysqli_data_seek($result_sanal, 0);
                    $standalone_virtual = false;
                    while ($sanal = mysqli_fetch_assoc($result_sanal)) {
                        if (!$sanal['fiziksel_sunucu_id']) {
                            if (!$standalone_virtual) {
                                echo '<div class="tree-item">';
                                echo '<h5>Bağımsız Sanal Sunucular</h5>';
                                $standalone_virtual = true;
                            }
                            echo '<div class="virtual-server">';
                            echo '<i class="bi bi-pc"></i> ';
                            echo htmlspecialchars($sanal['sunucu_adi']);
                            if ($sanal['ip_adresi']) {
                                echo ' (' . htmlspecialchars($sanal['ip_adresi']) . ')';
                            }
                            echo '</div>';
                        }
                    }
                    if ($standalone_virtual) {
                        echo '</div>';
                    }
                    ?>
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