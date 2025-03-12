<?php

/**
 * @author A. Kerem Gök
 */

session_start();

date_default_timezone_set('Europe/Istanbul');

require_once __DIR__ . '/config/database.php';

// Dil yönetimini başlat
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

// Session'da kayıtlı dil varsa onu kullan
if (isset($_SESSION['lang'])) {
    $language->setLanguage($_SESSION['lang']);
}

// Giriş kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}

// Admin kontrolü
if ($_SESSION['rol'] !== 'admin') {
    header('Location: index.php?hata=' . urlencode($language->get('error_no_access')));
    exit;
}

$mesaj = '';

// URL'den gelen mesajları kontrol et
if (isset($_GET['hata'])) {
    $mesaj = "<div class='alert alert-danger'>" . htmlspecialchars($_GET['hata']) . "</div>";
} elseif (isset($_GET['basari'])) {
    $mesaj = "<div class='alert alert-success'>" . htmlspecialchars($_GET['basari']) . "</div>";
}

// Kullanıcı ekleme/güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['kullanici_ekle']) || isset($_POST['kullanici_guncelle'])) {
        $kullanici_adi = mysqli_real_escape_string($conn, $_POST['kullanici_adi']);
        $ad_soyad = mysqli_real_escape_string($conn, $_POST['ad_soyad']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $rol = mysqli_real_escape_string($conn, $_POST['rol']);
        $durum = mysqli_real_escape_string($conn, $_POST['durum']);

        if (isset($_POST['kullanici_ekle'])) {
            // Yeni kullanıcı ekleme
            $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);

            // Kullanıcı adı ve email kontrolü
            $kontrol = mysqli_query($conn, "SELECT id FROM kullanicilar WHERE kullanici_adi = '$kullanici_adi' OR email = '$email'");
            if (mysqli_num_rows($kontrol) > 0) {
                $mesaj = "<div class='alert alert-danger'>" . $language->get('error_username_email_exists') . "</div>";
            } else {
                $sql = "INSERT INTO kullanicilar (kullanici_adi, ad_soyad, email, sifre, rol, durum) 
                        VALUES ('$kullanici_adi', '$ad_soyad', '$email', '$sifre', '$rol', '$durum')";

                if (mysqli_query($conn, $sql)) {
                    $mesaj = "<div class='alert alert-success'>" . $language->get('success_user_added') . "</div>";
                } else {
                    $mesaj = "<div class='alert alert-danger'>" . $language->get('error') . ": " . mysqli_error($conn) . "</div>";
                }
            }
        } else {
            // Kullanıcı güncelleme
            $id = mysqli_real_escape_string($conn, $_POST['id']);

            // Email kontrolü (kendi emaili hariç)
            $kontrol = mysqli_query($conn, "SELECT id FROM kullanicilar WHERE email = '$email' AND id != '$id'");
            if (mysqli_num_rows($kontrol) > 0) {
                $mesaj = "<div class='alert alert-danger'>" . $language->get('error_email_exists') . "</div>";
            } else {
                $sql = "UPDATE kullanicilar SET 
                        ad_soyad = '$ad_soyad',
                        email = '$email',
                        rol = '$rol',
                        durum = '$durum'
                        WHERE id = '$id'";

                if (!empty($_POST['sifre'])) {
                    $yeni_sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
                    $sql = "UPDATE kullanicilar SET 
                            ad_soyad = '$ad_soyad',
                            email = '$email',
                            sifre = '$yeni_sifre',
                            rol = '$rol',
                            durum = '$durum'
                            WHERE id = '$id'";
                }

                if (mysqli_query($conn, $sql)) {
                    $mesaj = "<div class='alert alert-success'>" . $language->get('success_user_updated') . "</div>";
                } else {
                    $mesaj = "<div class='alert alert-danger'>" . $language->get('error') . ": " . mysqli_error($conn) . "</div>";
                }
            }
        }
    }
}

// Kullanıcıları getir
$sql = "SELECT * FROM kullanicilar ORDER BY kullanici_adi";
$kullanicilar = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $language->get('user_management'); ?> - <?php echo $language->get('dashboard'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php require_once __DIR__ . '/header.php'; ?>

    <div class="container">
        <?php echo $mesaj; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="card-title h5 mb-0"><?php echo $language->get('users_list'); ?></h2>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#yeniKullaniciModal">
                            <?php echo $language->get('add_new_user'); ?>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo $language->get('user_id'); ?></th>
                                        <th><?php echo $language->get('username'); ?></th>
                                        <th><?php echo $language->get('full_name'); ?></th>
                                        <th><?php echo $language->get('email'); ?></th>
                                        <th><?php echo $language->get('role'); ?></th>
                                        <th><?php echo $language->get('status'); ?></th>
                                        <th><?php echo $language->get('last_login'); ?></th>
                                        <th><?php echo $language->get('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($kullanici = mysqli_fetch_assoc($kullanicilar)): ?>
                                        <tr>
                                            <td><?php echo $kullanici['id']; ?></td>
                                            <td><?php echo $kullanici['kullanici_adi']; ?></td>
                                            <td><?php echo $kullanici['ad_soyad']; ?></td>
                                            <td><?php echo $kullanici['email']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $kullanici['rol'] == 'admin' ? 'bg-danger' : 'bg-info'; ?>">
                                                    <?php echo $language->get($kullanici['rol'] == 'admin' ? 'user_role_admin' : 'user_role_user'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $kullanici['durum'] == 'Aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $language->get(strtolower($kullanici['durum'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                if ($kullanici['son_giris']) {
                                                    echo date('d.m.Y H:i', strtotime($kullanici['son_giris']));
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#duzenleModal<?php echo $kullanici['id']; ?>">
                                                    <?php echo $language->get('edit'); ?>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Düzenleme Modal -->
                                        <div class="modal fade" id="duzenleModal<?php echo $kullanici['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><?php echo $language->get('edit_user'); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?php echo $kullanici['id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label"><?php echo $language->get('username'); ?></label>
                                                                <input type="text" class="form-control" value="<?php echo $kullanici['kullanici_adi']; ?>" readonly>
                                                                <div class="form-text"><?php echo $language->get('username_help'); ?></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label"><?php echo $language->get('full_name'); ?></label>
                                                                <input type="text" class="form-control" name="ad_soyad"
                                                                    value="<?php echo $kullanici['ad_soyad']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label"><?php echo $language->get('email'); ?></label>
                                                                <input type="email" class="form-control" name="email"
                                                                    value="<?php echo $kullanici['email']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label"><?php echo $language->get('new_password'); ?></label>
                                                                <input type="password" class="form-control" name="sifre">
                                                                <div class="form-text"><?php echo $language->get('password_help'); ?></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label"><?php echo $language->get('role'); ?></label>
                                                                <select class="form-select" name="rol" required>
                                                                    <option value="kullanici" <?php echo $kullanici['rol'] == 'kullanici' ? 'selected' : ''; ?>>
                                                                        <?php echo $language->get('user_role_user'); ?>
                                                                    </option>
                                                                    <option value="admin" <?php echo $kullanici['rol'] == 'admin' ? 'selected' : ''; ?>>
                                                                        <?php echo $language->get('user_role_admin'); ?>
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label"><?php echo $language->get('status'); ?></label>
                                                                <select class="form-select" name="durum" required>
                                                                    <option value="Aktif" <?php echo $kullanici['durum'] == 'Aktif' ? 'selected' : ''; ?>>
                                                                        <?php echo $language->get('active'); ?>
                                                                    </option>
                                                                    <option value="Pasif" <?php echo $kullanici['durum'] == 'Pasif' ? 'selected' : ''; ?>>
                                                                        <?php echo $language->get('passive'); ?>
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <?php echo $language->get('cancel'); ?>
                                                            </button>
                                                            <button type="submit" name="kullanici_guncelle" class="btn btn-primary">
                                                                <?php echo $language->get('save'); ?>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni Kullanıcı Modal -->
    <div class="modal fade" id="yeniKullaniciModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $language->get('add_new_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><?php echo $language->get('username'); ?></label>
                            <input type="text" class="form-control" name="kullanici_adi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo $language->get('full_name'); ?></label>
                            <input type="text" class="form-control" name="ad_soyad" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo $language->get('email'); ?></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo $language->get('password'); ?></label>
                            <input type="password" class="form-control" name="sifre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo $language->get('role'); ?></label>
                            <select class="form-select" name="rol" required>
                                <option value="kullanici"><?php echo $language->get('user_role_user'); ?></option>
                                <option value="admin"><?php echo $language->get('user_role_admin'); ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo $language->get('status'); ?></label>
                            <select class="form-select" name="durum" required>
                                <option value="Aktif"><?php echo $language->get('active'); ?></option>
                                <option value="Pasif"><?php echo $language->get('passive'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <?php echo $language->get('cancel'); ?>
                        </button>
                        <button type="submit" name="kullanici_ekle" class="btn btn-primary">
                            <?php echo $language->get('save'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>