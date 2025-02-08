<?php
require 'conn.php';
session_start();
header('Content-Type: application/json');

$response = ['success' => false];

// Проверяем, что запрос - POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Недопустимый метод запроса.';
    echo json_encode($response);
    exit;
}

// Проверяем, авторизован ли пользователь
if (empty($_SESSION['user_id'])) {
    $response['message'] = 'Невозможно удалить, пользователь не авторизован.';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];

// Проверяем, является ли пользователь организатором
$stmtCheckOrg = pg_prepare($conn, "check_org", "SELECT 1 FROM organizators WHERE organizator_id = $1;");
$resultCheckOrg = pg_execute($conn, "check_org", [$userId]);

if ($resultCheckOrg === false) {
    $response['message'] = 'Ошибка при проверке организатора: ' . pg_last_error($conn);
    echo json_encode($response);
    exit;
}

// Если пользователь - организатор, удаляем его из organizators
if (pg_num_rows($resultCheckOrg) > 0) {
    $stmtDeleteOrg = pg_prepare($conn, "delete_organizer", "DELETE FROM organizators WHERE organizator_id = $1;");
    $resultDeleteOrg = pg_execute($conn, "delete_organizer", [$userId]);

    if ($resultDeleteOrg === false) {
        $response['message'] = 'Ошибка при удалении из organizators: ' . pg_last_error($conn);
        echo json_encode($response);
        exit;
    }
}

// Удаляем пользователя из users
$stmtDeleteUser = pg_prepare($conn, "delete_user", "DELETE FROM users WHERE user_id = $1;");
$resultDeleteUser = pg_execute($conn, "delete_user", [$userId]);

if ($resultDeleteUser) {
    // Успешное удаление пользователя
    $response['success'] = true;
    $response['message'] = 'Аккаунт успешно удален.';

    // Завершаем сессию и очищаем cookie
    session_destroy();
    setcookie("user_id", "", time() - 3600, "/");
} else {
    $response['message'] = 'Ошибка при удалении аккаунта: ' . pg_last_error($conn);
}

pg_close($conn);
echo json_encode($response);
