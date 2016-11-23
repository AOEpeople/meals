#!/bin/bash

echo "Clearing application cache ... "
php ${FINAL_RELEASEFOLDER}/app/console cache:clear
echo -n "done"

echo "Warming up application cache ..."
php ${FINAL_RELEASEFOLDER}/app/console cache:warmup
echo -n "done"
