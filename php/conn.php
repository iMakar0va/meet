<?php
// $host = 'localhost';
// $user = 'postgres';
// $password = '123';
// $dbname = 'meet2';

// $conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");
// if (!$conn) {
//     die("Ошибка подключения к базе данных: " . pg_last_error());
// }
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Данные для подключения
$host = '188.241.218.41';
$port = '5432';
$user = 'postgres';
$password = '123';
$dbname = 'meet2';

// Подключение с проверкой
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password options='--client_encoding=UTF8'");

if (!$conn) {
    die(json_encode(["error" => "Ошибка подключения: " . pg_last_error()]));
}

// Проверка подключения
$result = pg_query($conn, "SELECT 1");
if (!$result) {
    die(json_encode(["error" => "База недоступна: " . pg_last_error()]));
}

// Закрываем соединение
pg_close($conn);
