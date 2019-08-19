#!/bin/bash -e

echo -n "Clearing application cache ... "
php ${FINAL_RELEASEFOLDER}/app/console cache:clear
echo "done"

echo -n "Warming up application cache ..."
php ${FINAL_RELEASEFOLDER}/app/console cache:warmup
echo "done"
