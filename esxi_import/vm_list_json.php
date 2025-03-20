<?php

/**
 * VM Listesi JSON Endpoint
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/api/VMListJSON.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $vmList = new VMListJSON();
    echo $vmList->getJSON();

    $url = $this->config['post_url'];
    $data = $vmList->getJSON();

    // cURL kullanılabilir mi kontrol et
    if (function_exists('curl_version')) {
        // cURL ile istek gönder
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('cURL Hatası: ' . curl_error($ch));
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
        $result = file_get_contents($url, false, $context);
        
        if ($result === false) {
            throw new Exception('HTTP isteği başarısız oldu');
        }
    }

    echo $result;

} catch (Exception $e) {
    echo $e->getMessage();
}
