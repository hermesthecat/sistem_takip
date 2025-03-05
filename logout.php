<?php

/**
 * @author A. Kerem Gök
 */

session_start();

// Tüm session değişkenlerini temizle
$_SESSION = array();

// Session'ı sonlandır
session_destroy();

// Login sayfasına yönlendir
header('Location: login.php');
exit;
