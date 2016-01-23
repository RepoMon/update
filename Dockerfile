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
    php5-mongo \
    curl \
    libicu-dev \
    zip \
    unzip \
    git \
    npm

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/bin/composer

CMD ["/home/app/run.sh"]

#RUN git config --global user.email "robot@dep-mon.net"
#RUN git config --global user.name "Dependency monitor"

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

