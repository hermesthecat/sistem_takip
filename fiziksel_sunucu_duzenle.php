<?php

/**
 * @author A. Kerem Gök
 */

 require_once __DIR__ . '/auth.php';
 require_once __DIR__ . '/config/database.php';
 require_once __DIR__ . '/config/language.php';
 $language = Language::getInstance();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Sunucu bilgilerini al
$sql = "SELECT fs.*, p.proje_adi, p.proje_kodu 
        FROM fiziksel_sunucular fs
        LEFT JOIN projeler p ON fs.proje_id = p.id
        WHERE fs.id = '$id'";
$result = mysqli_query($conn, $sql);
$sunucu = mysqli_fetch_assoc($result);

if (!$sunucu) {
    header('Location: index.php');
    exit;
}

// Lokasyonları getir
$sql_lokasyonlar = "SELECT * FROM lokasyonlar ORDER BY lokasyon_adi";
$result_lokasyonlar = mysqli_query($conn, $sql_lokasyonlar);

// Projeleri getir
$sql_projeler = "SELECT * FROM projeler WHERE durum = 'Aktif' ORDER BY proje_adi";
$result_projeler = mysqli_query($conn, $sql_projeler);

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sunucu_adi = mysqli_real_escape_string($conn, $_POST['sunucu_adi']);
    $ip_adresi = mysqli_real_escape_string($conn, $_POST['ip_adresi']);
    $lokasyon_id = mysqli_real_escape_string($conn, $_POST['lokasyon_id']);
    $proje_id = isset($_POST['proje_id']) ? mysqli_real_escape_string($conn, $_POST['proje_id']) : 'NULL';
    $ram = mysqli_real_escape_string($conn, $_POST['ram']);
    $cpu = mysqli_real_escape_string($conn, $_POST['cpu']);
    $disk = mysqli_real_escape_string($conn, $_POST['disk']);

    $sql = "UPDATE fiziksel_sunucular SET 
            sunucu_adi = '$sunucu_adi',
            ip_adresi = '$ip_adresi',
            lokasyon_id = '$lokasyon_id',
            proje_id = $proje_id,
            ram = '$ram',
            cpu = '$cpu',
            disk = '$disk'
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        header('Location: index.php');
        exit;
    } else {
        $mesaj = $language->get('error_updating_server') . ": " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $language->get('edit_physical_server'); ?> - <?php echo $sunucu['sunucu_adi']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php require_once 'header.php'; ?>

    <div class="container">
        <div class="mb-3">
            <a href="index.php" class="btn btn-secondary">← <?php echo $language->get('back_to_physical_servers'); ?></a>
        </div>
        <div class="card">
            <div class="card-header">
                <h1 class="card-title"><?php echo $language->get('edit_physical_server'); ?> - <?php echo $sunucu['sunucu_adi']; ?></h1>
            </div>
            <div class="card-body">
                <?php if ($mesaj): ?>
                    <div class="alert alert-danger"><?php echo $mesaj; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sunucu_adi" class="form-label"><?php echo $language->get('server_name'); ?></label>
                                <input type="text" class="form-control" id="sunucu_adi" name="sunucu_adi" value="<?php echo $sunucu['sunucu_adi']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="ip_adresi" class="form-label"><?php echo $language->get('ip_address'); ?></label>
                                <input type="text" class="form-control" id="ip_adresi" name="ip_adresi" value="<?php echo $sunucu['ip_adresi']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="lokasyon_id" class="form-label"><?php echo $language->get('location'); ?></label>
                                <select class="form-select" id="lokasyon_id" name="lokasyon_id" required>
                                    <option value=""><?php echo $language->get('select_location'); ?></option>
                                    <?php while ($lokasyon = mysqli_fetch_assoc($result_lokasyonlar)): ?>
                                        <option value="<?php echo $lokasyon['id']; ?>" <?php echo ($lokasyon['id'] == $sunucu['lokasyon_id']) ? 'selected' : ''; ?>>
                                            <?php echo $lokasyon['lokasyon_adi']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="mt-2">
                                    <a href="lokasyon_ekle.php" class="btn btn-sm btn-outline-primary"><?php echo $language->get('add_new_location'); ?></a>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="proje_id" class="form-label"><?php echo $language->get('project'); ?></label>
                                <select class="form-select" id="proje_id" name="proje_id">
                                    <option value=""><?php echo $language->get('select_project'); ?></option>
                                    <?php while ($proje = mysqli_fetch_assoc($result_projeler)): ?>
                                        <option value="<?php echo $proje['id']; ?>"
                                            <?php echo ($proje['id'] == $sunucu['proje_id']) ? 'selected' : ''; ?>>
                                            <?php echo $proje['proje_adi']; ?> (<?php echo $proje['proje_kodu']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="mt-2">
                                    <a href="proje_ekle.php" class="btn btn-sm btn-outline-success"><?php echo $language->get('add_new_project'); ?></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cpu" class="form-label"><?php echo $language->get('cpu_cores'); ?></label>
                                <input type="text" class="form-control" id="cpu" name="cpu"
                                    value="<?php echo $sunucu['cpu']; ?>"
                                    placeholder="<?php echo $language->get('cpu_placeholder'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="ram" class="form-label"><?php echo $language->get('memory'); ?></label>
                                <input type="text" class="form-control" id="ram" name="ram"
                                    value="<?php echo $sunucu['ram']; ?>"
                                    placeholder="<?php echo $language->get('memory_placeholder'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="disk" class="form-label"><?php echo $language->get('disk'); ?></label>
                                <input type="text" class="form-control" id="disk" name="disk"
                                    value="<?php echo $sunucu['disk']; ?>"
                                    placeholder="<?php echo $language->get('disk_placeholder'); ?>" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $language->get('update'); ?></button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>