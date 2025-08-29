 composer install
 php artisan doctrine:schema:update --force --complete
 php artisan migrate --force
 php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan event:clear && php artisan optimize:clear

