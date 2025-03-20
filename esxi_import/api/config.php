<?php

/**
 * Hata ayıklama modu
 * Geliştirme ortamında tüm hataları göster
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * vCenter API Konfigürasyon
 * 
 * Bu dosya vCenter API'si için gerekli tüm yapılandırma ayarlarını içerir.
 * Üretim ortamında bu dosyadaki hassas bilgilerin (kullanıcı adı, şifre) 
 * güvenli bir şekilde saklanması kritik öneme sahiptir.
 * 
 * @package api
 * @author A. Kerem Gök
 * @version 1.0.0
 */

return [
    // SSL doğrulaması
    // Geliştirme ortamında false olarak ayarlanabilir
    // Üretim ortamında mutlaka true yapılmalıdır
    'ssl_verify' => false,

    // vCenter bağlantı bilgileri
    'vcenter' => [
        // vCenter sunucu adresi
        'host' => 'vcenter.local',

        // vCenter yönetici kullanıcı bilgileri
        // Üretimde bu bilgiler .env dosyasından alınmalıdır
        'username' => 'administrator@vshere.local',
        'password' => 'test1234'
    ],

    // Zaman aşımı ayarları (saniye cinsinden)
    'timeout' => [
        // Bağlantı kurma için maksimum süre
        'connection' => 120,

        // Komut çalıştırma için maksimum süre
        'execution' => 300
    ],

    'post_token' => '54ryrtyrtyr5466',

    'post_url' => 'http://noreplay.email/import_esxi.php',

    'physical_machine_id' => 1
];
