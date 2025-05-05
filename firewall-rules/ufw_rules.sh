#!/bin/bash
# Basic UFW Rules Placeholder

sudo ufw allow 51820/udp
sudo ufw allow from 192.168.1.0/24 to any port 443
sudo ufw reload
sudo ufw enable
