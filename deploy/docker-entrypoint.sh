#!/bin/sh
# symlinks uploads/ to the web-relative paths templates expect
set -e

UPLOAD_ROOT="/customers/8/5/0/rinnegatamante.it/httpd.www/vitadb"

for dir in icons avatars screenshots; do
	target="$UPLOAD_ROOT/$dir"
	link="/var/www/html/$dir"
	if [ ! -e "$link" ]; then
		ln -sfn "$target" "$link"
	fi
done

exec docker-php-entrypoint "$@"
