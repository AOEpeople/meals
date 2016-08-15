#!/bin/bash

echo "executing post install scripts"
source ${FINAL_RELEASEFOLDER}/setup/clear_cache.sh
source ${FINAL_RELEASEFOLDER}/setup/permissions.sh
echo "finished post install operations!"