<?php

/**
 * VM Listesi JSON Endpoint
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/api/VMListJSON.php';
require_once __DIR__ . '/api/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $vmList = new VMListJSON();
    $json_data = $vmList->getJSON();
    
    $config = require __DIR__ . '/api/config.php';
    $url = $config['post_url'];
    $data = $json_data;

    // Debug bilgileri
    error_log("POST İsteği Başlatılıyor:");
    error_log("URL: " . $url);
    error_log("Data: " . $data);
    error_log("Data Length: " . strlen($data) . " bytes");

    // cURL kullanılabilir mi kontrol et
    if (function_exists('curl_version')) {
        error_log("cURL kullanılıyor");
        
        // cURL ile istek gönder
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        // Debug için verbose log
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        $start_time = microtime(true);
        $result = curl_exec($ch);
        $end_time = microtime(true);
        
        // cURL bilgilerini al
        $info = curl_getinfo($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Verbose log'u oku
        rewind($verbose);
        $verbose_log = stream_get_contents($verbose);
        
        error_log("cURL Debug Bilgileri:");
        error_log("HTTP Code: " . $http_code);
        error_log("Total Time: " . ($end_time - $start_time) . " seconds");
        error_log("Verbose Log: " . $verbose_log);
        error_log("cURL Info: " . print_r($info, true));
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            error_log("cURL Hatası: " . $error);
            throw new Exception('cURL Hatası: ' . $error);
        }
        
        curl_close($ch);
    } else {
        error_log("file_get_contents kullanılıyor");
        
        // Alternatif olarak file_get_contents kullan
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => $data
            ]
        ];

        $context = stream_context_create($options);
        
        $start_time = microtime(true);
        $result = file_get_contents($url, false, $context);
        $end_time = microtime(true);
        
        error_log("file_get_contents Debug Bilgileri:");
        error_log("Total Time: " . ($end_time - $start_time) . " seconds");
        
        if ($result === false) {
            $error = error_get_last();
            error_log("file_get_contents Hatası: " . print_r($error, true));
            throw new Exception('HTTP isteği başarısız oldu');
        }
    }

    error_log("POST İsteği Tamamlandı");
    error_log("Response: " . $result);
    
    // JSON çıktısını ver
    echo $json_data;
} catch (Exception $e) {
    error_log("Hata Oluştu: " . $e->getMessage());
    error_log("Stack Trace: " . $e->getTraceAsString());
    
    // Hata durumunda JSON formatında hata mesajı döndür
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
