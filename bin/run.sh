#!/bin/sh

echo "Removing old cache if any"
rm -f composer.lock
rm -rf var/cache/*
rm -rf var/log/*


echo "Dumping assets"
php bin/console  assets:install --symlink --relative

php bin/console ca:cl

#echo "Database update"
php bin/console doctrine:schema:update --force

chmod -R 0777 var/log/ var/cache/

#'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'
