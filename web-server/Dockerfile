FROM php:7.4-apache

# Install system dependencies and pentesting tools
RUN apt-get update && apt-get install -y \
    wget \
    curl \
    netcat-traditional \
    nmap \
    iproute2 \
    iputils-ping \
    dnsutils \
    vim \
    nano \
    git \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Chisel for tunneling
RUN wget https://github.com/jpillora/chisel/releases/download/v1.9.1/chisel_1.9.1_linux_amd64.gz \
    && gunzip chisel_1.9.1_linux_amd64.gz \
    && mv chisel_1.9.1_linux_amd64 /usr/local/bin/chisel \
    && chmod +x /usr/local/bin/chisel

# Enable Apache modules
RUN a2enmod rewrite

# Create uploads directory with write permissions
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
