<?php
$host = 'roundhouse.proxy.rlwy.net';  // Хост для внешнего подключения
$port = '55214';                      // Порт для внешнего подключения
$user = 'postgres';                    // Имя пользователя
$password = 'KalODFtzEMVpNfunTSziOygJjQpyhGzv';  // Пароль
$dbname = 'railway';                  // Имя базы данных

// Добавляем порт в строку подключения
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Ошибка подключения к базе данных: " . pg_last_error());
} else {
    echo "Успешное подключение к базе данных!";
}
?>
