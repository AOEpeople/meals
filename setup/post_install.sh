#!/bin/bash -e

echo "Executing post install operations"

source ${FINAL_RELEASEFOLDER}/setup/clear_cache.sh
source ${FINAL_RELEASEFOLDER}/setup/permissions.sh

echo "Restarting web server ... "
service apache2 restart
echo -n "done"

if [ "${ENVIRONMENT}" == "deploy" -o  "${ENVIRONMENT}" == "dev" ]; then
	/home/systemstorage/bin/restore_backup.sh -u${SYMFONY__DATABASE_USER} -d${SYMFONY__DATABASE_NAME} -p${SYMFONY__DATABASE_PASSWORD} -hlocalhost -s/home/systemstorage/systemstorage/mealz/backup/production -v -f
fi

echo "Executing database migrations ... "
php ${FINAL_RELEASEFOLDER}/app/console doctrine:migrations:migrate -n
echo -n "done"

echo "All post install operations executed successfully!"
echo "finished post install operations!"