FROM php:5.6-apache
RUN apt-get update -y && apt-get install -y \
        libmcrypt-dev \
        mysql-client \
        zip \
        --no-install-recommends \
    && a2enmod rewrite \
    && pecl install xdebug-2.5.5 \
    && docker-php-ext-install -j$(nproc) bcmath calendar mcrypt pdo_mysql mysqli \
    && docker-php-ext-enable mysqli \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

RUN echo "Setting up PHP for development..." \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/max_execution_time = .*/max_execution_time = 300/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/;date.timezone =.*/date.timezone= "Europe\/Berlin"/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = .*/memory_limit = -1/' "$PHP_INI_DIR/php.ini"

COPY --chown=www-data:www-data docker/web/apache.conf /etc/apache2/sites-enabled/meals.conf
COPY --chown=www-data:www-data --from=aoepeople/meals:latest /var/www/html/ /var/www/html/
