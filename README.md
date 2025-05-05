# NextCloud + WireGuard VPN Project

A secure, self-hosted cloud storage solution using **NextCloud** and **WireGuard VPN**, deployed on a VirtualBox Ubuntu VM.

## 📦 Project Overview

This project enables a private file-sharing and collaboration platform (NextCloud) that is only accessible via a secure WireGuard VPN tunnel. It ensures that only authenticated users on the VPN can reach the cloud server.

## 🖥️ Project Architecture

- **Ubuntu 22.04 VM** running in **VirtualBox**
- **Apache2 + PHP + MariaDB** backend for NextCloud
- **WireGuard VPN** server for secure remote access
- **Bridged Network Adapter** for LAN access
- **GitHub** for source control & configuration backups

## 📦 Project Structure
NextCloud-VPN-Project/
├── nextcloud-setup/ # NextCloud installation + web root files
│ ├── apache/ # Apache config files
│ └── nextcloud-www/ # Cloned NextCloud source
├── wireguard-config/ # VPN configuration (wg0.conf, keys)
├── firewall-rules/ # IPTables or UFW rules
├── docs/ # Documentation or diagrams
├── install_nextcloud.sh # Setup script to automate installation
├── .gitignore
└── README.md

## ✅ What We've Completed

- [x] Installed and configured Apache, PHP, and MariaDB
- [x] Deployed NextCloud to `/var/www/nextcloud`
- [x] Created NextCloud admin user and team accounts (e.g., Elijah, Miguel)
- [x] Set up WireGuard with private/public key pair
- [x] Configured VPN interface in `/etc/wireguard/wg0.conf`
- [x] Enabled IP forwarding for routing
- [x] Verified external access to NextCloud via browser

## 🔒 VPN Configuration

Example `/etc/wireguard/wg0.conf`:

```ini
[Interface]
PrivateKey = <SERVER_PRIVATE_KEY>
Address = 10.8.0.1/24
ListenPort = 51820
SaveConfig = true

PostUp = sysctl -w net.ipv4.ip_forward=1
PostDown = sysctl -w net.ipv4.ip_forward=0


---

## 🔐 VPN Setup

We use [WireGuard](https://www.wireguard.com/) to create a private, encrypted network between clients and the NextCloud server.

- Server listens on port `51820`.
- Clients are assigned IPs in the range `10.8.0.0/24`.
- Each team member generates a public/private key pair and shares their **public key** to be added as a `[Peer]` in `wg0.conf`.

---

## ☁️ NextCloud Setup

- Apache2 is configured to serve `nextcloud-www`.
- NextCloud is only accessible within the VPN subnet.
- HTTPS is enabled with a self-signed cert (or optionally Let's Encrypt).
- All NextCloud data is excluded from version control via `.gitignore`.

To install NextCloud on a new VM:

```bash
chmod +x install_nextcloud.sh
./install_nextcloud.sh
