<?php

/**
 * @author A. Kerem Gök
 */

 require_once __DIR__ . '/auth.php';
 require_once __DIR__ . '/config/database.php';
 require_once __DIR__ . '/config/language.php';
 $language = Language::getInstance();

$mesaj = '';

// URL'den gelen mesajları kontrol et
if (isset($_GET['hata'])) {
    $mesaj = "<div class='alert alert-danger'>" . htmlspecialchars($_GET['hata']) . "</div>";
} elseif (isset($_GET['basari'])) {
    $mesaj = "<div class='alert alert-success'>" . htmlspecialchars($_GET['basari']) . "</div>";
}

$duzenle_mod = false;
$lokasyon = null;

// Düzenleme modu kontrolü
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM lokasyonlar WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $lokasyon = mysqli_fetch_assoc($result);

    if ($lokasyon) {
        $duzenle_mod = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lokasyon_adi = mysqli_real_escape_string($conn, $_POST['lokasyon_adi']);

    if ($duzenle_mod) {
        // Güncelleme işlemi
        $id = mysqli_real_escape_string($conn, $_GET['id']);

        // Aynı isimde lokasyon var mı kontrolü (kendi adı hariç)
        $sql_kontrol = "SELECT id FROM lokasyonlar WHERE lokasyon_adi = '$lokasyon_adi' AND id != '$id'";
        $result_kontrol = mysqli_query($conn, $sql_kontrol);

        if (mysqli_num_rows($result_kontrol) > 0) {
            $mesaj = "<div class='alert alert-danger'>" . $language->get('error_location_exists') . "</div>";
        } else {
            $sql = "UPDATE lokasyonlar SET lokasyon_adi = '$lokasyon_adi' WHERE id = '$id'";

            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>" . $language->get('success_location_updated') . "</div>";
                // Güncel veriyi al
                $sql = "SELECT * FROM lokasyonlar WHERE id = '$id'";
                $result = mysqli_query($conn, $sql);
                $lokasyon = mysqli_fetch_assoc($result);
            } else {
                $mesaj = "<div class='alert alert-danger'>" . $language->get('error') . ": " . mysqli_error($conn) . "</div>";
            }
        }
    } else {
        // Yeni lokasyon ekleme
        $sql_kontrol = "SELECT id FROM lokasyonlar WHERE lokasyon_adi = '$lokasyon_adi'";
        $result_kontrol = mysqli_query($conn, $sql_kontrol);

        if (mysqli_num_rows($result_kontrol) > 0) {
            $mesaj = "<div class='alert alert-danger'>" . $language->get('error_location_exists') . "</div>";
        } else {
            $sql = "INSERT INTO lokasyonlar (lokasyon_adi) VALUES ('$lokasyon_adi')";

            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>" . $language->get('success_location_added') . "</div>";
                $_POST = array(); // Formu temizle
            } else {
                $mesaj = "<div class='alert alert-danger'>" . $language->get('error') . ": " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

// Mevcut lokasyonları listele
$sql_lokasyonlar = "SELECT l.*, 
                    (SELECT COUNT(*) FROM fiziksel_sunucular WHERE lokasyon_id = l.id) as sunucu_sayisi 
                    FROM lokasyonlar l 
                    ORDER BY l.lokasyon_adi";
$result_lokasyonlar = mysqli_query($conn, $sql_lokasyonlar);
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $language->get('location_management'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>

    <div class="container">
        <?php echo $mesaj; ?>

        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title h3"><?php echo $duzenle_mod ? $language->get('edit_location_title') : $language->get('add_new_location_title'); ?></h1>
                    </div>
                    <div class="card-body">
                        <?php echo $mesaj; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="lokasyon_adi" class="form-label"><?php echo $language->get('location_name'); ?></label>
                                <input type="text" class="form-control" id="lokasyon_adi" name="lokasyon_adi"
                                    value="<?php echo $duzenle_mod ? $lokasyon['lokasyon_adi'] : (isset($_POST['lokasyon_adi']) ? $_POST['lokasyon_adi'] : ''); ?>"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $duzenle_mod ? $language->get('update') : $language->get('save'); ?>
                            </button>
                            <?php if ($duzenle_mod): ?>
                                <a href="lokasyon_ekle.php" class="btn btn-secondary"><?php echo $language->get('cancel'); ?></a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h3"><?php echo $language->get('existing_locations'); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?php echo $language->get('location_name'); ?></th>
                                        <th><?php echo $language->get('server_count'); ?></th>
                                        <th><?php echo $language->get('created_at'); ?></th>
                                        <th><?php echo $language->get('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    mysqli_data_seek($result_lokasyonlar, 0);
                                    while ($row = mysqli_fetch_assoc($result_lokasyonlar)):
                                    ?>
                                        <tr>
                                            <td><?php echo $row['lokasyon_adi']; ?></td>
                                            <td>
                                                <?php if ($row['sunucu_sayisi'] > 0): ?>
                                                    <span class="badge bg-info"><?php echo str_replace('{count}', $row['sunucu_sayisi'], $language->get('server_count_info')); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo $language->get('no_servers'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($row['olusturma_tarihi'])); ?></td>
                                            <td>
                                                <a href="?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><?php echo $language->get('edit'); ?></a>
                                                <?php if ($row['sunucu_sayisi'] == 0): ?>
                                                    <a href="lokasyon_sil.php?id=<?php echo $row['id']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('<?php echo $language->get('confirm_delete_location'); ?>')"><?php echo $language->get('delete'); ?></a>
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