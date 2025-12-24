#!/bin/bash

# Generate SSH key for root if it doesn't exist
if [ ! -f /root/.ssh/id_rsa ]; then
    mkdir -p /root/.ssh
    ssh-keygen -t rsa -b 2048 -f /root/.ssh/id_rsa -N ""
    cat /root/.ssh/id_rsa.pub >> /root/.ssh/authorized_keys
    chmod 600 /root/.ssh/authorized_keys
    chmod 700 /root/.ssh
    
    # Copy private key to web directory (vulnerability!)
    cp /root/.ssh/id_rsa /var/www/html/.ssh_key
    chmod 644 /var/www/html/.ssh_key
    
    echo "FLAG{ssh_key_exposed_in_webapp}" > /root/flag.txt
fi
