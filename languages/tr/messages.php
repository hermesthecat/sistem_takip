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
    
    // Hizmet Silme
    'error_service_id_missing' => 'Hizmet ID belirtilmedi.',
    'error_service_in_use' => 'Bu hizmet {count} sanal sunucu tarafından kullanılıyor. Önce bu sunuculardan hizmeti kaldırın.',
    'success_service_deleted' => 'Hizmet başarıyla silindi.',
    'error_deleting_service' => 'Hizmet silinirken bir hata oluştu: {error}',
    
    // Ana Sayfa
    'server_tracking_system' => 'Sunucu Takip Sistemi',
    'hardware' => 'Donanım',
    'virtual_servers_count' => '{count} sanal sunucu',
    'no_virtual_servers' => 'Yok',
    'virtual_servers_button' => 'Sanal Sunucular',
    'physical_server_resources' => 'Fiziksel Sunucu Kaynakları:',
    'core_usage' => 'Çekirdek Kullanımı',
    'memory_usage' => 'Bellek Kullanımı',
    'disk_usage' => 'Disk Kullanımı',
    'cores' => 'Çekirdek',
    'core_count' => '{used}/{total} Çekirdek',
    'gb_count' => '{used}/{total} GB',
    'hardware_cpu' => 'Çekirdek: {value}',
    'hardware_memory' => 'Bellek: {value}',
    'hardware_disk' => 'Disk: {value}',
    'no_physical_servers' => 'Henüz fiziksel sunucu eklenmemiş.',
    
    // Giriş Sayfası
    'login_page_title' => 'Giriş Yap - Sunucu Takip Sistemi',
    'error_invalid_credentials' => 'Kullanıcı adı veya şifre hatalı!',
    'login_form_title' => 'Sunucu Takip Sistemi',
    'username_placeholder' => 'Kullanıcı Adı',
    'password_placeholder' => 'Şifre',
    'login_button' => 'Giriş Yap',
    
    // Lokasyon Yönetimi
    'location_management' => 'Lokasyon Yönetimi',
    'add_new_location_title' => 'Yeni Lokasyon Ekle',
    'edit_location_title' => 'Lokasyon Düzenle',
    'location_name' => 'Lokasyon Adı',
    'existing_locations' => 'Mevcut Lokasyonlar',
    'server_count_info' => '{count} sunucu',
    'no_servers' => 'Sunucu yok',
    'error_location_exists' => 'Hata: Bu lokasyon adı zaten kullanılıyor!',
    'success_location_added' => 'Yeni lokasyon başarıyla eklendi.',
    'success_location_updated' => 'Lokasyon başarıyla güncellendi.',
    'confirm_delete_location' => 'Bu lokasyonu silmek istediğinize emin misiniz?',
    
    // Lokasyon Silme
    'error_location_has_servers' => 'Bu lokasyona bağlı sunucular olduğu için silinemez.',
    'success_location_deleted' => 'Lokasyon başarıyla silindi.',
    
    // Profil Sayfası
    'profile_page_title' => 'Profil - {name}',
    'profile_information' => 'Profil Bilgileri',
    'account_information' => 'Hesap Bilgileri',
    'current_password' => 'Mevcut Şifre',
    'new_password' => 'Yeni Şifre',
    'current_password_help' => 'Değişiklikleri onaylamak için mevcut şifrenizi girin.',
    'registration_date' => 'Kayıt Tarihi',
    'error_current_password' => 'Mevcut şifreniz hatalı!',
    'success_profile_updated' => 'Profiliniz başarıyla güncellendi.',
    'error_updating_profile' => 'Hata: {error}',
    
    // Proje Yönetimi
    'project_management' => 'Proje Yönetimi',
    'add_new_project' => 'Yeni Proje Ekle',
    'edit_project' => 'Proje Düzenle',
    'project_code' => 'Proje Kodu',
    'project_code_placeholder' => 'Örn: PRJ-2024',
    'existing_projects' => 'Mevcut Projeler',
    'error_project_code_exists' => 'Hata: Bu proje kodu zaten kullanılıyor!',
    'success_project_added' => 'Yeni proje başarıyla eklendi.',
    'success_project_updated' => 'Proje başarıyla güncellendi.',
    'error_updating_project' => 'Hata: {error}',
    'physical_server_count' => '{count} fiziksel',
    'virtual_server_count' => '{count} sanal',
    'confirm_delete_project' => 'Bu projeyi silmek istediğinize emin misiniz?',
    
    // Proje Silme
    'error_project_has_servers' => 'Bu projeye bağlı {fiziksel} fiziksel sunucu ve {sanal} sanal sunucu olduğu için silinemez.',
    'error_project_has_physical_servers' => 'Bu projeye bağlı {count} fiziksel sunucu olduğu için silinemez.',
    'error_project_has_virtual_servers' => 'Bu projeye bağlı {count} sanal sunucu olduğu için silinemez.',
    'success_project_deleted' => 'Proje başarıyla silindi.',
    'error_deleting_project' => 'Proje silinirken bir hata oluştu: {error}',
    
    // Sanal Sunucu Detay
    'virtual_server_detail' => 'Sanal Sunucu Detayı',
    'back_to_virtual_servers' => 'Sanal Sunuculara Dön',
    'running_services' => 'Çalışan Hizmetler',
    'add_existing_service' => 'Mevcut Hizmet Ekle',
    'add_new_service' => 'Yeni Hizmet Ekle',
    'service_port' => 'Port',
    'service_notes' => 'Notlar',
    'default_port' => 'Varsayılan port',
    'no_services_added' => 'Henüz hizmet eklenmemiş.',
    'edit_service' => 'Hizmet Düzenle',
    'remove_service' => 'Kaldır',
    'confirm_remove_service' => 'Bu hizmeti kaldırmak istediğinize emin misiniz?',
    'service_added_success' => 'Yeni hizmet başarıyla eklendi ve sunucuya tanımlandı.',
    'service_add_error' => 'Hizmet eklenirken hata: {error}',
    'service_assign_error' => 'Hizmet eklendi fakat sunucuya tanımlanırken hata: {error}',
    'service_updated_success' => 'Hizmet başarıyla güncellendi.',
    'service_removed_success' => 'Hizmet başarıyla kaldırıldı.',
    'service_action_error' => 'Hata: {error}',
    'physical_server' => 'Fiziksel Sunucu',
    'project_info' => '{project_name} ({project_code})',
    'no_project_assigned' => '-',
    'update' => 'Güncelle',
]; 