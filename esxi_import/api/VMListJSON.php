<?php

/**
 * vCenter VM Listesi JSON Çıktısı
 * @author A. Kerem Gök
 * @package VMware vCenter Web Interface
 * @version 1.0
 */

require_once __DIR__ . '/VCenterSOAP.php';
require_once __DIR__ . '/../vendor/vmwarephp/library/Vmwarephp/TypeDefinitions.inc';

class VMListJSON extends VCenterSOAP
{

    public function __construct()
    {
        parent::__construct();
        $this->log_file = dirname(__DIR__) . '/logs/vcenter-vmlist-json.log';
    }

    /**
     * VM listesini JSON formatında al
     * @return string JSON çıktısı
     * @throws Exception
     */
    public function getJSON()
    {
        $this->log("VM listesi JSON formatında alınıyor");

        try {
            $vmList = $this->service->findAllManagedObjects('VirtualMachine', [
                'name',
                'runtime.powerState',
                'config.hardware.numCPU',
                'config.hardware.memoryMB',
                'config.hardware.device'
            ]);

            $vms = [];
            foreach ($vmList as $vm) {
                // Disk boyutunu hesapla
                $totalDiskSize = 0;
                if (isset($vm->config->hardware->device)) {
                    foreach ($vm->config->hardware->device as $device) {
                        if (isset($device->capacityInKB)) {
                            $totalDiskSize += $device->capacityInKB / (1024 * 1024); // KB to GB
                        }
                    }
                }

                // Power state'i düzenle
                $powerState = strtolower($vm->runtime->powerState);
                if ($powerState === 'poweredon') $powerState = 'on';
                elseif ($powerState === 'poweredoff') $powerState = 'off';
                elseif ($powerState === 'suspended') $powerState = 'suspended';

                $vms[] = [
                    'id' => $vm->reference->_,
                    'name' => $vm->name,
                    'power_state' => $powerState,
                    'memory_mb' => (int)$vm->config->hardware->memoryMB,
                    'num_cpu' => (int)$vm->config->hardware->numCPU,
                    'total_disk_size_gb' => (int)round($totalDiskSize)
                ];
            }

            // ID'ye göre sırala
            usort($vms, function ($a, $b) {
                return (int)$a['id'] - (int)$b['id'];
            });

            $result = [
                'physical_machine_id' => $this->config['physical_machine_id'],
                'post_token' => $this->config['post_token'],
                'virtual_machines' => $vms
            ];

            $this->log("VM listesi JSON formatında alındı", [
                'count' => count($vms)
            ]);

            return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            $this->log("VM listesi JSON formatında alınamadı", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
