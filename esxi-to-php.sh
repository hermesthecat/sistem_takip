#!/bin/sh
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

# Create temporary JSON file
# bulunduğunuz ortamınıza göre geçici JSON dosyası oluştur
rm /esxi-to-php.json
json_output="/esxi-to-php.json"

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
    file=$(echo "$line" | awk '{print $3}')
    guest_os=$(echo "$line" | awk '{print $4}')
    version=$(echo "$line" | awk '{print $5}')
    
    # Get VM power state
    # VM'in güç durumunu al
    power_state=$(vim-cmd vmsvc/power.getstate "$vmid" | grep -i "Powered" | awk '{print $2}')
    
    # Get VM memory and CPU information
    # VM'in bellek ve CPU bilgilerini al
    config_info=$(vim-cmd vmsvc/get.config "$vmid")
    memory_mb=$(echo "$config_info" | grep "memoryMB" | awk '{print $3}' | sed 's/[",]//g')
    num_cpu=$(echo "$config_info" | grep "numCPUs" | awk '{print $3}' | sed 's/[",]//g')
    
    # Calculate total disk size
    # Toplam disk boyutunu hesapla
    total_disk_size_gb=0
    echo "$config_info" | while IFS= read -r disk_line; do
        if echo "$disk_line" | grep -q "diskPath"; then
            disk_path=$(echo "$disk_line" | sed 's/.*"\(.*\)".*/\1/')
            disk_size=$(echo "$config_info" | grep -A 2 "$disk_path" | grep "capacityInKB" | awk '{print $3}' | sed 's/[",]//g')
            disk_size_gb=$((disk_size / 1024 / 1024))
            total_disk_size_gb=$((total_disk_size_gb + disk_size_gb))
        fi
    done
    
    # Get IP addresses
    # IP adreslerini al
    guest_info=$(vim-cmd vmsvc/get.guest "$vmid")
    ip_addresses=$(echo "$guest_info" | grep "ipAddress" | awk -F'"' '{print $2}' | sort -u | sed ':a;N;$!ba;s/\n/,/g' | sed 's/,$//')
    
    # Output in JSON format
    # JSON formatında çıktı ver
    echo "    {" >> "$json_output"
    echo "      \"id\": \"$vmid\"," >> "$json_output"
    echo "      \"name\": \"$name\"," >> "$json_output"
    echo "      \"datastore_path\": \"$file\"," >> "$json_output"
    echo "      \"guest_os\": \"$guest_os\"," >> "$json_output"
    echo "      \"version\": \"$version\"," >> "$json_output"
    echo "      \"power_state\": \"$power_state\"," >> "$json_output"
    echo "      \"memory_mb\": $memory_mb," >> "$json_output"
    echo "      \"num_cpu\": $num_cpu," >> "$json_output"
    echo "      \"total_disk_size_gb\": $total_disk_size_gb," >> "$json_output"
    echo "      \"ip_addresses\": \"$ip_addresses\"" >> "$json_output"
    
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
wget -O- --post-file="$json_output" --header="Content-Type: application/json" "$PHP_URL" >/dev/null 2>&1

# Delete temporary file
# Geçici dosyayı sil
#rm "$json_output" 