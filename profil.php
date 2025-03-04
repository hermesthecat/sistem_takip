<?php
/**
 * @author A. Kerem Gök
 */
require_once 'auth.php';
require_once 'config/database.php';
require_once 'config/language.php';

$mesaj = '';

// Kullanıcı bilgilerini al
$id = $_SESSION['kullanici_id'];
$sql = "SELECT * FROM kullanicilar WHERE id = '$id'";
$result = mysqli_query($conn, $sql);
$kullanici = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ad_soyad = mysqli_real_escape_string($conn, $_POST['ad_soyad']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $yeni_sifre = $_POST['yeni_sifre'];
    $mevcut_sifre = $_POST['mevcut_sifre'];
    
    // Email kontrolü (kendi emaili hariç)
    $email_kontrol = mysqli_query($conn, "SELECT id FROM kullanicilar WHERE email = '$email' AND id != '$id'");
    if (mysqli_num_rows($email_kontrol) > 0) {
        $mesaj = "<div class='alert alert-danger'>" . $language->get('error_email_exists') . "</div>";
    } else {
        // Mevcut şifre kontrolü
        if (password_verify($mevcut_sifre, $kullanici['sifre'])) {
            $sql = "UPDATE kullanicilar SET 
                    ad_soyad = '$ad_soyad',
                    email = '$email'";
            
            // Yeni şifre girilmişse güncelle
            if (!empty($yeni_sifre)) {
                $hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                $sql .= ", sifre = '$hash'";
            }
            
            $sql .= " WHERE id = '$id'";
            
            if (mysqli_query($conn, $sql)) {
                $mesaj = "<div class='alert alert-success'>" . $language->get('success_profile_updated') . "</div>";
                // Güncel kullanıcı bilgilerini al
                $result = mysqli_query($conn, "SELECT * FROM kullanicilar WHERE id = '$id'");
                $kullanici = mysqli_fetch_assoc($result);
                // Session'daki ad soyadı güncelle
                $_SESSION['ad_soyad'] = $kullanici['ad_soyad'];
            } else {
                $mesaj = "<div class='alert alert-danger'>" . str_replace('{error}', mysqli_error($conn), $language->get('error_updating_profile')) . "</div>";
            }
        } else {
            $mesaj = "<div class='alert alert-danger'>" . $language->get('error_current_password') . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo str_replace('{name}', $kullanici['ad_soyad'], $language->get('profile_page_title')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <div class="container">
        <?php echo $mesaj; ?>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title h3"><?php echo $language->get('profile_information'); ?></h1>
                    </div>
                    <div class="card-body">
                        <?php echo $mesaj; ?>
                        
                        <form method="POST">
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
                                <input type="password" class="form-control" name="yeni_sifre">
                                <div class="form-text"><?php echo $language->get('password_help'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $language->get('current_password'); ?></label>
                                <input type="password" class="form-control" name="mevcut_sifre" required>
                                <div class="form-text"><?php echo $language->get('current_password_help'); ?></div>
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo $language->get('update'); ?></button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h2 class="card-title h5 mb-0"><?php echo $language->get('account_information'); ?></h2>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4"><?php echo $language->get('role'); ?></dt>
                            <dd class="col-sm-8">
                                <span class="badge <?php echo $kullanici['rol'] == 'admin' ? 'bg-danger' : 'bg-info'; ?>">
                                    <?php echo $language->get('user_role_' . $kullanici['rol']); ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-4"><?php echo $language->get('status'); ?></dt>
                            <dd class="col-sm-8">
                                <span class="badge <?php echo $kullanici['durum'] == 'Aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $language->get($kullanici['durum'] == 'Aktif' ? 'active' : 'passive'); ?>
                                </span>
                            </dd>
                            
                            <dt class="col-sm-4"><?php echo $language->get('last_login'); ?></dt>
                            <dd class="col-sm-8">
                                <?php 
                                if ($kullanici['son_giris']) {
                                    echo date('d.m.Y H:i', strtotime($kullanici['son_giris']));
                                } else {
                                    echo '<span class="text-muted">-</span>';
                                }
                                ?>
                            </dd>
                            
                            <dt class="col-sm-4"><?php echo $language->get('registration_date'); ?></dt>
                            <dd class="col-sm-8">
                                <?php echo date('d.m.Y H:i', strtotime($kullanici['olusturma_tarihi'])); ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 