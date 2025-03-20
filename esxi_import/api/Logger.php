<?php

/**
 * vCenter API için merkezi loglama sınıfı
 * 
 * Bu sınıf, vCenter API'si tarafından üretilen tüm logları yönetir.
 * Her istek için benzersiz bir request ID oluşturur ve tüm logları bu ID ile ilişkilendirir.
 * Loglar JSON formatında ve insan tarafından okunabilir şekilde kaydedilir.
 * 
 * @package api
 * @author A. Kerem Gök
 * @version 1.0.0
 */

class Logger
{
    /** 
     * Varsayılan log dosyası yolu
     * @var string 
     */
    protected $log_file;

    /** 
     * Mevcut istek için benzersiz ID
     * @var string 
     */
    protected $request_id;

    /**
     * Log dizini yolu
     * @var string
     */
    protected $log_dir;

    /**
     * Log buffer'ı - Performans için loglar önce buffer'da biriktirilir
     * @var string
     */
    protected $buffer = '';

    /**
     * Buffer'daki veri boyutu (byte cinsinden)
     * @var int
     */
    protected $buffer_size = 0;

    /**
     * Maksimum buffer boyutu (512KB)
     * Buffer bu boyuta ulaştığında otomatik flush edilir
     * @var int
     */
    protected $max_buffer_size = 512 * 1024;

    /**
     * Logger constructor
     * 
     * Yeni bir Logger örneği oluşturur.
     * Her örnek için benzersiz bir request ID oluşturur.
     * Varsayılan log dosyasını ayarlar.
     * Log dizininin varlığını kontrol eder, yoksa oluşturur.
     * Program sonlandığında buffer'ı temizlemek için shutdown hook ekler.
     * 
     * @throws Exception Log dizini oluşturulamazsa
     */
    public function __construct()
    {
        $this->request_id = uniqid();
        $this->log_dir = dirname(__DIR__) . '/logs';
        $this->log_file = $this->log_dir . '/vcenter-logger.log';

        if (!is_dir($this->log_dir)) {
            mkdir($this->log_dir, 0755, true);
        }

        register_shutdown_function([$this, 'flush']);
    }

    /**
     * Log mesajı yazar
     * 
     * Verilen mesajı ve opsiyonel verileri log dosyasına yazar.
     * Log formatı:
     * [Tarih] [Request ID] Mesaj
     * Data: {JSON formatında veri}
     * ----------------------------------------
     * 
     * Performans için loglar önce buffer'da biriktirilir.
     * Buffer maksimum boyuta ulaştığında otomatik olarak flush edilir.
     * 
     * @param string $message Loglanacak mesaj
     * @param array|null $data Loglanacak ek veriler (opsiyonel)
     * @param string|null $log_file Özel log dosyası yolu (opsiyonel)
     * 
     * @throws Exception Eğer log dosyası yazılabilir değilse veya log yazma sırasında hata oluşursa
     */
    public function log($message, $data = null, $log_file = null)
    {
        try {
            $target_log_file = $log_file ? $this->log_dir . '/' . $log_file . '.log' : $this->log_file;

            $log = '[' . date('Y-m-d H:i:s') . "] [$this->request_id] $message";
            if ($data !== null) {
                $log .= "\nData: " . json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            $log .= "\n----------------------------------------\n";

            $this->buffer .= $log;
            $this->buffer_size += strlen($log);

            if ($this->buffer_size >= $this->max_buffer_size) {
                $this->flush();
            }
        } catch (Exception $e) {
            error_log("Log yazma hatası: " . $e->getMessage());
        }
    }

    /**
     * Buffer'daki logları dosyaya yazar
     * 
     * Buffer'da biriken logları dosyaya yazarak buffer'ı temizler.
     * Dosya yazma işlemi sırasında LOCK_EX ile dosya kilitlenerek
     * çoklu process'lerde güvenli yazma sağlanır.
     * 
     * @throws Exception Dosya yazma hatası durumunda
     */
    public function flush()
    {
        if ($this->buffer_size > 0) {
            file_put_contents($this->log_file, $this->buffer, FILE_APPEND | LOCK_EX);
            $this->buffer = '';
            $this->buffer_size = 0;
        }
    }

    /**
     * Destructor
     * 
     * Nesne yok edilmeden önce buffer'da kalan logları
     * dosyaya yazmayı garantiler.
     */
    public function __destruct()
    {
        $this->flush();
    }
}
