FROM php:8.0-apache

# Устанавливаем зависимости для PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev

# Устанавливаем PHP расширения (включая pgsql для pg_* функций)
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Копируем все файлы в контейнер
COPY . /var/www/html/

# Обеспечиваем доступ к нужным папкам
RUN chown -R www-data:www-data /var/www/html

# Открываем порт для Apache
EXPOSE 80

# Запускаем Apache
CMD ["apache2-foreground"]
