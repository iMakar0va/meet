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

// Подключение к удаленной БД
$host = '188.241.218.41';  // IP или домен сервера PostgreSQL
$port = '5432';
$user = 'postgres';
$password = '123';
$dbname = 'meet2';

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die(json_encode(["error" => "Ошибка подключения: " . pg_last_error()]));
}
pg_close($conn);
