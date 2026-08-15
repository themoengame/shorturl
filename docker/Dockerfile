FROM php:8.5-apache

RUN docker-php-ext-install pdo_mysql

# Pastikan mod_rewrite aktif (jaga-jaga jika suatu saat dipakai)
RUN a2enmod rewrite
