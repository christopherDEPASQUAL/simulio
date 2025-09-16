FROM php:8.2-cli-alpine

# Paquets système
RUN apk add --no-cache \
    tzdata bash git unzip icu-dev libzip-dev

# Timezone
RUN cp /usr/share/zoneinfo/Europe/Paris /etc/localtime \
 && echo "Europe/Paris" > /etc/timezone

# Extensions PHP
RUN docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
EXPOSE 8000
