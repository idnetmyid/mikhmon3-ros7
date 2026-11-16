FROM php:8.4.27-apache-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    unzip \
    && docker-php-ext-install -j"$(nproc)" \
    mysqli \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Store application source outside Apache document root
WORKDIR /opt/mikhmon
COPY app/ /opt/mikhmon/

# Install container entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
