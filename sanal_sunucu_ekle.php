<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

if (!isset($_GET['fiziksel_id']) || empty($_GET['fiziksel_id'])) {
    header('Location: index.php?hata=' . urlencode($language->get('error_physical_server_id')));
    exit;
}

$fiziksel_id = mysqli_real_escape_string($conn, $_GET['fiziksel_id']);
$mesaj = '';

// Fiziksel sunucu bilgilerini al
$sql = "SELECT fs.*, p.id as varsayilan_proje_id 
        FROM fiziksel_sunucular fs 
        LEFT JOIN projeler p ON fs.proje_id = p.id 
        WHERE fs.id = '$fiziksel_id'";
$result = mysqli_query($conn, $sql);
$fiziksel_sunucu = mysqli_fetch_assoc($result);

if (!$fiziksel_sunucu) {
    header('Location: index.php?hata=' . urlencode($language->get('error_physical_server_not_found')));
    exit;
}

// Fiziksel sunucunun kaynak kullanımını kontrol et
$sql = "SELECT 
        COALESCE(SUM(CAST(REGEXP_REPLACE(cpu, '[^0-9]', '') AS UNSIGNED)), 0) as toplam_cpu,
        COALESCE(SUM(CAST(REGEXP_REPLACE(ram, '[^0-9]', '') AS UNSIGNED)), 0) as toplam_ram,
        COALESCE(SUM(CASE 
            WHEN disk LIKE '%TB%' THEN CAST(REGEXP_REPLACE(disk, '[^0-9]', '') AS UNSIGNED) * 1024
            ELSE CAST(REGEXP_REPLACE(disk, '[^0-9]', '') AS UNSIGNED)
        END), 0) as toplam_disk
        FROM sanal_sunucular 
        WHERE fiziksel_sunucu_id = '$fiziksel_id'";
$result = mysqli_query($conn, $sql);
$kaynak_kullanim = mysqli_fetch_assoc($result);

// Fiziksel sunucunun toplam kaynaklarını hesapla
$fiziksel_cpu = intval(preg_replace('/[^0-9]/', '', $fiziksel_sunucu['cpu']));
$fiziksel_ram = intval(preg_replace('/[^0-9]/', '', $fiziksel_sunucu['ram']));
$fiziksel_disk = $fiziksel_sunucu['disk'];
if (stripos($fiziksel_disk, 'TB') !== false) {
    $fiziksel_disk = intval(preg_replace('/[^0-9]/', '', $fiziksel_disk)) * 1024;
} else {
    $fiziksel_disk = intval(preg_replace('/[^0-9]/', '', $fiziksel_disk));
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sunucu_adi = mysqli_real_escape_string($conn, $_POST['sunucu_adi']);
    $ip_adresi = mysqli_real_escape_string($conn, $_POST['ip_adresi']);
    $ram = mysqli_real_escape_string($conn, $_POST['ram']);
    $cpu = mysqli_real_escape_string($conn, $_POST['cpu']);
    $disk = mysqli_real_escape_string($conn, $_POST['disk']);
    $proje_id = isset($_POST['proje_id']) ? mysqli_real_escape_string($conn, $_POST['proje_id']) : 'NULL';

    // IP adresi kontrolü
    $ip_kontrol = "SELECT COUNT(*) as sayi FROM sanal_sunucular WHERE ip_adresi = '$ip_adresi'";
    $ip_result = mysqli_query($conn, $ip_kontrol);
    $ip_row = mysqli_fetch_assoc($ip_result);

    if ($ip_row['sayi'] > 0) {
        $mesaj = "<div class='alert alert-danger'>" . $language->get('error_ip_in_use') . "</div>";
    } else {
        $sql = "INSERT INTO sanal_sunucular (fiziksel_sunucu_id, sunucu_adi, ip_adresi, ram, cpu, disk, proje_id) 
                VALUES ('$fiziksel_id', '$sunucu_adi', '$ip_adresi', '$ram', '$cpu', '$disk', $proje_id)";

        if (mysqli_query($conn, $sql)) {
            header('Location: sanal_sunucular.php?fiziksel_id=' . $fiziksel_id .
                '&basari=' . urlencode($language->get('success_virtual_server_added')));
            exit;
        } else {
            $mesaj = "<div class='alert alert-danger'>" . $language->get('error_adding_virtual_server', ['error' => mysqli_error($conn)]) . "</div>";
        }
    }
}

// Projeleri getir
$sql = "SELECT * FROM projeler WHERE durum = 'Aktif' ORDER BY proje_adi";
$projeler = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $language->get('add_virtual_server'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>

    <div class="container">
        <div class="mb-3">
            <a href="sanal_sunucular.php?fiziksel_id=<?php echo $fiziksel_id; ?>" class="btn btn-secondary">
                ← <?php echo $language->get('back_to_virtual_servers'); ?>
            </a>
        </div>

        <?php echo $mesaj; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title h5 mb-0">
                    <?php echo $language->get('add_virtual_server'); ?>
                    <small class="text-muted">(<?php echo $fiziksel_sunucu['sunucu_adi']; ?>)</small>
                </h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading"><?php echo $language->get('resource_usage'); ?></h6>
                    <div class="row">
                        <div class="col-md-4">
                            <small>CPU: <?php echo $kaynak_kullanim['toplam_cpu']; ?>/<?php echo $fiziksel_cpu; ?> Core</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar <?php echo ($kaynak_kullanim['toplam_cpu'] / $fiziksel_cpu * 100 > 80) ? 'bg-danger' : ''; ?>"
                                    role="progressbar"
                                    style="width: <?php echo ($fiziksel_cpu > 0) ? ($kaynak_kullanim['toplam_cpu'] / $fiziksel_cpu * 100) : 0; ?>%">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small>RAM: <?php echo $kaynak_kullanim['toplam_ram']; ?>/<?php echo $fiziksel_ram; ?> GB</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar <?php echo ($kaynak_kullanim['toplam_ram'] / $fiziksel_ram * 100 > 80) ? 'bg-danger' : ''; ?>"
                                    role="progressbar"
                                    style="width: <?php echo ($fiziksel_ram > 0) ? ($kaynak_kullanim['toplam_ram'] / $fiziksel_ram * 100) : 0; ?>%">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small>Disk: <?php echo $kaynak_kullanim['toplam_disk']; ?>/<?php echo $fiziksel_disk; ?> GB</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar <?php echo ($kaynak_kullanim['toplam_disk'] / $fiziksel_disk * 100 > 80) ? 'bg-danger' : ''; ?>"
                                    role="progressbar"
                                    style="width: <?php echo ($fiziksel_disk > 0) ? ($kaynak_kullanim['toplam_disk'] / $fiziksel_disk * 100) : 0; ?>%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" class="row" id="sanal_sunucu_form">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sunucu_adi" class="form-label"><?php echo $language->get('server_name'); ?></label>
                            <input type="text" class="form-control" id="sunucu_adi" name="sunucu_adi" required>
                        </div>
                        <div class="mb-3">
                            <label for="ip_adresi" class="form-label"><?php echo $language->get('ip_address'); ?></label>
                            <input type="text" class="form-control" id="ip_adresi" name="ip_adresi"
                                pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"
                                title="<?php echo $language->get('enter_valid_ipv4'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="proje_id" class="form-label"><?php echo $language->get('project'); ?></label>
                            <select class="form-select" id="proje_id" name="proje_id">
                                <option value=""><?php echo $language->get('select_project'); ?></option>
                                <?php while ($proje = mysqli_fetch_assoc($projeler)): ?>
                                    <option value="<?php echo $proje['id']; ?>"
                                        <?php echo ($proje['id'] == $fiziksel_sunucu['varsayilan_proje_id']) ? 'selected' : ''; ?>>
                                        <?php echo $language->get('project_info', ['project_name' => $proje['proje_adi'], 'project_code' => $proje['proje_kodu']]); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text"><?php echo $language->get('default_project_info'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="cpu" class="form-label"><?php echo $language->get('cpu_cores'); ?></label>
                            <input type="text" class="form-control" id="cpu" name="cpu"
                                placeholder="<?php echo $language->get('cpu_placeholder'); ?>" required
                                pattern="^\d+\s*(?:core|cores|cpu|işlemci|çekirdek)?$"
                                title="<?php echo $language->get('enter_valid_number', ['example' => '4 Core']); ?>">
                            <div class="form-text">
                                <?php echo $language->get('remaining_resources', ['value' => ($fiziksel_cpu - $kaynak_kullanim['toplam_cpu']) . ' Core']); ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ram" class="form-label"><?php echo $language->get('memory'); ?></label>
                            <input type="text" class="form-control" id="ram" name="ram"
                                placeholder="<?php echo $language->get('ram_placeholder'); ?>" required
                                pattern="^\d+\s*(?:gb|g|gigabyte)?$"
                                title="<?php echo $language->get('enter_valid_number', ['example' => '8GB']); ?>">
                            <div class="form-text">
                                <?php echo $language->get('remaining_resources', ['value' => ($fiziksel_ram - $kaynak_kullanim['toplam_ram']) . ' GB']); ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="disk" class="form-label"><?php echo $language->get('disk'); ?></label>
                            <input type="text" class="form-control" id="disk" name="disk"
                                placeholder="<?php echo $language->get('disk_placeholder'); ?>" required
                                pattern="^\d+\s*(?:gb|g|tb|t)?$"
                                title="<?php echo $language->get('enter_valid_number', ['example' => '100GB']); ?>">
                            <div class="form-text">
                                <?php echo $language->get('remaining_resources', ['value' => ($fiziksel_disk - $kaynak_kullanim['toplam_disk']) . ' GB']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><?php echo $language->get('add_virtual_server_button'); ?></button>
                    </div>
                </form>

                <script>
                    document.getElementById('sanal_sunucu_form').addEventListener('submit', function(e) {
                        var cpu = parseInt(document.getElementById('cpu').value.replace(/[^0-9]/g, ''));
                        var ram = parseInt(document.getElementById('ram').value.replace(/[^0-9]/g, ''));
                        var disk = parseInt(document.getElementById('disk').value.replace(/[^0-9]/g, ''));

                        var kalanCpu = <?php echo $fiziksel_cpu - $kaynak_kullanim['toplam_cpu']; ?>;
                        var kalanRam = <?php echo $fiziksel_ram - $kaynak_kullanim['toplam_ram']; ?>;
                        var kalanDisk = <?php echo $fiziksel_disk - $kaynak_kullanim['toplam_disk']; ?>;

                        var hatalar = [];

                        if (cpu > kalanCpu) {
                            hatalar.push('<?php echo $language->get('error_cpu_limit', ['value' => "' + kalanCpu + '"]); ?>');
                        }

                        if (ram > kalanRam) {
                            hatalar.push('<?php echo $language->get('error_ram_limit', ['value' => "' + kalanRam + '"]); ?>');
                        }

                        if (disk > kalanDisk) {
                            hatalar.push('<?php echo $language->get('error_disk_limit', ['value' => "' + kalanDisk + '"]); ?>');
                        }

                        if (hatalar.length > 0) {
                            e.preventDefault();
                            alert('<?php echo $language->get('resource_validation_error'); ?>\n\n' + hatalar.join('\n'));
                        }
                    });
                </script>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>