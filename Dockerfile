# Imagem base com PHP e Apache
FROM php:8.2-apache

# Copia o código do projeto para dentro do contêiner
COPY . /var/www/html/

# Habilita o mod_rewrite (caso use .htaccess)
RUN a2enmod rewrite

# Instala extensões do PHP para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Define o diretório de trabalho
WORKDIR /var/www/html

# Expõe a porta padrão do Apache
EXPOSE 80

# Inicia o Apache quando o container rodar
CMD ["apache2-foreground"]
