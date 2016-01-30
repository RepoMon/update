FROM ubuntu:latest

MAINTAINER Tim Rodger <tim.rodger@gmail.com>

RUN apt-get update -qq && \
    apt-get install -y \
    php5 \
    php5-curl \
    php5-cli \
    php5-intl \
    php5-fpm \
    curl \
    libicu-dev \
    zip \
    unzip \
    git \
    npm

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/bin/composer

CMD ["/home/app/run.sh"]

# create the directory to store the checked out repositories
RUN mkdir /tmp/repositories

# Move application files into place
COPY src/ /home/app/

WORKDIR /home/app

# Install dependencies
RUN composer install --prefer-dist && \
    apt-get clean

RUN chmod +x /home/app/run.sh

USER root

