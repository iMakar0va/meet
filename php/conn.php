<?php
$host = 'localhost';
$user = 'postgres';
$password = '123';
$dbname = 'meet2';

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Ошибка подключения к базе данных: " . pg_last_error());
}
