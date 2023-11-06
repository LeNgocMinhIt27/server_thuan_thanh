# Giao thong van tai quan su

## Requiminend

- php ~ 7.4.x
  - pgsql
  - fileinfo
  - gd2
  - curl
  - mbstring
  - exif
- Postgresql 12.5
  - PostGIS 3.0
- 7zip

## setup

### server

```shell
composer install
php artisan migrate --seed
php artisan optimize
```

### client

```shell
yarn
```

tạo copy file .env.local.example với tên .env.local

## Build

### Thiết lập env

- SANCTUM_STATEFUL_DOMAINS=<địa chỉ của web host web-serve>,<địa chỉ của web host web-client(nếu tách)>

### start server

```shell
php artisan serve
```

### Thiết lập Schedule cho laravel

cần cài đặt schedule của laravel
của window xem <https://www.jdsoftvera.com/how-to-add-laravel-task-schedule-on-windows/>

### Lệnh tùy Chỉnh

  #### backup

```shell
php artisan task:backup
```

file sinh ra ở 'storage/backup'

#### restore

```shell
php artisan task:restore {file}
```

file cần ở 'storage/backup'
