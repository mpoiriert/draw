FROM php:7.2.17-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends vim curl debconf subversion git apt-transport-https apt-utils \
    build-essential locales acl mailutils wget nodejs zip unzip \
    gnupg gnupg1 gnupg2 \
    sox lame libsox-fmt-mp3 \
    libpng-dev \
    libjpeg-dev

RUN docker-php-ext-configure gd --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install pdo pdo_mysql gd zip pcntl

COPY php.ini /usr/local/etc/php/php.ini
COPY php.ini /etc/php/7.2.17/php.ini
COPY php-fpm-pool.conf /etc/php/7.2.17/pool.d/www.conf

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

# Install prestissimo (composer plugin). Plugin that downloads packages in parallel to speed up the installation process
# After release of Composer 2.x, remove prestissimo, because parallelism already merged into Composer 2.x branch:
# https://github.com/composer/composer/pull/7904
RUN composer global require hirak/prestissimo

ARG PUID=1000
ARG PGID=1000

ENV PUID ${PUID}
ENV PGID ${PGID}

RUN groupadd dev -g ${PGID}
RUN useradd -u ${PUID} -g dev -d /home/dev -m dev

RUN rm -rf /var/lib/apt/lists/*
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

#XDEBUG
ARG xdebug
RUN if [ $xdebug = "1" ] ; then \
    pecl install xdebug \
    && docker-php-ext-enable xdebug \
    ; fi

#OPCACHE
RUN docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

WORKDIR /home/wwwroot/

EXPOSE 9000