# build frontend assets
FROM node:16 as frontend
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y -o Dpkg::Options::="--force-confold" --no-install-recommends --no-install-suggests \
        build-essential \
        nodejs
WORKDIR var/www/html/src/Resources
COPY src/Resources/package.json src/Resources/yarn.lock ./
RUN yarn install
COPY src/Resources/ .
COPY public .
RUN NODE_ENV=production yarn run build

# build production container
FROM php:7.4-apache
RUN apt-get update && apt-get upgrade -y && apt-get install --no-install-recommends --no-install-suggests -y \
        ca-certificates \
        git \
        libicu-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libmcrypt-dev \
        zip \
        unzip \
        --no-install-recommends \
    && pecl install mcrypt-1.0.4 \
    && a2enmod rewrite \
    && docker-php-ext-install -j$(nproc) bcmath calendar gd intl pdo_mysql mysqli opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-enable mcrypt mysqli \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /tmp/* \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -L https://github.com/a8m/envsubst/releases/download/v1.2.0/envsubst-Linux-x86_64 -o /usr/local/bin/envsubst \
    && chmod +x /usr/local/bin/envsubst

# add composer dependencies
COPY composer.json composer.lock ./
RUN composer install \
        --no-plugins \
        --no-scripts \
        --optimize-autoloader \
        --prefer-dist \
    && composer clearcache \
    && mkdir -p public/bundles/ \
    && chown -R www-data:www-data public/bundles

# add packages and configure development image
ARG BUILD_DEV=false
RUN  if [ "$BUILD_DEV" = "true" ]; then \
    echo "Installing dev dependencies" \
    && apt-get update -y && apt-get install -y \
        vim \
        --no-install-recommends \
    && rm -rf /var/lib/apt/lists/* /tmp/* \
; fi

# add service configuration
COPY --chown=www-data:www-data docker/web/ /container/

# add custom code and compiled frontend assets
COPY --chown=www-data:www-data . /var/www/html/
COPY --chown=www-data:www-data --from=frontend /var/www/html/public/static ./public/static

# clear symfony cache and fix file permissions
RUN composer run-script --no-cache post-install-cmd \
    && chown -R www-data:www-data /var/www/html

ENTRYPOINT ["/container/entrypoint"]
CMD ["apache2-foreground"]
