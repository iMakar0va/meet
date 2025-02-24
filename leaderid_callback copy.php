<?php
session_start();
require './php/conn.php';  // Подключение к базе данных

// Конфигурация
$client_id = "9736370e-6438-4d5c-bbe8-b2e9252fd0d5";
$client_secret = "tqGScc3gssZ4W3lGOTqi2cvF1mHCSKTO";
$redirect_uri = "https://localhost/wow2/meet/leaderid_callback.php";

// Получение кода авторизации
if (!isset($_GET['code'])) {
    error_log("Ошибка: отсутствует параметр code.");
    die("Ошибка авторизации: код отсутствует.");
}
$code = $_GET['code'];
error_log("Получен код авторизации: $code");

// Получаем access_token
$token_url = "https://apps.leader-id.ru/api/v1/oauth/token";
$data = json_encode([
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri
]);

$options = [
    'http' => [
        'header' => "Content-type: application/json",
        'method' => 'POST',
        'content' => $data
    ]
];

$response = @file_get_contents($token_url, false, stream_context_create($options));

if ($response === FALSE) {
    error_log("Ошибка запроса токена: " . json_encode(error_get_last()));
    die("Ошибка запроса токена.");
}

$token_data = json_decode($response, true);
error_log("Ответ токена: " . json_encode($token_data));

if (!isset($token_data['access_token'])) {
    die("Ошибка: токен отсутствует.");
}

$access_token = $token_data['access_token'];

// --- Запрос данных через /users/link-app ---
$user_url = "https://apps.leader-id.ru/api/v1/users/link-app";
$link_data_response = @file_get_contents($user_url, false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $access_token",
        'method' => 'GET'
    ]
]));

if ($link_data_response === FALSE) {
    error_log("Ошибка запроса: " . json_encode(error_get_last()));
    die("Ошибка получения данных профилей.");
}

$link_data = json_decode($link_data_response, true);
error_log("Ответ от /users/link-app: " . json_encode($link_data));

$user_id = null;

if (!empty($link_data['items']) && isset($link_data['items'][0]['id'])) {
    // Если есть профили, берем первый ID
    $user_id = $link_data['items'][0]['id'];
    error_log("Получен user_id из профилей: $user_id");
} elseif (isset($link_data['id'])) {
    // Если нет профилей, но есть просто id
    $user_id = $link_data['id'];
    error_log("Получен user_id напрямую: $user_id");
} elseif (preg_match('/\[(\d+)\]/', json_encode($link_data), $matches)) {
    // Если ID пришел как [6749818] в ответе
    $user_id = $matches[1];
    error_log("Получен user_id из текста ошибки: $user_id");
} else {
    error_log("Ошибка: нет профилей и user_id.");
    die("Ошибка: нет профилей и отсутствует user_id.");
}

if (!$user_id) {
    die("Ошибка: не удалось получить user_id.");
}

// --- Получаем данные пользователя по user_id ---
$user_url = "https://apps.leader-id.ru/api/v1/users/$user_id";
$options_user = [
    'http' => [
        'header' => "Authorization: Bearer $access_token",
        'method' => 'GET'
    ]
];

$user_data_response = @file_get_contents($user_url, false, stream_context_create($options_user));

if ($user_data_response === FALSE) {
    error_log("Ошибка запроса данных о пользователе: " . json_encode(error_get_last()));
    die("Ошибка получения данных пользователя.");
}

$user_data = json_decode($user_data_response, true);
error_log("Ответ от /users: " . json_encode($user_data));

if (empty($user_data)) {
    die("Ошибка: пустой ответ от API.");
}

$user_email = $user_data['email'] ?? null;
if (!$user_email) {
    die("Ошибка: email отсутствует в данных пользователя.");
}
error_log("Найден профиль пользователя с email: $user_email");

// --- Проверяем наличие пользователя в базе ---
$result = pg_query_params($conn, "SELECT user_id FROM users WHERE email = $1", [$user_email]);
if ($result && pg_num_rows($result) > 0) {
    // Пользователь найден
    $_SESSION['user_id'] = pg_fetch_result($result, 0, 'user_id');
    setcookie("user_id", $_SESSION['user_id'], time() + 3600 * 24 * 30, "/");
    header("Location: lk.php");
} else {
    // Новый пользователь — перенаправляем на регистрацию
    $_SESSION['leader_email'] = $user_email;
    header("Location: reg.php");
}
exit();
