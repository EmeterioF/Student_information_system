composer global require Laravel/installer

composer require yajra/laravel-datatables-oracle

composer install

cp .env.example .env

php artisan key:generate 

php artisan migrate:refresh --seed
