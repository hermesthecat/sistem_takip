<?php
return [
    // Genel
    'welcome' => 'Hoş Geldiniz',
    'login' => 'Giriş Yap',
    'logout' => 'Çıkış Yap',
    'profile' => 'Profil',
    'save' => 'Kaydet',
    'cancel' => 'İptal',
    'delete' => 'Sil',
    'edit' => 'Düzenle',
    'add' => 'Ekle',
    
    // Menü
    'dashboard' => 'Kontrol Paneli',
    'physical_servers' => 'Fiziksel Sunucular',
    'virtual_servers' => 'Sanal Sunucular',
    'services' => 'Hizmetler',
    'projects' => 'Projeler',
    'locations' => 'Lokasyonlar',
    'users' => 'Kullanıcılar',
    
    // Sunucu Yönetimi
    'server_name' => 'Sunucu Adı',
    'ip_address' => 'IP Adresi',
    'ram' => 'RAM',
    'cpu' => 'CPU',
    'disk' => 'Disk',
    'location' => 'Lokasyon',
    'project' => 'Proje',
    'status' => 'Durum',
    'created_at' => 'Oluşturma Tarihi',
    
    // Hizmetler
    'service_name' => 'Hizmet Adı',
    'port' => 'Port',
    'description' => 'Açıklama',
    
    // Projeler
    'project_name' => 'Proje Adı',
    'project_code' => 'Proje Kodu',
    
    // Kullanıcılar
    'username' => 'Kullanıcı Adı',
    'full_name' => 'Ad Soyad',
    'email' => 'E-posta',
    'password' => 'Şifre',
    'role' => 'Rol',
    
    // Mesajlar
    'success' => 'İşlem başarıyla tamamlandı.',
    'error' => 'Bir hata oluştu.',
    'confirm_delete' => 'Silmek istediğinizden emin misiniz?',
    
    // Durumlar
    'active' => 'Aktif',
    'passive' => 'Pasif',
    'completed' => 'Tamamlandı',
    
    // Admin Sayfası
    'user_management' => 'Kullanıcı Yönetimi',
    'users_list' => 'Kullanıcılar',
    'add_new_user' => 'Yeni Kullanıcı Ekle',
    'edit_user' => 'Kullanıcı Düzenle',
    'user_id' => 'ID',
    'last_login' => 'Son Giriş',
    'actions' => 'İşlemler',
    'new_password' => 'Yeni Şifre',
    'password_help' => 'Şifreyi değiştirmek istemiyorsanız boş bırakın.',
    'username_help' => 'Kullanıcı adı değiştirilemez.',
    'user_role_admin' => 'Admin',
    'user_role_user' => 'Kullanıcı',
    
    // Hata ve Başarı Mesajları
    'error_username_email_exists' => 'Bu kullanıcı adı veya email zaten kullanılıyor!',
    'error_email_exists' => 'Bu email adresi başka bir kullanıcı tarafından kullanılıyor!',
    'success_user_added' => 'Kullanıcı başarıyla eklendi.',
    'success_user_updated' => 'Kullanıcı başarıyla güncellendi.',
    'error_no_access' => 'Bu sayfaya erişim yetkiniz yok!',
    
    // Fiziksel Sunucu Düzenleme
    'edit_physical_server' => 'Fiziksel Sunucu Düzenle',
    'back_to_physical_servers' => 'Fiziksel Sunuculara Dön',
    'server_details' => 'Sunucu Detayları',
    'select_location' => 'Lokasyon Seçin',
    'add_new_location' => 'Yeni Lokasyon Ekle',
    'select_project' => 'Proje Seçin (Opsiyonel)',
    'add_new_project' => 'Yeni Proje Ekle',
    'cpu_cores' => 'Çekirdek',
    'memory' => 'Bellek',
    'cpu_placeholder' => 'Örn: Intel Xeon E5-2680 v4 2.40GHz',
    'memory_placeholder' => 'Örn: 64GB DDR4',
    'disk_placeholder' => 'Örn: 2x 500GB SSD RAID1',
    'update' => 'Güncelle',
    'error_updating_server' => 'Sunucu güncellenirken bir hata oluştu',
    
    // Fiziksel Sunucu Ekleme
    'add_physical_server' => 'Yeni Fiziksel Sunucu Ekle',
    'error_adding_server' => 'Sunucu eklenirken bir hata oluştu',
    'total_cores' => 'Toplam Çekirdek Sayısı',
    'memory_capacity' => 'Bellek Kapasitesi',
    'disk_capacity' => 'Toplam Disk Kapasitesi',
    
    // Fiziksel Sunucu Silme
    'error_has_virtual_servers' => 'Bu fiziksel sunucuya bağlı {sayi} adet sanal sunucu olduğu için silinemez. Önce bağlı sanal sunucuları silmeniz gerekmektedir.',
    'success_server_deleted' => 'Fiziksel sunucu başarıyla silindi.',
    
    // Hizmet Yönetimi
    'add_service' => 'Yeni Hizmet Ekle',
    'edit_service' => 'Hizmet Düzenle',
    'default_port' => 'Varsayılan Port',
    'existing_services' => 'Mevcut Hizmetler',
    'service_updated' => 'Hizmet başarıyla güncellendi.',
    'service_added' => 'Yeni hizmet başarıyla eklendi.',
    'service_usage' => 'Kullanım',
    'not_in_use' => 'Kullanılmıyor',
    'server_count' => '{count} sunucu',
    'confirm_delete_service' => 'Bu hizmeti silmek istediğinize emin misiniz?',
    'new_service' => 'Yeni Hizmet',
]; 