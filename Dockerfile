# build frontend assets
FROM node:17 as frontend
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
FROM php:7.4-fpm-alpine
RUN apk --no-cache add \
        unzip \
    && docker-php-ext-install bcmath calendar pdo_mysql opcache \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    # cleanup
    && rm -rf /tmp/*

# copy php.ini overrides
COPY docker/web/php.ini-overrides /usr/local/etc/php/conf.d/meals-overrides.ini
COPY docker/web/scripts/wait-for /usr/local/bin/

ENV APP_DEBUG="0" \
    APP_ENV="prod" \
    APP_ROOT="/var/www/meals" \
    # PHP settings
    PHP_MAX_EXECUTION_TIME=60 \
    PHP_MEMORY_LIMIT=120M

WORKDIR $APP_ROOT

# tasks that should/can only be performed as root ???
RUN chown -R www-data:www-data $APP_ROOT

# add service configuration
COPY --chown=www-data:www-data docker/web/ /container/

USER www-data:www-data

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


# add custom code and compiled frontend assets
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=frontend /var/www/html/public/static ./public/static

# clear symfony cache and fix file permissions
RUN composer run-script --no-cache post-install-cmd

# fix file permissions
RUN find . -type d -exec chmod 755 '{}' \+ \
    && find . -type f -exec chmod 640 '{}' \+ \
    # set CLI scripts to be executables
    && find bin scripts vendor/bin -type f -exec chmod 740 '{}' \+ \
    # non php files in public directory should be readable by others, e.g. nginx
    && find public -type f -not -name "*.php" -exec chmod 644 '{}' \+

ENTRYPOINT ["/container/entrypoint"]
CMD ["php-fpm"]
