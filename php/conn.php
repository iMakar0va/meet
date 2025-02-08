<?php
$host = parse_url(getenv('DATABASE_URL'), PHP_URL_HOST);
$user = parse_url(getenv('DATABASE_URL'), PHP_URL_USER);
$password = parse_url(getenv('DATABASE_URL'), PHP_URL_PASS);
$dbname = ltrim(parse_url(getenv('DATABASE_URL'), PHP_URL_PATH), '/');

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Ошибка подключения к базе данных: " . pg_last_error());
}
?>
