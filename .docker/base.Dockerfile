FROM php:8.3.36-fpm AS php
WORKDIR /app
RUN ls /app
RUN apt-get update
RUN apt-get install -y rsync libcurl3-dev apt-utils zlib1g-dev libpng-dev libicu-dev libonig-dev unzip poppler-utils libpng-dev libjpeg-dev default-mysql-client libzip-dev imagemagick libmagickwand-dev
RUN apt-get install -y --no-install-recommends texlive-base texlive-latex-base texlive-pictures texlive-latex-extra texlive-lang-german texlive-plain-generic texlive-fonts-recommended texlive-fonts-extra texlive-extra-utils
RUN docker-php-ext-install pdo_mysql curl exif intl mbstring pcntl zip
RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install imagick && docker-php-ext-enable imagick
RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install gd
RUN usermod -s /bin/bash www-data
RUN echo 'memory_limit = 2G' >> /usr/local/etc/php/conf.d/99-custom-php-memlimit.ini

