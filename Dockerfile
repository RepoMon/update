FROM ubuntu:latest

MAINTAINER Tim Rodger <tim.rodger@gmail.com>

EXPOSE 80

RUN apt-get update -qq && \
    apt-get install -y \
    php5 \
    php5-mysql \
    php5-curl \
    php5-cli \
    php5-intl \
    php5-fpm \
    curl \
    libicu-dev \
    zip \
    unzip \
    git

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/bin/composer

CMD ["/home/app/run.sh"]

RUN git config --global user.email "bot@repo-mon.com"
RUN git config --global user.name "Repository monitor"

# create the directory to store the checked out repositories
RUN mkdir /tmp/repositories

# Move application files into place
COPY src/ /home/app/

# remove any development cruft
RUN rm -rf /home/app/vendor/*

WORKDIR /home/app

# Install dependencies
RUN composer install --prefer-dist && \
    apt-get clean

RUN chmod +x /home/app/run.sh

USER root

