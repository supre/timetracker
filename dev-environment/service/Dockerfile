FROM php:7.3-apache

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN pecl install xdebug \
  && docker-php-ext-enable xdebug

RUN apt-get update && apt-get install -y zlib1g-dev libzip-dev 

RUN docker-php-ext-install pdo_mysql zip

RUN echo "xdebug.remote_autostart=1" >> $PHP_INI_DIR/php.ini
RUN echo "xdebug.remote_enable=1" >> $PHP_INI_DIR/php.ini
RUN echo "xdebug.remote_host=host.docker.internal" >> $PHP_INI_DIR/php.ini
RUN a2enmod rewrite

COPY ./startscript /bin/startscript
RUN chmod +x /bin/startscript

COPY ./downloadcomposer /root/downloadcomposer
RUN chmod +x /root/downloadcomposer \
  && cd /root && ./downloadcomposer

CMD ["startscript"]