<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?hata=' . urlencode($language->get('error_virtual_server_id')));
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$mesaj = '';

// Sanal sunucu bilgilerini al
$sql = "SELECT ss.*, fs.sunucu_adi as fiziksel_sunucu_adi, p.proje_adi, p.proje_kodu 
        FROM sanal_sunucular ss
        LEFT JOIN fiziksel_sunucular fs ON ss.fiziksel_sunucu_id = fs.id
        LEFT JOIN projeler p ON ss.proje_id = p.id
        WHERE ss.id = '$id'";
$result = mysqli_query($conn, $sql);
$sunucu = mysqli_fetch_assoc($result);

if (!$sunucu) {
    header('Location: index.php?hata=' . urlencode($language->get('error_virtual_server_not_found')));
    exit;
}

// Mevcut fiziksel sunucunun kaynak kullanımını kontrol et
$sql = "SELECT 
        COALESCE(SUM(CAST(REGEXP_REPLACE(cpu, '[^0-9]', '') AS UNSIGNED)), 0) as toplam_cpu,
        COALESCE(SUM(CAST(REGEXP_REPLACE(ram, '[^0-9]', '') AS UNSIGNED)), 0) as toplam_ram,
        COALESCE(SUM(CASE 
            WHEN disk LIKE '%TB%' THEN CAST(REGEXP_REPLACE(disk, '[^0-9]', '') AS UNSIGNED) * 1024
            ELSE CAST(REGEXP_REPLACE(disk, '[^0-9]', '') AS UNSIGNED)
        END), 0) as toplam_disk
        FROM sanal_sunucular 
        WHERE fiziksel_sunucu_id = '{$sunucu['fiziksel_sunucu_id']}'
        AND id != '$id'";
$result = mysqli_query($conn, $sql);
$mevcut_kaynak_kullanim = mysqli_fetch_assoc($result);

// Kullanılabilir fiziksel sunucuları getir
$sql = "SELECT fs.*, 
        COALESCE(SUM(CAST(REGEXP_REPLACE(ss.cpu, '[^0-9]', '') AS UNSIGNED)), 0) as kullanilan_cpu,
        COALESCE(SUM(CAST(REGEXP_REPLACE(ss.ram, '[^0-9]', '') AS UNSIGNED)), 0) as kullanilan_ram,
        COALESCE(SUM(CASE 
            WHEN ss.disk LIKE '%TB%' THEN CAST(REGEXP_REPLACE(ss.disk, '[^0-9]', '') AS UNSIGNED) * 1024
            ELSE CAST(REGEXP_REPLACE(ss.disk, '[^0-9]', '') AS UNSIGNED)
        END), 0) as kullanilan_disk,
        CAST(REGEXP_REPLACE(fs.cpu, '[^0-9]', '') AS UNSIGNED) as toplam_cpu,
        CAST(REGEXP_REPLACE(fs.ram, '[^0-9]', '') AS UNSIGNED) as toplam_ram,
        CASE 
            WHEN fs.disk LIKE '%TB%' THEN CAST(REGEXP_REPLACE(fs.disk, '[^0-9]', '') AS UNSIGNED) * 1024
            ELSE CAST(REGEXP_REPLACE(fs.disk, '[^0-9]', '') AS UNSIGNED)
        END as toplam_disk
        FROM fiziksel_sunucular fs
        LEFT JOIN sanal_sunucular ss ON fs.id = ss.fiziksel_sunucu_id AND ss.id != '$id'
        GROUP BY fs.id
        HAVING 
            (toplam_cpu - kullanilan_cpu) >= " . intval(preg_replace('/[^0-9]/', '', $sunucu['cpu'])) . " AND
            (toplam_ram - kullanilan_ram) >= " . intval(preg_replace('/[^0-9]/', '', $sunucu['ram'])) . " AND
            (toplam_disk - kullanilan_disk) >= " . (stripos($sunucu['disk'], 'TB') !== false ?
    intval(preg_replace('/[^0-9]/', '', $sunucu['disk'])) * 1024 :
    intval(preg_replace('/[^0-9]/', '', $sunucu['disk']))) . "
        ORDER BY fs.sunucu_adi";
$fiziksel_sunucular = mysqli_query($conn, $sql);

// Projeleri getir
$sql = "SELECT * FROM projeler WHERE durum = 'Aktif' ORDER BY proje_adi";
$projeler = mysqli_query($conn, $sql);

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sunucu_adi = mysqli_real_escape_string($conn, $_POST['sunucu_adi']);
    $ip_adresi = mysqli_real_escape_string($conn, $_POST['ip_adresi']);
    $ram = mysqli_real_escape_string($conn, $_POST['ram']);
    $cpu = mysqli_real_escape_string($conn, $_POST['cpu']);
    $disk = mysqli_real_escape_string($conn, $_POST['disk']);
    $proje_id = isset($_POST['proje_id']) && !empty($_POST['proje_id']) ?
        mysqli_real_escape_string($conn, $_POST['proje_id']) : 'NULL';
    $fiziksel_sunucu_id = mysqli_real_escape_string($conn, $_POST['fiziksel_sunucu_id']);

    // IP adresi kontrolü
    $ip_kontrol = "SELECT COUNT(*) as sayi FROM sanal_sunucular WHERE ip_adresi = '$ip_adresi' AND id != '$id'";
    $ip_result = mysqli_query($conn, $ip_kontrol);
    $ip_row = mysqli_fetch_assoc($ip_result);

    if ($ip_row['sayi'] > 0) {
        $mesaj = "<div class='alert alert-danger'>" . $language->get('error_ip_in_use') . "</div>";
    } else {
        $sql = "UPDATE sanal_sunucular SET 
                sunucu_adi = '$sunucu_adi',
                ip_adresi = '$ip_adresi',
                ram = '$ram',
                cpu = '$cpu',
                disk = '$disk',
                proje_id = $proje_id,
                fiziksel_sunucu_id = '$fiziksel_sunucu_id'
                WHERE id = '$id'";

        if (mysqli_query($conn, $sql)) {
            header('Location: sanal_sunucular.php?fiziksel_id=' . $fiziksel_sunucu_id .
                '&basari=' . urlencode($language->get('success_virtual_server_updated')));
            exit;
        } else {
            $mesaj = "<div class='alert alert-danger'>" . $language->get('error_updating_virtual_server', ['error' => mysqli_error($conn)]) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $language->get('edit_virtual_server') . ' - ' . $sunucu['sunucu_adi']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>

    <div class="container">
        <div class="mb-3">
            <a href="sanal_sunucular.php?fiziksel_id=<?php echo $sunucu['fiziksel_sunucu_id']; ?>" class="btn btn-secondary">
                ← <?php echo $language->get('back_to_virtual_servers'); ?>
            </a>
        </div>

        <?php echo $mesaj; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title h5 mb-0">
                    <?php echo $language->get('edit_virtual_server'); ?>
                    <small class="text-muted">(<?php echo $sunucu['sunucu_adi']; ?>)</small>
                </h2>
            </div>
            <div class="card-body">
                <form method="POST" class="row" id="sanal_sunucu_form">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sunucu_adi" class="form-label"><?php echo $language->get('server_name'); ?></label>
                            <input type="text" class="form-control" id="sunucu_adi" name="sunucu_adi"
                                value="<?php echo $sunucu['sunucu_adi']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="ip_adresi" class="form-label"><?php echo $language->get('ip_address'); ?></label>
                            <input type="text" class="form-control" id="ip_adresi" name="ip_adresi"
                                value="<?php echo $sunucu['ip_adresi']; ?>"
                                pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"
                                title="<?php echo $language->get('enter_valid_ipv4'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="proje_id" class="form-label"><?php echo $language->get('project'); ?></label>
                            <select class="form-select" id="proje_id" name="proje_id">
                                <option value=""><?php echo $language->get('select_project'); ?></option>
                                <?php while ($proje = mysqli_fetch_assoc($projeler)): ?>
                                    <option value="<?php echo $proje['id']; ?>"
                                        <?php echo ($proje['id'] == $sunucu['proje_id']) ? 'selected' : ''; ?>>
                                        <?php echo $language->get('project_info', ['project_name' => $proje['proje_adi'], 'project_code' => $proje['proje_kodu']]); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fiziksel_sunucu_id" class="form-label"><?php echo $language->get('physical_server'); ?></label>
                            <select class="form-select" id="fiziksel_sunucu_id" name="fiziksel_sunucu_id" required>
                                <?php while ($fs = mysqli_fetch_assoc($fiziksel_sunucular)): ?>
                                    <option value="<?php echo $fs['id']; ?>"
                                        <?php echo ($fs['id'] == $sunucu['fiziksel_sunucu_id']) ? 'selected' : ''; ?>
                                        data-cpu="<?php echo $fs['toplam_cpu'] - $fs['kullanilan_cpu']; ?>"
                                        data-ram="<?php echo $fs['toplam_ram'] - $fs['kullanilan_ram']; ?>"
                                        data-disk="<?php echo $fs['toplam_disk'] - $fs['kullanilan_disk']; ?>">
                                        <?php echo $fs['sunucu_adi']; ?>
                                        (<?php echo $language->get('available_resources', [
                                                'cpu' => $fs['toplam_cpu'] - $fs['kullanilan_cpu'],
                                                'ram' => $fs['toplam_ram'] - $fs['kullanilan_ram'],
                                                'disk' => $fs['toplam_disk'] - $fs['kullanilan_disk']
                                            ]); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text"><?php echo $language->get('only_servers_with_resources'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="cpu" class="form-label"><?php echo $language->get('cpu_cores'); ?></label>
                            <input type="text" class="form-control" id="cpu" name="cpu"
                                value="<?php echo $sunucu['cpu']; ?>"
                                placeholder="<?php echo $language->get('cpu_placeholder'); ?>" required
                                pattern="^\d+\s*(?:core|cores|cpu|işlemci|çekirdek)?$"
                                title="<?php echo $language->get('enter_valid_number', ['example' => '4 Core']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="ram" class="form-label"><?php echo $language->get('memory'); ?></label>
                            <input type="text" class="form-control" id="ram" name="ram"
                                value="<?php echo $sunucu['ram']; ?>"
                                placeholder="<?php echo $language->get('ram_placeholder'); ?>" required
                                pattern="^\d+\s*(?:gb|g|gigabyte)?$"
                                title="<?php echo $language->get('enter_valid_number', ['example' => '8GB']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="disk" class="form-label"><?php echo $language->get('disk'); ?></label>
                            <input type="text" class="form-control" id="disk" name="disk"
                                value="<?php echo $sunucu['disk']; ?>"
                                placeholder="<?php echo $language->get('disk_placeholder'); ?>" required
                                pattern="^\d+\s*(?:gb|g|tb|t)?$"
                                title="<?php echo $language->get('enter_valid_number', ['example' => '100GB']); ?>">
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><?php echo $language->get('save_changes'); ?></button>
                    </div>
                </form>

                <script>
                    document.getElementById('sanal_sunucu_form').addEventListener('submit', function(e) {
                        var cpu = parseInt(document.getElementById('cpu').value.replace(/[^0-9]/g, ''));
                        var ram = parseInt(document.getElementById('ram').value.replace(/[^0-9]/g, ''));
                        var disk = parseInt(document.getElementById('disk').value.replace(/[^0-9]/g, ''));

                        var fizikselSunucu = document.getElementById('fiziksel_sunucu_id').selectedOptions[0];
                        var kalanCpu = parseInt(fizikselSunucu.getAttribute('data-cpu'));
                        var kalanRam = parseInt(fizikselSunucu.getAttribute('data-ram'));
                        var kalanDisk = parseInt(fizikselSunucu.getAttribute('data-disk'));

                        var hatalar = [];

                        if (cpu > kalanCpu) {
                            hatalar.push('<?php echo $language->get('error_cpu_capacity', ['value' => "' + kalanCpu + '"]); ?>');
                        }

                        if (ram > kalanRam) {
                            hatalar.push('<?php echo $language->get('error_ram_capacity', ['value' => "' + kalanRam + '"]); ?>');
                        }

                        if (disk > kalanDisk) {
                            hatalar.push('<?php echo $language->get('error_disk_capacity', ['value' => "' + kalanDisk + '"]); ?>');
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