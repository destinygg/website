FROM php:7.4-fpm as base

WORKDIR /www/www.destiny.gg

RUN apt-get update && apt-get install -y \
    zip \
    git \
    cron

RUN docker-php-ext-install pdo_mysql

RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

COPY composer.json composer.json
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install


# ----- runner -----
FROM base

COPY ./config ./config
COPY ./cron ./cron
COPY ./lib ./lib
COPY ./public/index.php ./public/index.php
COPY ./scripts ./scripts
COPY ./views ./views
COPY composer.json composer.json

#cronjob
COPY ./config/docker-cron /etc/cron.d/docker-cron
RUN chmod 0644 /etc/cron.d/docker-cron
RUN crontab /etc/cron.d/docker-cron

#entrypoint
COPY ./config/docker-entrypoint.sh /usr/local/bin/
RUN chmod 777 /usr/local/bin/docker-entrypoint.sh \
    && ln -s /usr/local/bin/docker-entrypoint.sh /

#CMD ["php-fpm"]
ENTRYPOINT ["docker-entrypoint.sh"]
