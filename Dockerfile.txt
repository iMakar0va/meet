# Используем официальный образ PHP с Apache
FROM php:8.0-apache

# Устанавливаем необходимые расширения для работы с PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Копируем все файлы проекта в контейнер
COPY . /var/www/html/

# Устанавливаем рабочую директорию
WORKDIR /var/www/html/

# Открываем порт 80 для доступа к приложению
EXPOSE 80

# Запускаем Apache с флагом foreground, чтобы контейнер не завершился
CMD ["apache2-foreground"]
