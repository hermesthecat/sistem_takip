#!/bin/bash
# Author/Yazar: A. Kerem Gök
# Description: Lists all VMs from Proxmox server in JSON format and sends to PHP script
# Açıklama: Proxmox sunucusundaki tüm VM'leri JSON formatında listeler ve PHP script'e gönderir

# Check if physical machine ID is provided
# Fiziksel makine ID'sinin verilip verilmediğini kontrol et
if [ -z "$1" ]; then
    echo "Error: Physical machine ID is required"
    echo "Usage: $0 <physical_machine_id>"
    echo "Example: $0 PVE01"
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
PHP_URL="http://noreplay.email/proxmox-to-php.php"

# Create temporary JSON file
# Geçici JSON dosyası oluştur
json_output=$(mktemp)

# JSON start with physical machine ID
# Fiziksel makine ID'si ile JSON başlangıcı
echo "{" > "$json_output"
echo "  \"physical_machine_id\": \"$PHYSICAL_MACHINE_ID\"," >> "$json_output"
echo "  \"post_token\": \"$POST_TOKEN\"," >> "$json_output"
echo "  \"virtual_machines\": [" >> "$json_output"

# Get all VMs (QEMU/KVM only)
# Tüm VM'leri al (sadece QEMU/KVM makineleri)
vms=$(pvesh get /cluster/resources --type vm)

# Process VM list
# VM listesini işle
first=true
echo "$vms" | jq -c '.[]' | while read -r vm; do
    # Parse basic VM information
    # Temel VM bilgilerini parse et
    vmid=$(echo "$vm" | jq -r '.vmid')
    name=$(echo "$vm" | jq -r '.name')
    status=$(echo "$vm" | jq -r '.status')
    maxmem=$(echo "$vm" | jq -r '.maxmem')
    maxcpu=$(echo "$vm" | jq -r '.maxcpu')
    
    # Get detailed VM information
    # VM'in detaylı bilgilerini al
    config=$(qm config "$vmid")
    
    # Get operating system type
    # İşletim sistemi tipini al
    guest_os=$(echo "$config" | grep "^ostype:" | cut -d' ' -f2)
    
    # Calculate total disk size
    # Toplam disk boyutunu hesapla
    total_disk_size_gb=0
    while read -r disk; do
        if [[ $disk =~ ^(virtio|scsi|ide|sata)[0-9]+:.*size=([0-9]+[GMT]).*$ ]]; then
            size=${BASH_REMATCH[2]}
            # Convert size to GB
            # Boyutu GB'a çevir
            if [[ $size =~ ([0-9]+)T$ ]]; then
                size_gb=$((${BASH_REMATCH[1]} * 1024))
            elif [[ $size =~ ([0-9]+)G$ ]]; then
                size_gb=${BASH_REMATCH[1]}
            elif [[ $size =~ ([0-9]+)M$ ]]; then
                size_gb=$((${BASH_REMATCH[1]} / 1024))
            fi
            total_disk_size_gb=$((total_disk_size_gb + size_gb))
        fi
    done <<< "$(echo "$config" | grep -E '^(virtio|scsi|ide|sata)[0-9]+:')"
    
    # Get IP addresses (if VM is running)
    # IP adreslerini al (eğer makine çalışıyorsa)
    ip_addresses=""
    if [ "$status" = "running" ]; then
        # Get IP information through QEMU agent
        # QEMU agent üzerinden IP bilgilerini al
        agent_info=$(qm agent "$vmid" network-get-interfaces 2>/dev/null)
        if [ $? -eq 0 ]; then
            ip_addresses=$(echo "$agent_info" | jq -r '.[] | select(.["ip-addresses"]) | .["ip-addresses"][].ip-address' | grep -v '^fe80::' | grep -v '^127\.' | tr '\n' ',' | sed 's/,$//')
        fi
    fi
    
    # Convert memory to GB
    # Belleği GB'a çevir
    memory_gb=$(echo "scale=2; $maxmem / 1024 / 1024 / 1024" | bc)
    
    # Output in JSON format
    # JSON formatında çıktı ver
    if [ "$first" = true ]; then
        first=false
    else
        echo "    ," >> "$json_output"
    fi
    
    echo "    {" >> "$json_output"
    echo "      \"id\": \"$vmid\"," >> "$json_output"
    echo "      \"name\": \"$name\"," >> "$json_output"
    echo "      \"guest_os\": \"$guest_os\"," >> "$json_output"
    echo "      \"power_state\": \"$status\"," >> "$json_output"
    echo "      \"memory_mb\": $(printf "%.0f" "$(echo "$memory_gb * 1024" | bc)")," >> "$json_output"
    echo "      \"num_cpu\": $maxcpu," >> "$json_output"
    echo "      \"total_disk_size_gb\": $total_disk_size_gb," >> "$json_output"
    echo "      \"ip_addresses\": \"$ip_addresses\"" >> "$json_output"
    echo -n "    }" >> "$json_output"
done

# JSON end
# JSON sonlandırma
echo "" >> "$json_output"
echo "  ]" >> "$json_output"
echo "}" >> "$json_output"

# Send JSON data to PHP script
# JSON verisini PHP script'e gönder
curl -X POST -H "Content-Type: application/json" --data-binary "@$json_output" "$PHP_URL"

# Delete temporary file
# Geçici dosyayı sil
rm "$json_output" 