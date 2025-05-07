#!/bin/bash

# Load variables
if [ -f ".env" ]; then
	source .env
else
	echo "No .env file found!"
	exit 1
fi

#Generate server keys if they don't exist
if [ -z "$WG_SERVER_PRIVATE_KEY" ]; then
	echo "Generating WireGuard keys..."
	WG_SERVER_PRIVATE_KEY=$(wg genkey)
	WG_SERVER_PUBLIC_KEY=$(echo $WG_SERVER_PRIVATE_KEY | wg pubkey)
	echo "WG_SERVER_PRIVATE_KEY=$WG_SERVER_PRIVATE_KEY" >> .env
	echo "WG_SERVER_PUBLIC_KEY=$WG_SERVER_PUBLIC_KEY" >> .env
	echo "Keys added to .env file. Keep this file secure!"
fi

# Create actual config from template
cat wg0.conf.template |
	sed "s|{{WG_SERVER_IP}}|$WG_SERVER_IP|g" |
	sed "s|{{WG_SERVER_PRIVATE_KEY}}|$WG_SERVER_PRIVATE_KEY|g" |
	sed "s|{{WG_PORT}}|$WG_PORT|g" |
	sed "s|{{SERVER_INTERFACE}}|$SERVER_INTERFACE|g" > wg0.conf

echo "WireGuard server configuration generated: wg0.conf"
