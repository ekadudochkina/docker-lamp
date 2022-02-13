FROM php:7.4-fpm
RUN docker-php-ext-install mysqli pdo pdo_mysql

#Install xdebug
RUN pecl install xdebug-2.9.2 && docker-php-ext-enable xdebug

CMD ["php-fpm"]