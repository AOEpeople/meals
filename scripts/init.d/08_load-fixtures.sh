#!/bin/sh

if [ "${APP_ENV}" = "staging" ]; then
  echo "Load data fixtures"
  "${APP_ROOT}/bin/console" doctrine:fixtures:load --no-interaction
fi
