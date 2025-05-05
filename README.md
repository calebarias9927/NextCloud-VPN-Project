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
