<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

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
$sql = "SELECT * FROM sanal_sunucular WHERE fiziksel_sunucu_id = '$fiziksel_id' AND durum = 1 ORDER BY sunucu_adi";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php
            $title = str_replace('{server_name}', $fiziksel_sunucu['sunucu_adi'], $language->get('virtual_server_list_for'));
            echo $title;
            ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?php echo $language->get('virtual_server_list'); ?> <i>(<?php echo $fiziksel_sunucu['sunucu_adi']; ?>)</i></h1>
            <div>
                <a href="sanal_sunucu_ekle.php?fiziksel_id=<?php echo $fiziksel_id; ?>" class="btn btn-primary">
                    <?php echo $language->get('add_virtual_server_button'); ?>
                </a>
            </div>
        </div>

        <table class="table table-hover table-striped table-responsive">
            <thead>
                <tr>
                    <th><?php echo $language->get('virtual_server_id'); ?></th>
                    <th><?php echo $language->get('virtual_server_name'); ?></th>
                    <th><?php echo $language->get('virtual_server_ip'); ?></th>
                    <th><?php echo $language->get('virtual_server_memory'); ?></th>
                    <th><?php echo $language->get('virtual_server_cores'); ?></th>
                    <th><?php echo $language->get('virtual_server_disk'); ?></th>
                    <th class="text-end"><?php echo $language->get('virtual_server_actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['sunucu_adi']; ?></td>
                            <td><?php echo $row['ip_adresi']; ?></td>
                            <td><?php echo $row['ram']; ?></td>
                            <td><?php echo $row['cpu']; ?></td>
                            <td><?php echo $row['disk']; ?></td>
                            <td class="text-end">
                                <a href='sanal_sunucu_detay.php?id=<?php echo $row['id']; ?>' class='btn btn-info btn-sm'><?php echo $language->get('virtual_server_detail'); ?></a>
                                <a href='sanal_sunucu_duzenle.php?id=<?php echo $row['id']; ?>' class='btn btn-warning btn-sm'><?php echo $language->get('virtual_server_edit'); ?></a>
                                <?php if ($_SESSION['rol'] == 'admin') { ?>
                                    <a href='sanal_sunucu_sil.php?id=<?php echo $row['id']; ?>' class='btn btn-danger btn-sm' onclick='return confirm(\"" . $language->get(' confirm_delete_virtual_server') . "\" )'><?php echo $language->get('virtual_server_delete'); ?></a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="7" class="text-center"><?php echo $language->get('no_virtual_servers_for_physical'); ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>