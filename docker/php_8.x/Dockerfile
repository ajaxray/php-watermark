FROM php:8-cli-alpine

COPY . /usr/app
WORKDIR /usr/app

ADD php-ini-overrides.ini /usr/local/etc/php/conf.d/overrides.ini

# Install required tools
RUN apk add --update --no-cache sqlite-dev rsync imagemagick bash pngcrush optipng=0.7.7-r0

# Install PHP extensions
# Available extensions:
# bcmath bz2 calendar ctype curl dba dom enchant exif fileinfo filter ftp gd gettext gmp hash iconv
# imap interbase intl json ldap mbstring mcrypt mssql mysql mysqli oci8 odbc opcache pcntl pdo
# pdo_dblib pdo_firebird pdo_mysql pdo_oci pdo_odbc pdo_pgsql pdo_sqlite pgsql phar posix pspell
# readline recode reflection session shmop simplexml snmp soap sockets spl standard sybase_ct sysvmsg
# sysvsem sysvshm tidy tokenizer wddx xml xmlreader xmlrpc xmlwriter xsl zip
RUN docker-php-ext-install -j$(nproc) pdo_mysql pdo_sqlite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ENTRYPOINT ["php"]