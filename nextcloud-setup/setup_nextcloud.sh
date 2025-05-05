#!/bin/bash
# install_nextcloud.sh

# Exit on error
set -e

echo "[*] Updating system packages..."
sudo apt update && sudo apt upgrade -y

echo "[*] Installing Apache, PHP, and required modules..."
sudo apt install apache2 mariadb-server libapache2-mod-php php-gd php-json php-mysql php-curl php-mbstring php-intl php-imagick php-xml php-zip unzip -y

echo "[*] Creating web directory and downloading Nextcloud..."
cd /var/www/
sudo wget https://download.nextcloud.com/server/releases/latest.zip
sudo unzip latest.zip
sudo chown -R www-data:www-data nextcloud
sudo chmod -R 755 nextcloud

echo "[*] Configuring Apache..."
sudo cp ~/NextCloud-VPN-Project/nextcloud-setup/apache/nextcloud.conf /etc/apache2/sites-available/nextcloud.conf
sudo a2ensite nextcloud.conf
sudo a2enmod rewrite headers env dir mime
sudo systemctl reload apache2

echo "[*] Done. Visit your VM IP in browser to complete setup!"
