<?php

/**
 * vCenter SOAP API Bağlantısı
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/vmwarephp/library/Vmwarephp/Autoloader.php';
require_once __DIR__ . '/Logger.php';

// Autoloader'ı başlat
$autoloader = new Vmwarephp\Autoloader();
$autoloader->register();

use Vmwarephp\Vhost;
use Vmwarephp\Service;

class VCenterSOAP extends Logger
{

    /** @var Service */
    protected $service;

    /** @var string */
    protected $log_file;

    /** @var array */
    protected $config;

    /** @var int */
    protected $max_retries = 3;

    /** @var int */
    protected $retry_delay = 2;

    /** @var string */
    protected $request_id;

    /** @var Logger */
    protected $logger;

    /** @var array */
    protected static $connection_pool = [];

    /** @var array */
    protected static $vm_cache = [];

    /** @var int */
    protected $cache_ttl = 300; // 5 dakika

    /**
     * VCenterSOAP constructor
     * @throws Exception
     */
    public function __construct()
    {
        $this->request_id = uniqid();
        $this->config = require __DIR__ . '/config.php';

        $this->log_file = dirname(__DIR__) . '/logs/vcenter-soap.log';

        $this->log("SOAP API başlatılıyor", [
            'request_id' => $this->request_id,
            'config' => [
                'host' => $this->config['vcenter']['host'],
                'username' => $this->config['vcenter']['username']
            ]
        ]);

        $this->initSoapService();
    }

    public function findVM($vm_id)
    {
        $cache_key = "vm_" . $vm_id;

        // Cache kontrol
        if (
            isset(self::$vm_cache[$cache_key]) &&
            (time() - self::$vm_cache[$cache_key]['time'] < $this->cache_ttl)
        ) {
            $this->log("VM cache'den alındı", ['vm' => $vm_id]);
            return self::$vm_cache[$cache_key]['data'];
        }

        $this->log("VM aranıyor", ['vm' => $vm_id]);

        try {
            $vms = $this->service->findAllManagedObjects('VirtualMachine', [
                'name',
                'runtime.powerState',
                'config.guestFullName',
                'config.instanceUuid',
                'snapshot',
                'snapshot.rootSnapshotList',
                'snapshot.currentSnapshot'
            ]);

            foreach ($vms as $candidate) {
                if ($candidate->reference->_ === $vm_id) {
                    // Cache'e ekle
                    self::$vm_cache[$cache_key] = [
                        'data' => $candidate,
                        'time' => time()
                    ];
                    return $candidate;
                }
            }

            throw new Exception("VM bulunamadı: $vm_id");
        } catch (Exception $e) {
            $this->log("VM arama hatası", [
                'vm' => $vm_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * SOAP Service başlat
     * @throws Exception
     */
    protected function initSoapService()
    {
        $host = $this->config['vcenter']['host'];

        // Connection pool kontrol
        if (
            isset(self::$connection_pool[$host]) &&
            self::$connection_pool[$host]['service']->isConnected()
        ) {
            $this->service = self::$connection_pool[$host]['service'];
            return $this->service;
        }

        $context = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'ciphers' => 'ALL:@SECLEVEL=0',
                'disable_compression' => true,
                'SNI_enabled' => false
            ],
            'http' => [
                'timeout' => $this->config['timeout']['connection'],
                'protocol_version' => '1.1',
                'header' => [
                    'Connection: keep-alive',
                    'Keep-Alive: 3600'  // 1 saat
                ]
            ]
        ];

        stream_context_set_default($context);

        // SOAP optimizasyonları
        ini_set('soap.wsdl_cache_enabled', 1);
        ini_set('soap.wsdl_cache_ttl', 3600);  // 1 saat

        $vhost = new Vhost(
            $this->config['vcenter']['host'],
            $this->config['vcenter']['username'],
            $this->config['vcenter']['password']
        );

        $this->service = new Service($vhost);
        $this->service->connect();

        // Connection pool'a ekle
        self::$connection_pool[$host] = [
            'service' => $this->service,
            'time' => time()
        ];

        return $this->service;
    }

    /**
     * SOAP isteği yap
     * @param string $method Metod adı
     * @param array $params Parametreler
     * @return mixed
     * @throws Exception
     */
    protected function makeSoapRequest($method, $params = [])
    {
        $attempt = 1;
        $last_error = null;
        $start_time = microtime(true);
        $request_id = uniqid();

        while ($attempt <= $this->max_retries) {
            try {
                $this->log("SOAP istek başlatıldı", [
                    'request_id' => $request_id,
                    'method' => $method,
                    'params' => $params,
                    'attempt' => $attempt,
                    'start_time' => date('Y-m-d H:i:s')
                ]);

                $result = $this->service->$method($params);

                $this->log("SOAP istek başarılı", [
                    'request_id' => $request_id,
                    'method' => $method,
                    'attempt' => $attempt,
                    'duration' => round(microtime(true) - $start_time, 2),
                    'result' => $result
                ]);

                return $result;
            } catch (Exception $e) {
                $last_error = $e;
                $this->log("SOAP istek hatası", [
                    'request_id' => $request_id,
                    'method' => $method,
                    'params' => $params,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'elapsed_time' => round(microtime(true) - $start_time, 2)
                ]);

                if ($attempt < $this->max_retries) {
                    $this->log("Yeniden deneme bekleniyor", [
                        'request_id' => $request_id,
                        'method' => $method,
                        'attempt' => $attempt,
                        'delay' => $this->retry_delay,
                        'next_attempt' => date('Y-m-d H:i:s', time() + $this->retry_delay)
                    ]);
                    sleep($this->retry_delay);
                }

                $attempt++;
            }
        }

        $total_time = round(microtime(true) - $start_time, 2);
        $this->log("SOAP istek başarısız", [
            'request_id' => $request_id,
            'method' => $method,
            'total_attempts' => $this->max_retries,
            'total_time' => $total_time,
            'last_error' => $last_error ? $last_error->getMessage() : 'Bilinmeyen hata'
        ]);

        throw new Exception(
            "SOAP isteği başarısız ($this->max_retries deneme sonrası): " .
                ($last_error ? $last_error->getMessage() : 'Bilinmeyen hata')
        );
    }

    /**
     * Task'ın tamamlanmasını bekle
     * @param ManagedObject $task Task objesi
     * @param int $timeout Zaman aşımı (saniye)
     * @return bool
     * @throws Exception
     */
    protected function waitForTask($task, $timeout = 300)
    {
        $start = time();
        $this->log("Task bekleniyor", [
            'task_id' => $task->reference->_,
            'start_time' => date('Y-m-d H:i:s'),
            'timeout' => $timeout
        ]);

        while (true) {
            $task_info = $task->info;

            if ($task_info->state === 'success') {
                $this->log("Task başarılı", [
                    'task_id' => $task->reference->_,
                    'duration' => time() - $start
                ]);
                return true;
            }

            if ($task_info->state === 'error') {
                $error = isset($task_info->error->localizedMessage)
                    ? $task_info->error->localizedMessage
                    : 'Bilinmeyen hata';

                $this->log("Task hatası", [
                    'task_id' => $task->reference->_,
                    'error' => $error,
                    'duration' => time() - $start
                ]);

                throw new Exception("Task hatası: $error");
            }

            if (time() - $start > $timeout) {
                $this->log("Task zaman aşımı", [
                    'task_id' => $task->reference->_,
                    'timeout' => $timeout,
                    'duration' => time() - $start
                ]);

                throw new Exception("Task zaman aşımı ($timeout saniye)");
            }

            sleep(1);
        }
    }
}
