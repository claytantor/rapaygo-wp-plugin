FROM wordpress:latest


WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer selfupdate
COPY composer.json composer.json
RUN php /usr/local/bin/composer install --no-dev