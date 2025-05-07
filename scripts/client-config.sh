#!/bin/bash

# Load variables
if [ -f ".env" ]; then
	source .envelse
	echo "No .env file found!"
	exit 1
fi

# Check parameters
if [ $# -lt 2 ]; then
	echo "Usage: $0 <client-name> <client-ip>"
	exit 1
fi
	
CLIENT_NAME=$1
CLIENT_IP=$2

# Generate client keys
CLIENT_PRIVATE_KEY=$(wg genkey)
CLIENT_PUBLIC_KEY=$(echo $CLIENT_PRIVATE_KEY | wg pubkey)
echo "Client: $CLIENT_NAME"
echo "Private key: $CLIENT_PRIVATE_KEY"
echo "Public key: $CLIENT_PUBLIC_KEY"

# Generate client config
cat client.conf.template |
	sed "s|{{CLIENT_PRIVATE_KEY}}|$CLIENT_PRIVATE_KEY|g" |
	sed "s|{{CLIENT_IP}}|$CLIENT_IP|g" |
	sed "s|{{WG_SERVER_PUBLIC_KEY}}|$WG_SERVER_PUBLIC_KEY|g" |
	sed "s|{{WG_SERVER_PUBLIC_IP}}|$WG_SERVER_PUBLIC_IP|g" |
	sed "s|{{WG_PORT}}|$WG_PORT|g" |
	sed "s|{{WG_SUBNET}}|$WG_SUBNET|g" |
	sed "s|{{NEXTCLOUD_SERVER_IP}}|$NEXTCLOUD_SERVER_IP|g" > $CLIENT_NAME.conf

# Add peer to server configuration
echo "" >> wg0.conf
echo "[Peer] # $CLIENT_NAME" >> wg0.conf
echo "PublicKey = $CLIENT_PUBLIC_KEY" >> wg0.conf
echo "AllowedIPs = $CLIENT_IP/32" >> wg0.conf
echo "Client configuration created: $CLIENT_NAME.conf"
echo "Server configuration updated with new peer"
