<?php

/**
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/config/language.php';

if (isset($_POST['lang'])) {
    $lang = $_POST['lang'];
    $language = Language::getInstance();
    $language->setLanguage($lang);

    // Önceki sayfaya yönlendir
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    header('Location: ' . $referer);
    exit;
}
