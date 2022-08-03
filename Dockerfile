FROM php:8.0

RUN apt-get update && apt-get install -y \
curl \
wget \
git \
procps \
libfreetype6-dev \
libjpeg62-turbo-dev \
libmcrypt-dev \
libxml2-dev \
libzip-dev \
libpng-dev \
libonig-dev \
libsqlite3-dev \
libc-client-dev libkrb5-dev \
gettext \
iputils-ping \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
&& docker-php-ext-install -j$(nproc) iconv mbstring mysqli pdo_mysql zip pdo_sqlite gettext imap gd sockets

RUN docker-php-ext-enable pdo_sqlite imap gettext

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD docker.cfg/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/event_sourcing

#RUN composer install --prefer-source --no-interaction

CMD [ "php", "./examples/demo.php" ]
