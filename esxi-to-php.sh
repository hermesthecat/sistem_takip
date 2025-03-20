#!/bin/bash
# Author/Yazar: A. Kerem Gök
# Description: Lists all VMs from ESXi server in JSON format and sends to PHP script
# Açıklama: ESXi sunucusundaki tüm VM'leri JSON formatında listeler ve PHP script'e gönderir

# Check if physical machine ID is provided
# Fiziksel makine ID'sinin verilip verilmediğini kontrol et
if [ -z "$1" ]; then
    echo "Error: Physical machine ID is required"
    echo "Usage: $0 <physical_machine_id>"
    echo "Example: $0 ESX01"
    exit 1
fi

# Check if post token is provided
# POST token'ın verilip verilmediğini kontrol et
if [ -z "$2" ]; then
    echo "Error: Post token is required"
    echo "Usage: $0 <physical_machine_id> <post_token>"
    exit 1
fi

# Store physical machine ID
# Fiziksel makine ID'sini sakla
PHYSICAL_MACHINE_ID="$1"

# POST token
# POST token'ı
# TODO: This should be changed to a random string and match with the token in fiziksel_sunucular table
POST_TOKEN="$2"

# PHP script URL (update this address according to your environment)
# PHP script URL'si (bu adresi kendi ortamınıza göre güncelleyin)
PHP_URL="http://noreplay.email/import_esxi.php"

# Delete temporary files
# Geçici dosyaları sil
json_output="/esxi-to-php.json"
rm -f "$json_output"

# JSON start with physical machine ID
# Fiziksel makine ID'si ile JSON başlangıcı
echo "{" > "$json_output"
echo "  \"physical_machine_id\": \"$PHYSICAL_MACHINE_ID\"," >> "$json_output"
echo "  \"post_token\": \"$POST_TOKEN\"," >> "$json_output"
echo "  \"virtual_machines\": [" >> "$json_output"

# Get all VMs
# Tüm VM'leri al
vms=$(vim-cmd vmsvc/getallvms)

# Skip first line (header)
# İlk satırı atla (başlık satırı)
echo "$vms" | tail -n +2 | while read -r line
do
    # Parse VM information
    # VM bilgilerini parse et
    vmid=$(echo "$line" | awk '{print $1}')
    name=$(echo "$line" | awk '{print $2}')

    # Get VM power state
    # VM'in güç durumunu al
    power_state=$(vim-cmd vmsvc/power.getstate "$vmid" | grep -i "Powered" | awk '{print $2}')

    # Get VM memory and CPU information
    # VM'in bellek ve CPU bilgilerini al
    config_info=$(vim-cmd vmsvc/get.config "$vmid")
    memory_mb=$(echo "$config_info" | grep "memoryMB" | awk '{print $3}' | sed 's/[",]//g')

    # CPU bilgilerini daha kapsamlı almak için alternatif yöntem deneyelim
    # Metod 1: Standart yöntem
    num_sockets=$(echo "$config_info" | grep "numCPUs" | awk '{print $3}' | sed 's/[",]//g')
    cores_per_socket=$(echo "$config_info" | grep "numCoresPerSocket" | awk '{print $3}' | sed 's/[",]//g')

    # Metod 2: Device getdevices ile
    device_info=$(vim-cmd vmsvc/device.getdevices "$vmid")
    cpu_info=$(echo "$device_info" | grep -A 20 "VirtualCPU")
    alt_num_cpu=$(echo "$cpu_info" | grep -A 1 "coresPerSocket" | grep -v "coresPerSocket" | awk '{print $1}' | sed 's/,//g')

    # Metod 3: VM özet bilgileri
    summary_info=$(vim-cmd vmsvc/get.summary "$vmid")
    summary_num_cpu=$(echo "$summary_info" | grep -A 1 "numCpu" | awk 'NR==1{print $3}' | sed 's/,//g')

    # En iyi sonucu seçelim
    if [ ! -z "$summary_num_cpu" ] && [ "$summary_num_cpu" -gt 0 ]; then
        num_cpu=$summary_num_cpu
    else
        # Değerler boş olabilir, varsayılan değerler ata
        if [ -z "$num_sockets" ]; then
            num_sockets=1
        fi

        if [ -z "$cores_per_socket" ]; then
            cores_per_socket=1
        fi

        # Toplam çekirdek sayısını hesapla
        num_cpu=$((num_sockets * cores_per_socket))

        # Alt yöntemi kontrol et
        if [ "$num_cpu" -lt "$alt_num_cpu" ] && [ ! -z "$alt_num_cpu" ] && [ "$alt_num_cpu" -gt 0 ]; then
            num_cpu=$alt_num_cpu
        fi
    fi

    # Calculate total disk size
    # Toplam disk boyutunu hesapla
    total_disk_size_gb=0

    # Daha basit bir disk boyutu hesaplama yöntemi
    disk_sizes=$(vim-cmd vmsvc/device.getdevices "$vmid" | grep -A 3 "VirtualDisk" | grep capacityInKB | awk '{print $3}' | sed 's/,//g')

    for disk_size in $disk_sizes
    do
        disk_size_gb=$((disk_size / 1024 / 1024))
        total_disk_size_gb=$((total_disk_size_gb + disk_size_gb))
    done

    # If no disk size found, try fallback method
    if [ "$total_disk_size_gb" = "0" ]; then
        disk_kb=$(vim-cmd vmsvc/get.summary "$vmid" | grep -A 1 "storage" | grep committed | awk '{print $3}' | sed 's/,//g')
        if [ ! -z "$disk_kb" ]; then
            total_disk_size_gb=$((disk_kb / 1024 / 1024))
        fi
    fi

    echo "name: $name"
    echo "vmid: $vmid"
    echo "power_state: $power_state"
    echo "memory_mb: $memory_mb"
    echo "num_cpu: $num_cpu"
    echo "total_disk_size_gb: $total_disk_size_gb"
    echo "--------------------------------"

    # Output in JSON format
    # JSON formatında çıktı ver
    echo "    {" >> "$json_output"
    echo "      \"id\": \"$vmid\"," >> "$json_output"
    echo "      \"name\": \"$name\"," >> "$json_output"
    echo "      \"power_state\": \"$power_state\"," >> "$json_output"
    echo "      \"memory_mb\": $memory_mb," >> "$json_output"
    echo "      \"num_cpu\": $num_cpu," >> "$json_output"
    echo "      \"total_disk_size_gb\": $total_disk_size_gb" >> "$json_output"

    # Add comma if not last VM
    # Son VM değilse virgül ekle
    if [ "$(echo "$vms" | tail -n +2 | tail -n1)" != "$line" ]; then
        echo "    }," >> "$json_output"
    else
        echo "    }" >> "$json_output"
    fi
done

# JSON end
# JSON sonlandırma
echo "  ]" >> "$json_output"
echo "}" >> "$json_output"

# Send JSON data to PHP script
# JSON verisini PHP script'e gönder

# URL'den host ve yolu ayır
HOST="noreplay.email"
PATH_URL="/import_esxi.php"

# JSON dosyasının boyutunu al
CONTENT_LENGTH=$(wc -c < "$json_output")

# HTTP isteğini netcat ile gönder
(
    echo "POST $PATH_URL HTTP/1.1"
    echo "Host: $HOST"
    echo "Content-Type: application/json"
    echo "Content-Length: $CONTENT_LENGTH"
    echo ""
    cat "$json_output"
) | nc $HOST 80 > /dev/null 2>&1