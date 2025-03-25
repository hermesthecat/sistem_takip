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

class VCenterSOAP
{

    /** @var Service */
    protected $service;

    /** @var array */
    protected $config;

    /** @var int */
    protected $max_retries = 3;

    /** @var int */
    protected $retry_delay = 2;

    /** @var string */
    protected $request_id;

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
            return self::$vm_cache[$cache_key]['data'];
        }

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

                $result = $this->service->$method($params);

                return $result;
            } catch (Exception $e) {
                $last_error = $e;

                if ($attempt < $this->max_retries) {

                    sleep($this->retry_delay);
                }

                $attempt++;
            }
        }

        $total_time = round(microtime(true) - $start_time, 2);

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

        while (true) {
            $task_info = $task->info;

            if ($task_info->state === 'success') {

                return true;
            }

            if ($task_info->state === 'error') {
                $error = isset($task_info->error->localizedMessage)
                    ? $task_info->error->localizedMessage
                    : 'Bilinmeyen hata';

                throw new Exception("Task hatası: $error");
            }

            if (time() - $start > $timeout) {

                throw new Exception("Task zaman aşımı ($timeout saniye)");
            }

            sleep(1);
        }
    }
}
