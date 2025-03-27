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
POST_TOKEN="$2"

# PHP script URL (update this address according to your environment)
# PHP script URL'si (bu adresi kendi ortamınıza göre güncelleyin)
PHP_URL="http://noreplay.email/import_proxmox.php"

# Create temporary JSON file
# Geçici JSON dosyası oluştur
json_output=$(mktemp)

# JSON start with physical machine ID
# Fiziksel makine ID'si ile JSON başlangıcı
echo "{" >"$json_output"
echo "  \"physical_machine_id\": \"$PHYSICAL_MACHINE_ID\"," >>"$json_output"
echo "  \"post_token\": \"$POST_TOKEN\"," >>"$json_output"
echo "  \"virtual_machines\": [" >>"$json_output"

# Get all VM IDs using qm list
# Tüm VM ID'lerini qm list komutu ile al
vm_ids=$(qm list | grep -v VMID | awk '{print $1}')

# Process VM list
# VM listesini işle
first=true
for vmid in $vm_ids; do
    # Get VM status
    status=$(qm status $vmid | awk '{print $2}')

    # Get VM config
    config=$(qm config $vmid 2>/dev/null)
    if [ $? -ne 0 ]; then
        echo "Warning: Could not get config for VM $vmid, skipping"
        continue
    fi

    # Get VM name
    name=$(echo "$config" | grep "^name:" | cut -d' ' -f2-)
    if [ -z "$name" ]; then
        name="VM-$vmid"
    fi

    # Get CPU cores
    maxcpu=$(echo "$config" | grep "^cores:" | cut -d' ' -f2)
    if [ -z "$maxcpu" ] || [ "$maxcpu" = "0" ]; then
        maxcpu=1
    fi

    # Get memory
    memory_mb=$(echo "$config" | grep "^memory:" | cut -d' ' -f2)
    if [ -z "$memory_mb" ] || [ "$memory_mb" = "0" ]; then
        memory_mb=1024
    fi

    # Get OS type
    guest_os=$(echo "$config" | grep "^ostype:" | cut -d' ' -f2)
    if [ -z "$guest_os" ]; then
        guest_os="other"
    fi

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
                size_gb=$(echo "scale=2; ${BASH_REMATCH[1]} / 1024" | bc | awk '{printf "%.0f", $1}')
            else
                size_gb=0
            fi
            total_disk_size_gb=$((total_disk_size_gb + size_gb))
        fi
    done <<<"$(echo "$config" | grep -E '^(virtio|scsi|ide|sata)[0-9]+:')"

    # If total_disk_size_gb is 0, set a default value
    # total_disk_size_gb 0 ise varsayılan bir değer ata
    if [ "$total_disk_size_gb" -eq 0 ]; then
        total_disk_size_gb=1
    fi

    # Get IP addresses (if VM is running)
    # IP adreslerini al (eğer makine çalışıyorsa)
    ip_addresses=""
    if [ "$status" = "running" ]; then
        # Get IP information through QEMU agent
        # QEMU agent üzerinden IP bilgilerini al
        agent_info=$(qm agent "$vmid" network-get-interfaces 2>/dev/null)
        if [ $? -eq 0 ]; then
            ip_addresses=$(echo "$agent_info" | grep -Po '"ip-address": "\K[^"]+' | grep -v "^fe80::" | grep -v "^127\." | tr '\n' ',' | sed 's/,$//')
        fi
    fi

    # Output in JSON format
    # JSON formatında çıktı ver
    if [ "$first" = true ]; then
        first=false
    else
        echo "    ," >>"$json_output"
    fi

    # Escape special characters in the name
    # İsimde özel karakterleri kaçır
    name=$(echo "$name" | sed 's/"/\\"/g')

    echo "    {" >>"$json_output"
    echo "      \"id\": \"$vmid\"," >>"$json_output"
    echo "      \"name\": \"$name\"," >>"$json_output"
    echo "      \"guest_os\": \"$guest_os\"," >>"$json_output"
    echo "      \"power_state\": \"$status\"," >>"$json_output"
    echo "      \"memory_mb\": $memory_mb," >>"$json_output"
    echo "      \"num_cpu\": $maxcpu," >>"$json_output"
    echo "      \"total_disk_size_gb\": $total_disk_size_gb," >>"$json_output"
    echo "      \"ip_addresses\": \"$ip_addresses\"" >>"$json_output"
    echo "    }" >>"$json_output"
done

# JSON end
# JSON sonlandırma
echo "" >>"$json_output"
echo "  ]" >>"$json_output"
echo "}" >>"$json_output"

# Validate final JSON
# Son JSON'ı doğrula
if ! jq . "$json_output" >/dev/null 2>&1; then
    echo "Error: Generated invalid JSON:"
    cat "$json_output"
    rm "$json_output"
    exit 1
fi

# Display JSON for debugging
# JSON'ı hata ayıklama için göster
echo "Generated JSON:"
cat "$json_output"

# Send JSON data to PHP script
# JSON verisini PHP script'e gönder
echo -e "\n=== Sending data to PHP server ===\n"
echo "Target URL: $PHP_URL"
echo -e "Sending request...\n"

response=$(curl -X POST -H "Content-Type: application/json" --data-binary "@$json_output" "$PHP_URL")

# Format the response
echo "=== Server Response ==="
echo "$response" | tr -d '\r' | sed 's/\([^-]\)- /\1\n- /g' | while read -r line; do
    if [[ $line == *"başarıyla eklendi"* ]]; then
        echo "✓ ${line%% - *}: Başarıyla eklendi"
    else
        echo "$line"
    fi
done
echo -e "\n=== Process Complete ===\n"

# Delete temporary file
# Geçici dosyayı sil
rm "$json_output"

# Disable debug mode
# Hata ayıklama modunu devre dışı bırak
set +x
