#!/bin/sh

echo "Removing old cache if any"
rm -f composer.lock
rm -rf var/cache/*
rm -rf var/log/*



echo "Dumping assets"
bin/console  assets:install --symlink --relative

php bin/console cache:clear

echo "Database update"
php bin/console doctrine:schema:update --complete

chmod -R 0777 var/log/ var/cache/


