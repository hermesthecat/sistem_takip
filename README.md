# Sanallaştırma Sunucusu VM Listesi Projesi

**Yazar:** A. Kerem Gök

## Proje Hakkında

Bu proje, ESXi ve Proxmox sanallaştırma sunucularındaki sanal makinelerin (VM) bilgilerini toplayıp web arayüzünde görüntülemeyi sağlar. Sunucudaki VM'lerin durumları, özellikleri ve IP adresleri gibi önemli bilgileri JSON formatında alıp, kullanıcı dostu bir tablo halinde gösterir.

## Dosyalar

### 1. esxi-to-php.sh
ESXi sunucusundan VM bilgilerini toplayan bash script'i.
- Fiziksel makine ID'si (parametre olarak)
- VM ID ve isim bilgileri
- Güç durumu (açık/kapalı)
- İşletim sistemi tipi
- Donanım versiyonu
- CPU ve RAM bilgileri
- Toplam disk boyutu (GB)
- IP adresleri

Kullanım:
```bash
./esxi-to-php.sh <physical_machine_id>
# Örnek:
./esxi-to-php.sh ESX01
```

Örnek JSON çıktısı:
```json
{
  "physical_machine_id": "ESX01",
  "virtual_machines": [
    {
      "id": "1",
      "name": "Web-Server",
      "datastore_path": "[datastore1] Web-Server/Web-Server.vmx",
      "guest_os": "ubuntu64Guest",
      "version": "vmx-19",
      "power_state": "on",
      "memory_mb": 4096,
      "num_cpu": 2,
      "total_disk_size_gb": 150,
      "ip_addresses": "192.168.1.100,10.0.0.100"
    },
    {
      "id": "2",
      "name": "Database-Server",
      "datastore_path": "[datastore1] Database-Server/Database-Server.vmx",
      "guest_os": "centos7_64Guest",
      "version": "vmx-19",
      "power_state": "off",
      "memory_mb": 8192,
      "num_cpu": 4,
      "total_disk_size_gb": 500,
      "ip_addresses": ""
    }
  ]
}
```

### 2. proxmox-to-php.sh
Proxmox sunucusundan VM bilgilerini toplayan bash script'i.
- Fiziksel makine ID'si (parametre olarak)
- VM ID ve isim bilgileri
- Güç durumu (running/stopped)
- İşletim sistemi tipi
- CPU ve RAM bilgileri
- Toplam disk boyutu (GB)
- IP adresleri (QEMU agent üzerinden)

Kullanım:
```bash
./proxmox-to-php.sh <physical_machine_id>
# Örnek:
./proxmox-to-php.sh PVE01
```

Örnek JSON çıktısı:
```json
{
  "physical_machine_id": "PVE01",
  "virtual_machines": [
    {
      "id": "100",
      "name": "web-prod",
      "guest_os": "l26",
      "power_state": "running",
      "memory_mb": 4096,
      "num_cpu": 2,
      "total_disk_size_gb": 120,
      "ip_addresses": "192.168.1.50,10.0.0.50"
    },
    {
      "id": "101",
      "name": "db-prod",
      "guest_os": "win10",
      "power_state": "stopped",
      "memory_mb": 16384,
      "num_cpu": 4,
      "total_disk_size_gb": 750,
      "ip_addresses": ""
    }
  ]
}
```

### 3. show_vms.php
VM bilgilerini web arayüzünde gösteren PHP script'i.
- Responsive tasarım
- Renkli durum göstergeleri
- Kolay okunabilir tablo formatı
- Otomatik güncelleme zamanı

## Gereksinimler

### ESXi Sunucusu İçin
- SSH erişimi
- Root yetkisi
- `vim-cmd` komut erişimi
- `curl` paketi

### Proxmox Sunucusu İçin
- SSH erişimi
- Root yetkisi
- Aşağıdaki paketler:
  ```bash
  apt-get install jq curl bc
  ```
- QEMU guest agent (IP bilgileri için)

### Web Sunucusu İçin
- PHP 7.0 veya üzeri
- Apache2/Nginx
- POST isteklerine izin verilmesi

## Kurulum

1. Web sunucunuza `show_vms.php` dosyasını yükleyin.

2. Bash script'lerini sunucularınıza yükleyin:
   ```bash
   # ESXi için
   chmod +x esxi-to-php.sh
   
   # Proxmox için
   chmod +x proxmox-to-php.sh
   ```

3. Script'lerdeki `PHP_URL` değişkenini kendi web sunucunuzun adresiyle güncelleyin:
   ```bash
   PHP_URL="http://your-web-server/show_vms.php"
   ```

## Kullanım

### ESXi Sunucusunda
```bash
./esxi-to-php.sh <physical_machine_id>
# Örnek:
./esxi-to-php.sh ESX01
```

### Proxmox Sunucusunda
```bash
./proxmox-to-php.sh <physical_machine_id>
# Örnek:
./proxmox-to-php.sh PVE01
```

## Özellikler

- Otomatik veri toplama
- JSON formatında veri aktarımı
- Responsive web arayüzü
- Renkli durum göstergeleri
- Kolay okunabilir tablo formatı
- Çoklu IP adresi desteği
- Otomatik boyut dönüşümleri (MB/GB/TB)

## Güvenlik Notları

1. Script'leri root yetkisiyle çalıştırın
2. Web sunucunuzda güvenlik önlemlerini alın
3. Hassas bilgileri gizleyin
4. Güvenli SSL bağlantısı kullanın

## Hata Giderme

1. IP adresleri görünmüyorsa:
   - VM'nin açık olduğundan emin olun
   - VMware Tools/QEMU agent'ın yüklü olduğunu kontrol edin
   - Ağ bağlantısını kontrol edin

2. Script çalışmıyorsa:
   - Yetkileri kontrol edin
   - Gerekli paketlerin yüklü olduğunu doğrulayın
   - Log dosyalarını kontrol edin

## Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun
3. Değişikliklerinizi commit edin
4. Branch'inizi push edin
5. Pull request oluşturun

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. 