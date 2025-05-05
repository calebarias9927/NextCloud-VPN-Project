#!/bin/bash
# NextCloud Server UFW Setup Script


# Exit script if any command fails
set -e

# IMPORTANT: Change this to your actual WireGuard subnet
WIREGUARD_SUBNET="10.8.0.2/32"


# Reset UFW to default state
ufw --force reset

# Set default policies (deny all incoming, allow all outgoing)
ufw default deny incoming
ufw default allow outgoing

# Block all public access to NextCloud ports
ufw deny 80/tcp
ufw deny 443/tcp

# Allow access to NextCloud only from WireGuard VPN subnet
ufw allow from 10.8.0.2/32 to any port 80 proto tcp comment 'NextCloud HTTP via WireGuard'
ufw allow from 10.8.0.2/32 to any port 443 proto tcp comment 'NextCloud HTTPS via WireGuard'

# Enable UFW
ufw --force enable
