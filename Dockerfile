# syntax=docker/dockerfile:1

FROM php:8.2-apache as final

RUN docker-php-ext-install xml php-zip 
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --from=deps vendor/ /var/www/html/vendor
COPY ./ /var/www/html
USER www-data

FROM php:8-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY ./ /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    zip \
    zlib1g-dev \ 
    curl \
    sudo \
    unzip \
    libzip-dev \
    libicu-dev \
    libbz2-dev \
    libpng-dev \
    libjpeg-dev \
    libmcrypt-dev \
    libreadline-dev \
    libfreetype6-dev \
    g++ \
    poppler-utils
        
    
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Add Composer's global bin directory to PATH
ENV PATH="$PATH:/root/.composer/vendor/bin"

RUN composer install --no-dev --optimize-autoloader
# Install PHPUnit globally
RUN composer global require phpunit/phpunit --prefer-dist && chmod +x /root/.composer/vendor/bin/phpunit

RUN composer dump

USER www-data