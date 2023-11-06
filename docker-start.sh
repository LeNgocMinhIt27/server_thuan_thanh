#!/usr/bin/env sh
cd /var/www/web-server

cd ./public
rm -r storage
cd ..


RUN chmod -R 755 public storage/ bootstrap/
RUN chown -R www-data:www-data ./bootstrap
RUN chown -R www-data:www-data ./storage
RUN chown -R www-data ./public

php artisan storage:link
php artisan optimize
php artisan migrate --force
php artisan db:seed --class UpdateToLastConfigData --force
cp supervisor/webserver-worker.conf /etc/supervisor/conf.d/laravel-worker.conf
service supervisor start
supervisorctl reread
supervisorctl update

a2enmod rewrite
apachectl -D FOREGROUND
