FROM php:7.4.29-apache

RUN apt update \
  && apt install -y \
  g++ \
  libicu-dev \
  libpq-dev \
  libzip-dev \
  libpng-dev \
  zip \
  unzip \
  p7zip-full \
  zlib1g-dev \
  libcurl4-openssl-dev \
  pkg-config \
  libssl-dev \
  supervisor \
  gdal-bin \
  default-jre \
  default-jdk \
  postgis \
  pdftk

RUN apt install -y nano


RUN docker-php-ext-install intl \
  opcache \
  pdo \
  pdo_pgsql \
  pgsql \
  zip

RUN docker-php-ext-configure gd && docker-php-ext-install -j$(nproc) gd

RUN apt-get install -y postgresql-client
RUN pecl install --force redis
WORKDIR /var/www/

COPY supervisor/webserver-worker.conf /etc/supervisor/conf.d/laravel-worker.conf
COPY apache.conf /etc/apache2/sites-enabled/000-default.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY docker-start.sh /var/www/docker-start.sh
RUN ["chmod", "+x", "/var/www/docker-start.sh"]

WORKDIR /var/www/web-server
COPY composer.json composer.json
RUN composer install --no-scripts --no-autoloader

COPY laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf
COPY php/php.ini /usr/local/etc/php/php.ini
COPY docker.env .env
COPY . .

RUN composer dump
RUN php artisan key:generate --force

RUN chmod -R 755 public storage/ bootstrap/
RUN chown -R www-data:www-data ./bootstrap
RUN chown -R www-data:www-data ./storage
RUN chown -R www-data ./public

EXPOSE 80
