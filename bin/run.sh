 composer install
 php artisan doctrine:schema:update --force --complete
 php artisan optimize:clear
 echo "" >storage/logs/laravel.log
