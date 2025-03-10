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
    $sql = "SELECT * FROM websiteler WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $hizmet = mysqli_fetch_assoc($result);

    if ($hizmet) {
        $duzenle_mod = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alan_adi = mysqli_real_escape_string($conn, $_POST['alan_adi']);
    $aciklama = mysqli_real_escape_string($conn, $_POST['aciklama']);
    $durum = mysqli_real_escape_string($conn, $_POST['durum']);

    if ($duzenle_mod) {
        $sql = "UPDATE websiteler SET 
                alan_adi = '$alan_adi',
                aciklama = '$aciklama',
                durum = '$durum'
                WHERE id = '$id'";
        $basari_mesaj = $language->get('web_site_updated');
    } else {
        $sql = "INSERT INTO websiteler (alan_adi, aciklama, durum) 
                VALUES ('$alan_adi', '$aciklama', '$durum')";
        $basari_mesaj = $language->get('web_site_added');
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: site_ekle.php?basari=" . urlencode($basari_mesaj));
        exit;
    } else {
        $mesaj = "<div class='alert alert-danger'>" . $language->get('error_deleting_web_site') . ": " . mysqli_error($conn) . "</div>";
    }
}

// Mevcut web siteleri getir
$sql = "SELECT h.*, 
        (SELECT COUNT(*) FROM sanal_sunucu_web_siteler WHERE website_id = h.id) as kullanim_sayisi 
        FROM websiteler h 
        ORDER BY h.alan_adi";
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
    <title><?php echo $duzenle_mod ? $language->get('edit_web_site') : $language->get('add_web_site'); ?></title>
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
                            <?php echo $duzenle_mod ? $language->get('edit_web_site') : $language->get('add_web_site'); ?>
                        </h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="alan_adi" class="form-label"><?php echo $language->get('web_site_name'); ?></label>
                                <input type="text" class="form-control" id="alan_adi" name="alan_adi"
                                    value="<?php echo $duzenle_mod ? $hizmet['alan_adi'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="aciklama" class="form-label"><?php echo $language->get('description'); ?></label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?php echo $duzenle_mod ? $hizmet['aciklama'] : ''; ?></textarea>
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
                                <a href="hizmet_ekle.php" class="btn btn-secondary"><?php echo $language->get('new_web_site'); ?></a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h5 mb-0"><?php echo $language->get('existing_web_sites'); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-responsive">
                                <thead>
                                    <tr>
                                        <th><?php echo $language->get('web_site_name'); ?></th>
                                        <th><?php echo $language->get('status'); ?></th>
                                        <th><?php echo $language->get('web_site_usage'); ?></th> <!-- Updated to match context -->
                                        <th><?php echo $language->get('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($row['alan_adi']); ?>
                                                <?php if ($row['aciklama']): ?>
                                                    <small class="d-block text-muted"><?php echo htmlspecialchars($row['aciklama']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $row['durum'] == 'Aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $language->get($row['durum'] == 'Aktif' ? 'active' : 'passive'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['kullanim_sayisi'] > 0): ?>
                                                    <span class="badge bg-info">
                                                        <?php echo str_replace('{count}', $row['kullanim_sayisi'], $language->get('web_site_count')); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo $language->get('not_in_use'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="hizmet_ekle.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm"><?php echo $language->get('edit'); ?></a>
                                                <?php if ($row['kullanim_sayisi'] == 0): ?>
                                                    <a href="hizmet_sil.php?id=<?php echo $row['id']; ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('<?php echo $language->get('confirm_delete_web_site'); ?>')"><?php echo $language->get('delete'); ?></a> <!-- Updated confirmation message -->
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