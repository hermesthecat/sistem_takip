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
    echo $vmList->getJSON();

    $config = require __DIR__ . '/api/config.php';
    $url = $config['post_url'];
    $data = $vmList->getJSON();

    // Debug bilgileri
    echo "POST İsteği Başlatılıyor:";
    echo "URL: " . $url;
    echo "Data: " . $data;
    echo "Data Length: " . strlen($data) . " bytes";

    // cURL kullanılabilir mi kontrol et
    if (function_exists('curl_version')) {
        echo "cURL kullanılıyor";
        
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
        
        echo "cURL Debug Bilgileri:";
        echo "HTTP Code: " . $http_code;
        echo "Total Time: " . ($end_time - $start_time) . " seconds";
        echo "Verbose Log: " . $verbose_log;
        echo "cURL Info: " . print_r($info, true);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            echo "cURL Hatası: " . $error;
            throw new Exception('cURL Hatası: ' . $error);
        }
        
        curl_close($ch);
    } else {
        echo "file_get_contents kullanılıyor";
        
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
        
        echo "file_get_contents Debug Bilgileri:";
        echo "Total Time: " . ($end_time - $start_time) . " seconds";
        
        if ($result === false) {
            $error = error_get_last();
            echo "file_get_contents Hatası: " . print_r($error, true);
            throw new Exception('HTTP isteği başarısız oldu');
        }
    }

    echo "POST İsteği Tamamlandı";
    echo "Response: " . $result;
    
    echo $result;
} catch (Exception $e) {
    echo "Hata Oluştu: " . $e->getMessage();
    echo "Stack Trace: " . $e->getTraceAsString();
    echo $e->getMessage();
}
