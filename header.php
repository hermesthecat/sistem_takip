<?php
/**
 * @author A. Kerem Gök
 */

require_once 'config/Language.php';
$language = Language::getInstance();

// Aktif sayfayı belirle
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"><?php echo $language->get('dashboard'); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" 
                       href="index.php"><?php echo $language->get('physical_servers'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'proje_ekle.php' ? 'active' : ''; ?>" 
                       href="proje_ekle.php"><?php echo $language->get('projects'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'lokasyon_ekle.php' ? 'active' : ''; ?>" 
                       href="lokasyon_ekle.php"><?php echo $language->get('locations'); ?></a>
                </li>
                <?php if ($_SESSION['rol'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'admin.php' ? 'active' : ''; ?>" 
                           href="admin.php"><?php echo $language->get('users'); ?></a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <!-- Dil Seçimi -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-globe"></i>
                        <?php echo strtoupper($language->getCurrentLang()); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach ($language->getAvailableLangs() as $lang): ?>
                            <li>
                                <form action="change_language.php" method="post" style="display: inline;">
                                    <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                                    <button type="submit" class="dropdown-item <?php echo $language->getCurrentLang() == $lang ? 'active' : ''; ?>">
                                        <?php echo strtoupper($lang); ?>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                
                <!-- Kullanıcı Menüsü -->
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
                                <i class="bi bi-person"></i> <?php echo $language->get('profile'); ?>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> <?php echo $language->get('logout'); ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav> 