<?php
class Language {
    private static $instance = null;
    private $messages = [];
    private $currentLang = 'tr';
    private $availableLangs = ['tr', 'en'];
    
    private function __construct() {
        $this->loadLanguage();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function setLanguage($lang) {
        if (in_array($lang, $this->availableLangs)) {
            $this->currentLang = $lang;
            $this->loadLanguage();
            
            // Dil tercihini session'a kaydet
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['lang'] = $lang;
        }
    }
    
    private function loadLanguage() {
        $langFile = dirname(__DIR__) . '/languages/' . $this->currentLang . '/messages.php';
        if (file_exists($langFile)) {
            $this->messages = require $langFile;
        }
    }
    
    public function get($key, $params = []) {
        $message = isset($this->messages[$key]) ? $this->messages[$key] : $key;
        
        // Parametreleri değiştir
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $message = str_replace(':' . $param, $value, $message);
            }
        }
        
        return $message;
    }
    
    public function getCurrentLang() {
        return $this->currentLang;
    }
    
    public function getAvailableLangs() {
        return $this->availableLangs;
    }
} 