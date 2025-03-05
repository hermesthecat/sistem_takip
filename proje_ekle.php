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
$proje = null;

// Düzenleme modu kontrolü
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM projeler WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $proje = mysqli_fetch_assoc($result);

    if ($proje) {
        $duzenle_mod = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $proje_adi = mysqli_real_escape_string($conn, $_POST['proje_adi']);
    $proje_kodu = mysqli_real_escape_string($conn, $_POST['proje_kodu']);
    $aciklama = mysqli_real_escape_string($conn, $_POST['aciklama']);
    $durum = mysqli_real_escape_string($conn, $_POST['durum']);

    if ($duzenle_mod) {
        // Güncelleme işlemi
        $id = mysqli_real_escape_string($conn, $_GET['id']);

        // Proje kodu kontrolü (kendi kodu hariç)
        $sql_kontrol = "SELECT id FROM projeler WHERE proje_kodu = '$proje_kodu' AND id != '$id'";
        $result_kontrol = mysqli_query($conn, $sql_kontrol);

        if (mysqli_num_rows($result_kontrol) > 0) {
            $mesaj = "<div class='alert alert-danger'>" . $language->get('error_project_code_exists') . "</div>";
        } else {
            $sql = "UPDATE projeler SET 
                    proje_adi = '$proje_adi',
                    proje_kodu = '$proje_kodu',
                    aciklama = '$aciklama',
                    durum = '$durum'
                    WHERE id = '$id'";

            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>" . $language->get('success_project_updated') . "</div>";
                // Güncel veriyi al
                $sql = "SELECT * FROM projeler WHERE id = '$id'";
                $result = mysqli_query($conn, $sql);
                $proje = mysqli_fetch_assoc($result);
            } else {
                $mesaj = "<div class='alert alert-danger'>" . str_replace('{error}', mysqli_error($conn), $language->get('error_updating_project')) . "</div>";
            }
        }
    } else {
        // Yeni proje ekleme
        $sql_kontrol = "SELECT id FROM projeler WHERE proje_kodu = '$proje_kodu'";
        $result_kontrol = mysqli_query($conn, $sql_kontrol);

        if (mysqli_num_rows($result_kontrol) > 0) {
            $mesaj = "<div class='alert alert-danger'>" . $language->get('error_project_code_exists') . "</div>";
        } else {
            $sql = "INSERT INTO projeler (proje_adi, proje_kodu, aciklama, durum) VALUES ('$proje_adi', '$proje_kodu', '$aciklama', '$durum')";

            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>" . $language->get('success_project_added') . "</div>";
                $_POST = array(); // Formu temizle
            } else {
                $mesaj = "<div class='alert alert-danger'>" . str_replace('{error}', mysqli_error($conn), $language->get('error_updating_project')) . "</div>";
            }
        }
    }
}

// Mevcut projeleri listele
$sql_projeler = "SELECT p.*, 
                (SELECT COUNT(*) FROM fiziksel_sunucular WHERE proje_id = p.id) as fiziksel_sayi,
                (SELECT COUNT(*) FROM sanal_sunucular WHERE proje_id = p.id) as sanal_sayi
                FROM projeler p 
                ORDER BY p.proje_adi";
$result_projeler = mysqli_query($conn, $sql_projeler);
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $language->get('project_management'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>

    <div class="container">
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title h3">
                            <?php echo $duzenle_mod ? $language->get('edit_project') : $language->get('add_new_project'); ?>
                        </h1>
                    </div>
                    <div class="card-body">
                        <?php echo $mesaj; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="proje_adi" class="form-label"><?php echo $language->get('project_name'); ?></label>
                                <input type="text" class="form-control" id="proje_adi" name="proje_adi"
                                    value="<?php echo $duzenle_mod ? $proje['proje_adi'] : (isset($_POST['proje_adi']) ? $_POST['proje_adi'] : ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="proje_kodu" class="form-label"><?php echo $language->get('project_code'); ?></label>
                                <input type="text" class="form-control" id="proje_kodu" name="proje_kodu"
                                    value="<?php echo $duzenle_mod ? $proje['proje_kodu'] : (isset($_POST['proje_kodu']) ? $_POST['proje_kodu'] : ''); ?>"
                                    placeholder="<?php echo $language->get('project_code_placeholder'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="aciklama" class="form-label"><?php echo $language->get('description'); ?></label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?php
                                                                                                        echo $duzenle_mod ? $proje['aciklama'] : (isset($_POST['aciklama']) ? $_POST['aciklama'] : '');
                                                                                                        ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="durum" class="form-label"><?php echo $language->get('status'); ?></label>
                                <select class="form-select" id="durum" name="durum" required>
                                    <?php
                                    $durumlar = array('Aktif' => 'active', 'Pasif' => 'passive', 'Tamamlandı' => 'completed');
                                    foreach ($durumlar as $tr => $en) {
                                        $selected = '';
                                        if ($duzenle_mod && $proje['durum'] == $tr) {
                                            $selected = 'selected';
                                        } elseif (!$duzenle_mod && isset($_POST['durum']) && $_POST['durum'] == $tr) {
                                            $selected = 'selected';
                                        } elseif (!$duzenle_mod && !isset($_POST['durum']) && $tr == 'Aktif') {
                                            $selected = 'selected';
                                        }
                                        echo "<option value='$tr' $selected>" . $language->get($en) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $duzenle_mod ? $language->get('update') : $language->get('save'); ?>
                            </button>
                            <?php if ($duzenle_mod): ?>
                                <a href="proje_ekle.php" class="btn btn-secondary"><?php echo $language->get('cancel'); ?></a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h3"><?php echo $language->get('existing_projects'); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo $language->get('project_name'); ?></th>
                                        <th><?php echo $language->get('project_code'); ?></th>
                                        <th><?php echo $language->get('status'); ?></th>
                                        <th><?php echo $language->get('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    mysqli_data_seek($result_projeler, 0);
                                    while ($row = mysqli_fetch_assoc($result_projeler)):
                                    ?>
                                        <tr>
                                            <td>
                                                <?php echo $row['proje_adi']; ?>
                                                <?php if ($row['aciklama']): ?>
                                                    <small class="d-block text-muted"><?php echo $row['aciklama']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><code><?php echo $row['proje_kodu']; ?></code></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                                        echo $row['durum'] == 'Aktif' ? 'success' : ($row['durum'] == 'Pasif' ? 'warning' : 'secondary');
                                                                        ?>"><?php echo $language->get($row['durum'] == 'Aktif' ? 'active' : ($row['durum'] == 'Pasif' ? 'passive' : 'completed')); ?></span>
                                                <?php if ($row['fiziksel_sayi'] > 0 || $row['sanal_sayi'] > 0): ?>
                                                    <div class="mt-1">
                                                        <?php if ($row['fiziksel_sayi'] > 0): ?>
                                                            <span class="badge bg-info"><?php echo str_replace('{count}', $row['fiziksel_sayi'], $language->get('physical_server_count')); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($row['sanal_sayi'] > 0): ?>
                                                            <span class="badge bg-info"><?php echo str_replace('{count}', $row['sanal_sayi'], $language->get('virtual_server_count')); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><?php echo $language->get('edit'); ?></a>
                                                <?php if ($row['fiziksel_sayi'] == 0 && $row['sanal_sayi'] == 0): ?>
                                                    <a href="proje_sil.php?id=<?php echo $row['id']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('<?php echo $language->get('confirm_delete_project'); ?>')"><?php echo $language->get('delete'); ?></a>
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