<?php
// $host = 'localhost';
// $user = 'postgres';
// $password = '123';
// $dbname = 'meet2';

// $conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");
// if (!$conn) {
//     die("Ошибка подключения к базе данных: " . pg_last_error());
// }
$host = 'roundhouse.proxy.rlwy.net';  // Хост для внешнего подключения
$port = '55214';                      // Порт для внешнего подключения
$user = 'postgres';                    // Имя пользователя
$password = 'KalODFtzEMVpNfunTSziOygJjQpyhGzv';  // Пароль
$dbname = 'railway';                  // Имя базы данных

// Строка подключения
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Ошибка подключения к базе данных: " . pg_last_error());
}

echo "Подключение успешно!";
?>

