<?php
/**
 * @author A. Kerem Gök
 */
session_start();

// Giriş kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}

// Admin kontrolü
if ($_SESSION['rol'] !== 'admin') {
    header('Location: index.php?hata=' . urlencode('Bu sayfaya erişim yetkiniz yok!'));
    exit;
}

require_once 'config/database.php';

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
                $mesaj = "<div class='alert alert-danger'>Bu kullanıcı adı veya email zaten kullanılıyor!</div>";
            } else {
                $sql = "INSERT INTO kullanicilar (kullanici_adi, ad_soyad, email, sifre, rol, durum) 
                        VALUES ('$kullanici_adi', '$ad_soyad', '$email', '$sifre', '$rol', '$durum')";
                
                if (mysqli_query($conn, $sql)) {
                    $mesaj = "<div class='alert alert-success'>Kullanıcı başarıyla eklendi.</div>";
                } else {
                    $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
                }
            }
        } else {
            // Kullanıcı güncelleme
            $id = mysqli_real_escape_string($conn, $_POST['id']);
            
            // Email kontrolü (kendi emaili hariç)
            $kontrol = mysqli_query($conn, "SELECT id FROM kullanicilar WHERE email = '$email' AND id != '$id'");
            if (mysqli_num_rows($kontrol) > 0) {
                $mesaj = "<div class='alert alert-danger'>Bu email adresi başka bir kullanıcı tarafından kullanılıyor!</div>";
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
                    $mesaj = "<div class='alert alert-success'>Kullanıcı başarıyla güncellendi.</div>";
                } else {
                    $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($conn) . "</div>";
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
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Yönetimi - Sunucu Takip Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <div class="container">
        <?php echo $mesaj; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="card-title h5 mb-0">Kullanıcılar</h2>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#yeniKullaniciModal">
                            Yeni Kullanıcı Ekle
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kullanıcı Adı</th>
                                        <th>Ad Soyad</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Durum</th>
                                        <th>Son Giriş</th>
                                        <th>İşlemler</th>
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
                                                    <?php echo $kullanici['rol']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $kullanici['durum'] == 'Aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $kullanici['durum']; ?>
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
                                                    Düzenle
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Düzenleme Modal -->
                                        <div class="modal fade" id="duzenleModal<?php echo $kullanici['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Kullanıcı Düzenle</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?php echo $kullanici['id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Kullanıcı Adı</label>
                                                                <input type="text" class="form-control" value="<?php echo $kullanici['kullanici_adi']; ?>" readonly>
                                                                <div class="form-text">Kullanıcı adı değiştirilemez.</div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Ad Soyad</label>
                                                                <input type="text" class="form-control" name="ad_soyad" 
                                                                       value="<?php echo $kullanici['ad_soyad']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" class="form-control" name="email" 
                                                                       value="<?php echo $kullanici['email']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Yeni Şifre</label>
                                                                <input type="password" class="form-control" name="sifre">
                                                                <div class="form-text">Şifreyi değiştirmek istemiyorsanız boş bırakın.</div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Rol</label>
                                                                <select class="form-select" name="rol" required>
                                                                    <option value="kullanici" <?php echo $kullanici['rol'] == 'kullanici' ? 'selected' : ''; ?>>
                                                                        Kullanıcı
                                                                    </option>
                                                                    <option value="admin" <?php echo $kullanici['rol'] == 'admin' ? 'selected' : ''; ?>>
                                                                        Admin
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Durum</label>
                                                                <select class="form-select" name="durum" required>
                                                                    <option value="Aktif" <?php echo $kullanici['durum'] == 'Aktif' ? 'selected' : ''; ?>>
                                                                        Aktif
                                                                    </option>
                                                                    <option value="Pasif" <?php echo $kullanici['durum'] == 'Pasif' ? 'selected' : ''; ?>>
                                                                        Pasif
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                            <button type="submit" name="kullanici_guncelle" class="btn btn-primary">Güncelle</button>
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
                    <h5 class="modal-title">Yeni Kullanıcı Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" name="kullanici_adi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" name="ad_soyad" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Şifre</label>
                            <input type="password" class="form-control" name="sifre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" name="rol" required>
                                <option value="kullanici">Kullanıcı</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Durum</label>
                            <select class="form-select" name="durum" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Pasif">Pasif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" name="kullanici_ekle" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 