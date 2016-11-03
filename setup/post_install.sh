#!/bin/bash

echo "Executing post install operations"

source ${FINAL_RELEASEFOLDER}/setup/clear_cache.sh
source ${FINAL_RELEASEFOLDER}/setup/permissions.sh

echo "Restarting web server ... "
service apache2 restart
echo -n "done"

echo "Executing database migrations ... "
source ${FINAL_RELEASEFOLDER}/app/console doctrine:migrations:migrate
echo -n "done"

echo "All post install operations executed successfully!"
