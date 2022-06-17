#!/bin/sh

if [ "${APP_ENV}" = "prod" ] || [ "${APP_ENV}" = "staging" ]; then
    echo "Parse .env files and dump content to .env.local.php file."
    composer dump-env "${APP_ENV}"
fi
