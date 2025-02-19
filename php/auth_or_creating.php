<?php
session_start();
require 'conn.php';

// Если пользователь не авторизован, перенаправляем на страницу авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Проверяем, является ли пользователь организатором, учитывая, что статус имеет тип boolean
$sql = "SELECT * FROM organizators WHERE organizator_id = $1 AND is_organizator = true";
$result = pg_query_params($conn, $sql, [$userId]);

// Если запрос вернул хотя бы одну строку, значит пользователь - организатор
if ($result && pg_num_rows($result) > 0) {
    header("Location: ../creatingEvent.php");
    exit;
}

// Если пользователь не является организатором, перенаправляем на страницу профиля
header("Location: ../lk.php");
exit;
?>
