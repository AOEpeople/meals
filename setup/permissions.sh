#!/bin/bash

echo "setting file permissions..."
chown -R root:root ${FINAL_RELEASEFOLDER}
chown -R www-data:www-data ${FINAL_RELEASEFOLDER}/app/cache ${FINAL_RELEASEFOLDER}/app/logs ${FINAL_RELEASEFOLDER}/web/media