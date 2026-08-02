FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/sportconnect.conf \
    && a2enconf sportconnect

COPY . /var/www/html

RUN mkdir -p /var/www/html/storage/sessions \
    /var/www/html/public/uploads/campeonatos \
    && chown -R www-data:www-data /var/www/html/storage \
    /var/www/html/public/uploads

EXPOSE 80

CMD ["apache2-foreground"]