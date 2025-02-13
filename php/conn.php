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

try {
    // Строка подключения с использованием PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);

    // Устанавливаем атрибут для исключений
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Подключение успешно!";
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
}
?>

