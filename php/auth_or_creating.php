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
$sql = "SELECT * FROM organizators WHERE organizator_id = :userId AND isorganizator = true";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();

// Если запрос вернул хотя бы одну строку, значит пользователь - организатор
if ($stmt->rowCount() > 0) {
    header("Location: ../creatingEvent.php");
    exit;
}

// Если пользователь не является организатором, перенаправляем на страницу профиля
header("Location: ../lk.php");
exit;
?>
