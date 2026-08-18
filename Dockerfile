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

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "."]