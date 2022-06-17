#!/bin/sh

# exit on error
set -e

echo "looking for init scripts..."
find scripts/init.d -maxdepth 1 -type f -print -name '*.sh' | sort -n | while read -r script
do
    if [ -x "${script}" ]; then
      printf "executing '%s' ... " "${script}"
      sh "${script}"
      printf "[done]\n"
    else
      printf "\033[33m%s: not an executable ... [skipped]\n \033[0m" "${script}"
    fi
done
