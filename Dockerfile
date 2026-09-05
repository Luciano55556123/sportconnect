FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        libcurl4-openssl-dev \
        libpq-dev \
        unzip \
    && docker-php-ext-install curl pdo pdo_pgsql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

RUN printf 'upload_max_filesize=10M\npost_max_size=12M\nmemory_limit=256M\n' > /usr/local/etc/php/conf.d/sportconnect-uploads.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN if grep -q '"name": "bacon/bacon-qr-code"' composer.lock; then \
        composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction; \
    else \
        composer update bacon/bacon-qr-code --with-dependencies --no-dev --prefer-dist --optimize-autoloader --no-interaction; \
    fi

COPY . /var/www/html

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

RUN printf '<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n' > /etc/apache2/conf-available/sportconnect.conf \
    && a2enconf sportconnect

RUN mkdir -p \
        /var/www/html/storage/sessions \
        /var/www/html/public/uploads/campeonatos \
        /var/www/html/uploads \
    && chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/public/uploads \
        /var/www/html/uploads

EXPOSE 80

CMD ["apache2-foreground"]
