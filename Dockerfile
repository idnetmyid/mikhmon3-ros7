
FROM php:8.4.27-apache-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    unzip \
    && docker-php-ext-install -j"$(nproc)" \
    mysqli \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Apache document root sekaligus lokasi source Mikhmon
WORKDIR /var/www/html

# Copy source aplikasi langsung ke Apache document root
COPY app/ /var/www/html/

# Install container entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
