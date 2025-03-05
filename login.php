<?php

/**
 * @author A. Kerem Gök
 */

session_start();

date_default_timezone_set('Europe/Istanbul');

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['kullanici_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/language.php';
$language = Language::getInstance();

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici_adi = mysqli_real_escape_string($conn, $_POST['kullanici_adi']);
    $sifre = $_POST['sifre'];

    $sql = "SELECT * FROM kullanicilar WHERE kullanici_adi = '$kullanici_adi' AND durum = 'Aktif'";
    $result = mysqli_query($conn, $sql);

    if ($kullanici = mysqli_fetch_assoc($result)) {
        if (password_verify($sifre, $kullanici['sifre'])) {
            // Giriş başarılı
            $_SESSION['kullanici_id'] = $kullanici['id'];
            $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];
            $_SESSION['ad_soyad'] = $kullanici['ad_soyad'];
            $_SESSION['rol'] = $kullanici['rol'];

            // Son giriş tarihini güncelle
            $sql = "UPDATE kullanicilar SET son_giris = NOW() WHERE id = " . $kullanici['id'];
            mysqli_query($conn, $sql);

            header('Location: index.php');
            exit;
        } else {
            $mesaj = "<div class='alert alert-danger'>" . $language->get('error_invalid_credentials') . "</div>";
        }
    } else {
        $mesaj = "<div class='alert alert-danger'>" . $language->get('error_invalid_credentials') . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $language->get('login_page_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
        }

        .login-container {
            max-width: 400px;
            padding: 15px;
        }
    </style>
</head>

<body class="d-flex align-items-center py-4 bg-body-tertiary">
    <main class="login-container w-100 m-auto">
        <form method="POST" class="card">
            <div class="card-body">
                <h1 class="h3 mb-3 fw-normal text-center"><?php echo $language->get('login_form_title'); ?></h1>

                <?php echo $mesaj; ?>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi"
                        placeholder="<?php echo $language->get('username_placeholder'); ?>" required>
                    <label for="kullanici_adi"><?php echo $language->get('username_placeholder'); ?></label>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="sifre" name="sifre"
                        placeholder="<?php echo $language->get('password_placeholder'); ?>" required>
                    <label for="sifre"><?php echo $language->get('password_placeholder'); ?></label>
                </div>

                <button class="w-100 btn btn-lg btn-primary" type="submit"><?php echo $language->get('login_button'); ?></button>
            </div>
        </form>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>