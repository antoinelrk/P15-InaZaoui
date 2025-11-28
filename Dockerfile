FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    curl \
    openssl \
    libssl-dev \
    libpq-dev \
  && docker-php-ext-install -j"$(nproc)" \
       intl \
       pdo \
       pdo_pgsql \
       pdo_mysql \
       opcache \
       zip \
       mbstring \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony

COPY . .

RUN composer install --no-interaction --optimize-autoloader --prefer-dist \
  && php bin/console cache:clear --env=prod \
  && php bin/console cache:warmup --env=prod

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]