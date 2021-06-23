# syntax=docker/dockerfile:1.2

# get php dependencies in separate stage
FROM php:5.6-apache as composer
RUN apt-get update -y && apt-get install -y git zip unzip \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = .*/memory_limit = -1/' "$PHP_INI_DIR/php.ini" \
    && curl -sS https://getcomposer.org/installer | php -- --1 --install-dir=/usr/local/bin --filename=composer
COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --optimize-autoloader --prefer-dist

# build frontend assets
FROM node:14 as frontend
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y -o Dpkg::Options::="--force-confold" --no-install-recommends --no-install-suggests \
        build-essential \
        nodejs
WORKDIR var/www/html/app/Resources
COPY app/Resources/package.json app/Resources/bower.json app/Resources/yarn.lock ./
RUN yarn install
COPY app/Resources/ .
COPY web .
RUN NODE_ENV=production yarn run build

# build production container
FROM php:5.6-apache
RUN apt-get update -y && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libmcrypt-dev \
        mysql-client \
        zip \
        --no-install-recommends \
    && a2enmod rewrite \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mcrypt pdo_mysql mysqli \
    && docker-php-ext-enable mysqli \
    && rm -rf /var/lib/apt/lists/* \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/max_execution_time = .*/max_execution_time = 300/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/;date.timezone =.*/date.timezone= "Europe\/Berlin"/' "$PHP_INI_DIR/php.ini"

COPY --chown=www-data:www-data docker/web/apache.conf /etc/apache2/sites-enabled/meals.conf
COPY --chown=www-data:www-data --from=composer /var/www/html/vendor/ ./vendor/
COPY --chown=www-data:www-data --from=composer /var/www/html/bin/ ./bin/
COPY --chown=www-data:www-data --from=frontend /var/www/html/web/ ./web/
COPY --chown=www-data:www-data . /var/www/html/

