#!/bin/bash

echo ${ENVIRONMENT} > ${FINAL_RELEASEFOLDER}/app/DEFAULT_ENV

out="${FINAL_RELEASEFOLDER}/app/config/${ENVIRONMENT}/parameters.yml"
echo "parameters:" > $out
while IFS='=' read -r name value ; do
  if [[ $name == 'SYMFONY__'* ]]; then
    parameter=${name#SYMFONY__}
    parameter=${parameter//__/\.}
    parameter=${parameter,,}
    echo "  ${parameter}: \"$value\"" >> $out
  fi
done < <(env)
