#!/usr/bin/env sh

cd /var/www/web-server

while [ true ]
do
    php artisan schedule:run --verbose --no-interaction
    sleep 60
done
