# Mealz for Zombies

## Installation

    composer install

    APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data' | grep -v root | head -1 | cut -d\  -f1`
    sudo setfacl -R -m u:"$APACHEUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/media
    sudo setfacl -dR -m u:"$APACHEUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/media

    php app/console doctrine:schema:update --force

### Apache configuration

    <VirtualHost *:80>
            ServerName mealz.local
            DocumentRoot /var/www/mealz/web
            DirectoryIndex app_dev.php

            # autostart xdebug on this host
            # php_value xdebug.remote_enable 1

            <Directory "/var/www/mealz/web">
                    AllowOverride All
                    Allow from All

                    <IfModule mod_rewrite.c>
                            RewriteEngine On

                            RewriteCond %{REQUEST_FILENAME} !-f
                            RewriteRule ^(.*)$ app_dev.php [QSA,L]
                    </IfModule>

            </Directory>
    </VirtualHost>

### PayPal API configuration

Export your client id and secret as environment variables.
See http://symfony.com/doc/current/cookbook/configuration/external_parameters.html

### Frontend build

```
cd /var/www/mealz/devbox/app/Resources
npm install
./node_modules/.bin/bower install
./node_modules/.bin/gulp
```

### You're done

Point your webbrowser to http://mealz.local

## Troubleshooting

### SQLSTATE[42S22]: Column not found: 1054 Unknown column

    php app/console doctrine:schema:update --force --env=dev

## Developer information

### User roles

The following roles are in use:

  * ROLE_USER: basically everyone who is allowed to join the meals
  * ROLE_KITCHEN_STAFF: allowed to create and edit dishes and meals
  * ROLE_LOGIN_LDAP: for users who are authenticated via LDAP

### Test data

To load up some test data, run

    php app/console doctrine:fixtures:load --env=dev

It generates dishes, meals and users.

You can use "john", "jane, "alice" and "bob" to login. Their password is just their username.
The User "kochomi" is allowed to modify dishes and edit meals.

### Running tests

Some tests require a working database. The database dedicated for running tests can be configured by setting
the database name in `app/config/parameters.yml` as `database_name_testing`. Credentials should be the same
as for the dev environment.

Before running phpunit make sure the database schema is up-to-date:

    php app/console doctrine:schema:update --env=test --force
    bin/phpunit
