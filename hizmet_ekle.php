<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

$mesaj = '';
$duzenle_mod = false;
$hizmet = null;

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM hizmetler WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $hizmet = mysqli_fetch_assoc($result);

    if ($hizmet) {
        $duzenle_mod = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hizmet_adi = mysqli_real_escape_string($conn, $_POST['hizmet_adi']);
    $aciklama = mysqli_real_escape_string($conn, $_POST['aciklama']);
    $port = mysqli_real_escape_string($conn, $_POST['port']);
    $durum = mysqli_real_escape_string($conn, $_POST['durum']);

    if ($duzenle_mod) {
        $sql = "UPDATE hizmetler SET 
                hizmet_adi = '$hizmet_adi',
                aciklama = '$aciklama',
                port = '$port',
                durum = '$durum'
                WHERE id = '$id'";
        $basari_mesaj = $language->get('service_updated');
    } else {
        $sql = "INSERT INTO hizmetler (hizmet_adi, aciklama, port, durum) 
                VALUES ('$hizmet_adi', '$aciklama', '$port', '$durum')";
        $basari_mesaj = $language->get('service_added');
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: hizmet_ekle.php?basari=" . urlencode($basari_mesaj));
        exit;
    } else {
        $mesaj = "<div class='alert alert-danger'>" . $language->get('error') . ": " . mysqli_error($conn) . "</div>";
    }
}

// Mevcut hizmetleri getir
$sql = "SELECT h.*, 
        (SELECT COUNT(*) FROM sanal_sunucu_hizmetler WHERE hizmet_id = h.id) as kullanim_sayisi 
        FROM hizmetler h 
        ORDER BY h.hizmet_adi";
$result = mysqli_query($conn, $sql);

// URL'den gelen mesajları kontrol et
if (isset($_GET['basari'])) {
    $mesaj = "<div class='alert alert-success'>" . htmlspecialchars($_GET['basari']) . "</div>";
} elseif (isset($_GET['hata'])) {
    $mesaj = "<div class='alert alert-danger'>" . htmlspecialchars($_GET['hata']) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $duzenle_mod ? $language->get('edit_service') : $language->get('add_service'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>
    <div class="container">
        <?php echo $mesaj; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h5 mb-0">
                            <?php echo $duzenle_mod ? $language->get('edit_service') : $language->get('add_service'); ?>
                        </h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="hizmet_adi" class="form-label"><?php echo $language->get('service_name'); ?></label>
                                <input type="text" class="form-control" id="hizmet_adi" name="hizmet_adi"
                                    value="<?php echo $duzenle_mod ? $hizmet['hizmet_adi'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="aciklama" class="form-label"><?php echo $language->get('description'); ?></label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?php echo $duzenle_mod ? $hizmet['aciklama'] : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="port" class="form-label"><?php echo $language->get('default_port'); ?></label>
                                <input type="text" class="form-control" id="port" name="port"
                                    value="<?php echo $duzenle_mod ? $hizmet['port'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="durum" class="form-label"><?php echo $language->get('status'); ?></label>
                                <select class="form-select" id="durum" name="durum">
                                    <option value="Aktif" <?php echo ($duzenle_mod && $hizmet['durum'] == 'Aktif') ? 'selected' : ''; ?>><?php echo $language->get('active'); ?></option>
                                    <option value="Pasif" <?php echo ($duzenle_mod && $hizmet['durum'] == 'Pasif') ? 'selected' : ''; ?>><?php echo $language->get('passive'); ?></option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $duzenle_mod ? $language->get('update') : $language->get('save'); ?>
                            </button>
                            <?php if ($duzenle_mod): ?>
                                <a href="hizmet_ekle.php" class="btn btn-secondary"><?php echo $language->get('new_service'); ?></a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h5 mb-0"><?php echo $language->get('existing_services'); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-responsive">
                                <thead>
                                    <tr>
                                        <th><?php echo $language->get('service_name'); ?></th>
                                        <th><?php echo $language->get('port'); ?></th>
                                        <th><?php echo $language->get('status'); ?></th>
                                        <th><?php echo $language->get('service_usage'); ?></th>
                                        <th class="text-end"><?php echo $language->get('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($row['hizmet_adi']); ?>
                                                <?php if ($row['aciklama']): ?>
                                                    <small class="d-block text-muted"><?php echo htmlspecialchars($row['aciklama']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $row['port'] ?: '-'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $row['durum'] == 'Aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $language->get($row['durum'] == 'Aktif' ? 'active' : 'passive'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['kullanim_sayisi'] > 0): ?>
                                                    <span class="badge bg-info">
                                                        <?php echo str_replace('{count}', $row['kullanim_sayisi'], $language->get('server_count')); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo $language->get('not_in_use'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="hizmet_ekle.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm"><?php echo $language->get('edit'); ?></a>
                                                <?php if ($row['kullanim_sayisi'] == 0): ?>
                                                    <a href="hizmet_sil.php?id=<?php echo $row['id']; ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('<?php echo $language->get('confirm_delete_service'); ?>')"><?php echo $language->get('delete'); ?></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>