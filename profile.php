<?php
session_start();
require 'php/conn.php';

// Проверяем, вошел ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Получаем данные пользователя из БД
$userId = $_SESSION['user_id'];
$getUserQuery = "SELECT email, name FROM users WHERE user_id = $1";
$result = pg_query_params($conn, $getUserQuery, [$userId]);

if ($result && pg_num_rows($result) > 0) {
    $user = pg_fetch_assoc($result);
} else {
    die('Ошибка загрузки профиля');
}

pg_close($conn);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
</head>

<body>
    <h1>Добро пожаловать, <?php echo htmlspecialchars($user['name']); ?>!</h1>
    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    <a href="logout.php">Выйти</a>
</body>

</html>