FROM php:8.3-cli

RUN apt-get update && \
    apt-get install -y unzip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
