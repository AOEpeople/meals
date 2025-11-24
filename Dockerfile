# generate frontend assets
FROM node:24 AS frontend
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y -o Dpkg::Options::="--force-confold" --no-install-recommends --no-install-suggests \
        build-essential \
        nodejs
WORKDIR /var/www/html/src/Resources
COPY src/Resources/package.json src/Resources/package-lock.json ./
RUN npm install
COPY src/Resources/ .
COPY public .
RUN NODE_ENV=production npm run build

# build production container
FROM php:8.5-fpm-alpine
RUN apk --no-cache add \
        icu-dev \
        unzip \
        busybox-suid \
    && docker-php-ext-install bcmath calendar intl opcache pdo_mysql  \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    # cleanup
    && rm -rf /tmp/*

# add service configuration
COPY --chown=www-data:www-data docker/web/ /container/

ENV APP_DEBUG="0" \
    APP_ENV="prod" \
    APP_ROOT="/var/www/meals" \
    # PHP default settings
    PHP_MAX_EXECUTION_TIME=60 \
    PHP_MEMORY_LIMIT=120M

WORKDIR $APP_ROOT

RUN chown -R www-data:www-data $APP_ROOT

USER www-data:www-data

# add composer dependencies
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install \
        --no-scripts \
        --optimize-autoloader \
        --prefer-dist

# add application code and assets
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=frontend /var/www/html/public/static ./public/static

# trigger composer post install scripts, e.g. clear cache, create auto-load script
RUN composer run-script --no-cache post-install-cmd

USER root
RUN \
    # copy php.ini overrides
    cp /container/php.ini-overrides /usr/local/etc/php/conf.d/meals-overrides.ini \
    # fix file permissions
    && find . -type d -exec chmod 755 '{}' \+ \
    && find . -type f -exec chmod 640 '{}' \+ \
    # make CLI scripts executable
    && find bin scripts vendor/bin -type f -exec chmod 740 '{}' \+ \
    # set non php files in public directory as readonly
    && find public -type f -not -name "*.php" -exec chmod 644 '{}' \+

RUN echo "* * * * * /var/www/meals/bin/console meals:keep-alive-connection > /dev/stdout" >> /etc/crontabs/www-data
RUN chmod +x "/container/entrypoint"
RUN chmod +x "/container/scripts/wait-for"

ENTRYPOINT ["/container/entrypoint"]

CMD ["su", "-s", "/bin/sh", "www-data", "-c", "php-fpm"]
