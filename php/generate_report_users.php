<?php
// Подключение к базе данных
require 'conn.php';

// Получаем event_id из GET-параметра
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// Проверяем, что event_id существует
if ($event_id <= 0) {
    die("Некорректный event_id.");
}

// Заголовки для CSV с правильной кодировкой
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="users_list.csv"');

// Открываем файл для записи в поток вывода
$output = fopen('php://output', 'w');

// Устанавливаем BOM для UTF-8, чтобы Excel корректно отображал русский текст
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // Это добавит BOM для UTF-8 в начало файла

// Заголовки столбцов (по возможности для жирного шрифта при открытии в Excel)
fputcsv($output, ['ID', 'Фамилия', 'Имя', 'Почта', 'Дата рождения', 'Пол'], ';'); // Используем точку с запятой как разделитель

// Запрос к базе данных для получения пользователей, записанных на конкретное мероприятие
$query = "
    SELECT u.user_id, u.last_name, u.first_name, u.email, u.birth_date, u.gender
    FROM users u
    JOIN user_events ue ON u.user_id = ue.user_id
    WHERE ue.event_id = $event_id
";

$result = pg_query($conn, $query);

if (!$result) {
    die("Ошибка запроса к БД: " . pg_last_error($conn));
}

// Переменная-счётчик для ID
$idCounter = 1;

// Заполнение данных
while ($user = pg_fetch_assoc($result)) {
    // Запись данных в CSV с разделителем ;
    fputcsv($output, [$idCounter++, $user['last_name'], $user['first_name'], $user['email'], $user['birth_date'], $user['gender']], ';');
}

// Закрываем поток
fclose($output);
pg_close($conn);
exit();
?>
