<?php
// $host = 'localhost';
// $user = 'postgres';
// $password = '123';
// $dbname = 'meet2';

// $conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");
// if (!$conn) {
//     die("Ошибка подключения к базе данных: " . pg_last_error());
// }
$host = 'postgres.railway.internal';  // Хост от Railway
$port = '5432';                     // Порт от Railway
$user = 'postgres';                  // Ваш PostgreSQL пользователь
$password = 'KalODFtzEMVpNfunTSziOygJjQpyhGzv';  // Ваш пароль от Railway
$dbname = 'railway';                 // Имя базы данных, указанное на Railway

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Ошибка подключения к базе данных: " . pg_last_error());
}

// Дополнительные действия с базой данных
