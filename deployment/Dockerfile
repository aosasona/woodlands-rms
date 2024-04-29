FROM composer:2.7.2 AS composer

ARG APP_ENV ?= production
ARG GITHUB_TOKEN

ENV APP_ENV ${APP_ENV}
ENV GITHUB_TOKEN ${GITHUB_TOKEN}

WORKDIR /app

COPY . .

RUN APP_ENV=${APP_ENV} sh ./deployment/setup-core.sh

RUN composer install --optimize-autoloader --no-interaction --no-progress && \
  composer dump-autoload --optimize

FROM trafex/php-nginx:3.5.0


# Comment the following lines to disable SQLite support
#########################################################
# USER root

# RUN apk add --no-cache php83-sqlite3 php83-pdo_sqlite
#########################################################

# Comment the following lines to disable MySQL support
#########################################################
# USER root

# RUN apk add --no-cache php83-mysqli php83-pdo_mysql
#########################################################

# Comment the following lines to disable PostgreSQL support
#########################################################
USER root

RUN apk add --no-cache php83-pgsql php83-pdo_pgsql
#########################################################

USER nobody

COPY --chown=nginx --from=composer /app /var/www/html

COPY --chown=nginx ./deployment/conf.d/default.conf /etc/nginx/conf.d/default.conf
