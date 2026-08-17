@echo off
set PHP_INI_SCAN_DIR=%~dp0php-conf.d
php artisan serve %*
