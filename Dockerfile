FROM php:8.2-apache

# Instala extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instala e habilita o Xdebug (apenas ambiente de desenvolvimento)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configuração do Xdebug
RUN { \
    echo 'xdebug.mode=develop,debug'; \
    echo 'xdebug.start_with_request=yes'; \
    echo 'xdebug.client_host=host.docker.internal'; \
    echo 'xdebug.client_port=9003'; \
    echo 'xdebug.var_display_max_depth=10'; \
    echo 'xdebug.var_display_max_children=256'; \
    echo 'xdebug.var_display_max_data=2048'; \
} > /usr/local/etc/php/conf.d/xdebug.ini

# Habilita mod_rewrite do Apache (necessário para o .htaccess funcionar)
RUN a2enmod rewrite

# Configura o Apache para permitir .htaccess na pasta do projeto
RUN echo '<Directory /var/www/html/farol>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/farol.conf \
    && a2enconf farol

# Aponta o DocumentRoot para a pasta do projeto
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/farol|' /etc/apache2/sites-available/000-default.conf

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/farol

# Copia os arquivos do projeto
COPY . .

# Instala as dependências PHP (se a pasta vendor não existir)
RUN if [ ! -d "vendor" ]; then composer install --no-interaction --optimize-autoloader; fi

# Permissões para o Apache
RUN chown -R www-data:www-data /var/www/html/farol \
    && chmod -R 755 /var/www/html/farol

EXPOSE 80