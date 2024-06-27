echo "Date base generated"

php artisan doctrine:schema:update --force
php artisan optimize
