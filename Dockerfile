FROM ubuntu:latest

MAINTAINER Tim Rodger <tim.rodger@gmail.com>

RUN apt-get update -qq && \
    apt-get install -y \
    curl \
    libicu-dev \
    zip \
    unzip \
    git

# install bcmath and mbstring for videlalvaro/php-amqplib
RUN docker-php-ext-install bcmath mbstring

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/bin/composer

CMD ["/home/app/run.sh"]

# Move application files into place
COPY src/ /home/app/

# remove any development cruft
RUN rm -rf /home/app/vendor/*

# create the directory to store the checked out repositories
RUN mkdir /tmp/repositories

WORKDIR /home/app

# Install dependencies
RUN composer install --prefer-dist && \
    apt-get clean

RUN git config --global user.email "bot@repo-mon.com"
RUN git config --global user.name "Repository monitor"

RUN chmod +x /home/app/run.sh

USER root

