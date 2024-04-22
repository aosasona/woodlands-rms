FROM composer:2.7.2 AS composer

COPY . /app

RUN composer install --optimize-autoloader --no-interaction --no-progress && \
  composer dump-autoload --optimize && \
  composer migrate

FROM trafex/php-nginx:3.5.0


# Comment the following lines to disable SQLite support
#########################################################
USER root

RUN apk add --no-cache php83-sqlite3 php83-pdo_sqlite
#########################################################

# Comment the following lines to disable MySQL support
#########################################################
# USER root
#
# RUN apk add --no-cache php83-mysqli php83-pdo_mysql
#########################################################

USER nobody

COPY --chown=nginx --from=composer /app /var/www/html

COPY --chown=nginx ./deployment/conf.d/default.conf /etc/nginx/conf.d/default.conf
