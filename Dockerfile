# get php dependencies in separate stage
FROM php:8.0.8-apache as composer
RUN apt-get update -y && apt-get install -y git zip unzip \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = .*/memory_limit = -1/' "$PHP_INI_DIR/php.ini" \
    && curl -sS https://getcomposer.org/installer | php -- --1 --install-dir=/usr/local/bin --filename=composer
COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --optimize-autoloader --prefer-dist

# build frontend assets
FROM node:16 as frontend
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y -o Dpkg::Options::="--force-confold" --no-install-recommends --no-install-suggests \
        build-essential \
        nodejs
WORKDIR var/www/html/app/Resources
COPY app/Resources/package.json app/Resources/yarn.lock ./
RUN yarn install
COPY app/Resources/ .
COPY web .
RUN NODE_ENV=production yarn run build

# build production container
FROM php:8.0.8-apache
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
        libicu-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libmcrypt-dev \
        mysql-client \
        sendmail \
        zip \
        --no-install-recommends \
    && a2enmod rewrite \
    && docker-php-ext-install -j$(nproc) bcmath calendar gd intl mcrypt pdo_mysql mysqli opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-enable mysqli \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /tmp/* \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/max_execution_time = .*/max_execution_time = 300/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/;date.timezone =.*/date.timezone= "Europe\/Berlin"/' "$PHP_INI_DIR/php.ini" \
    && printf "[mail function]\nsendmail_path='/usr/sbin/sendmail -t -i'\n" > /usr/local/etc/php/conf.d/sendmail.ini

COPY --chown=www-data:www-data docker/web/apache.conf /etc/apache2/sites-enabled/meals.conf
COPY --chown=www-data:www-data --from=composer /usr/local/bin/composer /usr/local/bin/composer
COPY --chown=www-data:www-data --from=composer /var/www/html/vendor/ ./vendor/
COPY --chown=www-data:www-data --from=composer /var/www/html/bin/ ./bin/
COPY --chown=www-data:www-data --from=frontend /var/www/html/web/ ./web/

COPY --chown=www-data:www-data . /var/www/html/

# add packages and configure development image
ARG BUILD_DEV=false
RUN  if [ "$BUILD_DEV" = "true" ]; then echo "Installing dev dependencies" \
    && apt-get update -y && apt-get install -y \
        git \
        vim \
        --no-install-recommends \
    && curl -L -o /usr/sbin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 \
    && chmod +x /usr/sbin/mhsendmail \
    && rm -rf /var/lib/apt/lists/* /tmp/* \
    ; fi

RUN  if [ "$BUILD_DEV" = "true" ]; then echo "Setting up PHP for development..." \
    && sed -i 's/max_execution_time = .*/max_execution_time = 300/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/;date.timezone =.*/date.timezone= "Europe\/Berlin"/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = .*/memory_limit = -1/' "$PHP_INI_DIR/php.ini" \
    && printf "[mail function]\nsendmail_path='/usr/sbin/mhsendmail --smtp-addr=mail:1025'\n" > /usr/local/etc/php/conf.d/sendmail.ini \
    && printf "[opcache]\nopcache.enable = 1\nopcache.enable_cli = 1\n" >> "$PHP_INI_DIR/php.ini" \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer2 \
    ; fi
