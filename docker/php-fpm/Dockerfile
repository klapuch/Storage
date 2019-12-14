FROM php:fpm-alpine

RUN apk update  && apk add --no-cache git postgresql-dev zip unzip zlib-dev libzip-dev \
	&& docker-php-ext-install pdo_pgsql zip sysvsem

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN rm -rf /var/cache/apk/*