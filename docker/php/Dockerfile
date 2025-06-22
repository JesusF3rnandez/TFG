FROM php:8.1-fpm

# Argumento para el UID del usuario host
ARG UID
EXPOSE 9000

# Crear usuario de aplicación con el mismo UID que el host
RUN adduser -u ${UID} --disabled-password --gecos "" appuser

# Configuración del entorno del usuario
RUN mkdir /home/appuser/.ssh \
    && chown -R appuser:appuser /home/appuser/ \
    && echo "StrictHostKeyChecking no" >> /home/appuser/.ssh/config \
    && echo "export COLUMNS=300" >> /home/appuser/.bashrc \
    && echo "alias sf=/appdata/www/bin/console" >> /home/appuser/.bashrc

# Configuración de PHP
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

# Instalación de dependencias del sistema y extensiones de PHP
RUN apt-get update \
    && apt-get install -y \
        git acl openssl openssh-client wget zip vim libssh-dev \
        libpng-dev zlib1g-dev libzip-dev libxml2-dev libicu-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip gd soap bcmath sockets \
    && pecl install xdebug \
    && docker-php-ext-enable --ini-name 05-opcache.ini opcache xdebug

# Instalación de Composer
RUN curl https://getcomposer.org/composer.phar -o /usr/bin/composer \
    && chmod +x /usr/bin/composer \
    && composer self-update

# Instalación de PHP-CS-Fixer
RUN wget https://cs.symfony.com/download/php-cs-fixer-v2.phar -O php-cs-fixer \
    && chmod a+x php-cs-fixer \
    && mv php-cs-fixer /usr/local/bin/php-cs-fixer

# Crear directorio de trabajo
RUN mkdir -p /appdata/www
WORKDIR /appdata/www

# Copiar archivos composer para aprovechar la cache de Docker
COPY composer.json composer.lock ./

# Instalar dependencias del proyecto definidas en composer.json y composer.lock
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Instalar doctrine/orm y symfony/maker-bundle para que estén disponibles en el contenedor
RUN composer require doctrine/orm symfony/maker-bundle --dev --no-interaction --no-scripts

# Copiar el resto del proyecto
COPY . .
