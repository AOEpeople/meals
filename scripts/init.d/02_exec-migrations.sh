#!/bin/sh

"${APP_ROOT}/bin/console" doctrine:migrations:migrate -n
