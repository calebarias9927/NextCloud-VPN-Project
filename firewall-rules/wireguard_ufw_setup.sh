#!/bin/bash
# WireGuard Server UFW Setup Script

# Exit script if any command fails
set -e


# Reset UFW to default state
ufw --force reset

# Set default policies (deny all incoming, allow all outgoing)
ufw default deny incoming
ufw default allow outgoing

# Allow WireGuard port (UDP 51820)
ufw allow 51820/udp comment 'WireGuard VPN'

# Enable UFW
ufw --force enable
