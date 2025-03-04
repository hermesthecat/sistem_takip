<?php
/**
 * @author A. Kerem Gök
 */

// Aktif sayfayı belirle
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Sunucu Takip</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" 
                       href="index.php">Fiziksel Sunucular</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'proje_ekle.php' ? 'active' : ''; ?>" 
                       href="proje_ekle.php">Projeler</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'lokasyon_ekle.php' ? 'active' : ''; ?>" 
                       href="lokasyon_ekle.php">Lokasyonlar</a>
                </li>
                <?php if ($_SESSION['rol'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'admin.php' ? 'active' : ''; ?>" 
                           href="admin.php">Kullanıcı Yönetimi</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <?php echo $_SESSION['ad_soyad']; ?>
                        <span class="badge <?php echo $_SESSION['rol'] == 'admin' ? 'bg-danger' : 'bg-info'; ?> ms-1">
                            <?php echo $_SESSION['rol']; ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item <?php echo $current_page == 'profil.php' ? 'active' : ''; ?>" 
                               href="profil.php">
                                <i class="bi bi-person"></i> Profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Çıkış Yap
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav> 