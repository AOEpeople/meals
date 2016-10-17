#!/bin/bash

echo "clearing cache..."
php ${FINAL_RELEASEFOLDER}/app/console cache:clear
echo "warmup cache..."
php ${FINAL_RELEASEFOLDER}/app/console cache:warmup