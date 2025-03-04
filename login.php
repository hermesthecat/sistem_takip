<?php
/**
 * @author A. Kerem Gök
 */
session_start();

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['kullanici_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/database.php';

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
            $mesaj = "<div class='alert alert-danger'>Kullanıcı adı veya şifre hatalı!</div>";
        }
    } else {
        $mesaj = "<div class='alert alert-danger'>Kullanıcı adı veya şifre hatalı!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap - Sunucu Takip Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
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
                <h1 class="h3 mb-3 fw-normal text-center">Sunucu Takip Sistemi</h1>
                
                <?php echo $mesaj; ?>
                
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" 
                           placeholder="Kullanıcı Adı" required>
                    <label for="kullanici_adi">Kullanıcı Adı</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="sifre" name="sifre" 
                           placeholder="Şifre" required>
                    <label for="sifre">Şifre</label>
                </div>
                
                <button class="w-100 btn btn-lg btn-primary" type="submit">Giriş Yap</button>
            </div>
        </form>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 