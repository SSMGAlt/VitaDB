#!/bin/sh
# Runs every time the container starts, after /var/www/html has already been
# bind-mounted from the git checkout. screenshots.php, icon0.php, and
# avatar.php write to a hardcoded absolute path (see deploy/README.md); these
# symlinks make that same content reachable at the relative web paths
# (icons/..., avatars/..., screenshots/...) the Angular templates use.
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
