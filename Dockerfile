FROM php:7.3-cli

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
      libpq-dev &&\
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) pdo_pgsql

COPY app /app

WORKDIR /app

CMD ["php", "sync-identity-providers.php"]
