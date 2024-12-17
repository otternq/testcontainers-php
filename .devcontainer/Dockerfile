FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get -qq update
RUN apt-get -qq install -y --no-install-recommends ca-certificates wget gnupg
RUN apt-get -qq install -y --no-install-recommends software-properties-common
RUN env LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
RUN add-apt-repository ppa:maxmind/ppa
RUN apt-get -qq clean all
RUN apt-get -qq update
RUN apt-get -qq install -y --no-install-recommends build-essential automake autoconf libtool \
    curl \
    redis-tools \
    zip \
    unzip \
    gzip \
    libcurl3-dev \
    git \
    ack
# libcurl3-dev needed to install the datadog tracer from source

ARG PHP_VERSION="7.0"

# Note: the following will install PHP 7.0 (FPM and co.) as well as PHP 7.0 (CLI). This is because pear's tools depend
# on PHP7 for CLI use and that's just how it is.
RUN apt-get -qq install -y --no-install-recommends \
    runit \
    nginx \
    "php${PHP_VERSION}" \
    "php${PHP_VERSION}-dev" \
    "php${PHP_VERSION}-cli" \
    "php${PHP_VERSION}-fpm" \
    "php${PHP_VERSION}-mysqlnd" \
    "php${PHP_VERSION}-curl" \
    "php${PHP_VERSION}-geoip" \
    "php${PHP_VERSION}-memcached" \
    "php${PHP_VERSION}-gd" \
    "php${PHP_VERSION}-mcrypt" \
    "php${PHP_VERSION}-common" \
    "php${PHP_VERSION}-xml" \
    "php${PHP_VERSION}-apc" \
    "php${PHP_VERSION}-opcache" \
    "php${PHP_VERSION}-redis" \
    "php${PHP_VERSION}-mbstring" \
    php-pear

RUN update-alternatives --set php "/usr/bin/php${PHP_VERSION}"
RUN update-alternatives --set php-config "/usr/bin/php-config${PHP_VERSION}"
RUN update-alternatives --set phpize "/usr/bin/phpize${PHP_VERSION}"

#
# Install composer
RUN curl -s https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

ARG PROTOBUF_TAG="3.7.1"
RUN pecl install protobuf-${PROTOBUF_TAG}

ARG XDEBUG_VERSION="2.7.0"
RUN pecl install xdebug-${XDEBUG_VERSION}

WORKDIR /

ARG PHP_EXT_VERSION="20151012"

RUN apt-get -qq remove -y --purge php7.0-dev 'software-properties' 'python3' '.-dev$' 'build-essential' 'automake' 'autoconf' 'libtool' '^autoconf$' '^cpp$' '^gcc$' '^g\+\+$'

RUN apt-get -qq autoremove -y

RUN apt-get -qq autoclean

RUN apt-get -qq clean

# Remove a handful of things that're used to bootstrap the base image but not at runtime.
# lists: dead weight, apt is unused past this point
# manpages, perl, gcc build thingies: unused
# *.a files: only used in static linking while building programs
RUN rm -rf \
    /var/lib/apt/lists/* \
    /usr/share/doc \
    /usr/share/man

# Switch back to dialog for any ad-hoc use of apt-get
ENV DEBIAN_FRONTEND=dialog