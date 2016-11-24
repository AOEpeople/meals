#!/bin/bash -e

echo "Setting file permissions ... "
chown -R root:root ${FINAL_RELEASEFOLDER}
chown -R www-data:www-data ${FINAL_RELEASEFOLDER}/app/cache ${FINAL_RELEASEFOLDER}/app/logs ${FINAL_RELEASEFOLDER}/web/media
chmod -R 775 ${FINAL_RELEASEFOLDER}/app/logs
echo -n "done"
