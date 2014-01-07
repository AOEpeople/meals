Installation
------------

    composer install

    APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data' | grep -v root | head -1 | cut -d\  -f1`
    sudo setfacl -R -m u:"$APACHEUSER":rwX -m u:`whoami`:rwX app/cache app/logs
    sudo setfacl -dR -m u:"$APACHEUSER":rwX -m u:`whoami`:rwX app/cache app/logs

    php app/console doctrine:schema:update --force

Apache configuration

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

Point your webbrowser to http://mealz.local