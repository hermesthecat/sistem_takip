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

    // cURL kullanılabilir mi kontrol et
    if (function_exists('curl_version')) {

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

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            throw new Exception('cURL Hatası: ' . $error);
        }

        curl_close($ch);
    } else {

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

        if ($result === false) {
            $error = error_get_last();
            error_log("file_get_contents Hatası: " . print_r($error, true));
            throw new Exception('HTTP isteği başarısız oldu');
        }
    }
} catch (Exception $e) {

    // Hata durumunda JSON formatında hata mesajı döndür
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
