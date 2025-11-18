FROM php:8.2-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends vim curl debconf subversion git apt-transport-https apt-utils \
    build-essential locales acl mailutils wget nodejs zip unzip \
    gnupg gnupg1 gnupg2 \
    sox lame libsox-fmt-mp3 \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    openssh-server

RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql gd zip pcntl

COPY php.ini /usr/local/etc/php/php.ini
COPY php.ini /etc/php/8.2/php.ini
COPY php-fpm-pool.conf /etc/php/8.2/pool.d/www.conf

#COMPOSER
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
	mv composer.phar /usr/local/bin/composer
ENV COMPOSER_MEMORY_LIMIT -1

#SYMFONY
RUN curl -sSk https://get.symfony.com/cli/installer -O - | bash

#REDIS
RUN pecl install redis && docker-php-ext-enable redis

#AWS
RUN apt-get install -y --no-install-recommends awscli

#XDEBUG
ARG xdebug
RUN if [ $xdebug = "1" ] ; then \
    pecl install -f xdebug-3.3.1 \
    && docker-php-ext-enable xdebug \
    ; fi

#OPCACHE
RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

#AMQP
RUN apt-get -y install --no-install-recommends gcc make autoconf libc-dev pkg-config librabbitmq-dev --fix-missing
RUN pecl install amqp-1.11.0 && docker-php-ext-enable amqp

#INTL
RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev
RUN docker-php-ext-install intl

#localisation file
RUN rm -rf /var/lib/apt/lists/*
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

#MONGODB
RUN pecl install mongodb-1.17.2 && docker-php-ext-enable mongodb

#Graphviz
RUN apt-get update && apt-get install -y --no-install-recommends graphviz

#MYSQL
RUN apt-get update && apt-get install -y --no-install-recommends default-mysql-client && \
    rm -rf /var/lib/apt

ARG PUID=1000
ARG PGID=1000

ENV PUID ${PUID}
ENV PGID ${PGID}

RUN groupadd dev -g ${PGID}
RUN useradd -u ${PUID} -g dev -d /home/dev -m dev
RUN usermod -aG sudo dev
RUN echo "dev  ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers
RUN mkdir -p /home/wwwroot && chown -R dev:dev /home/wwwroot /home/dev

USER dev

WORKDIR /home/wwwroot/

EXPOSE 9000
