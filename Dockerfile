FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql mbstring zip xml \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

ENV PORT=8080
EXPOSE 8080

# Must bind to Railway's injected $PORT, otherwise the proxy returns 502.
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /app /app/router.php"]
