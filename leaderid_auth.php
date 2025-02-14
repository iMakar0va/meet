<?php
require '../php/conn.php';
session_start();

$client_id = '9736370e-6438-4d5c-bbe8-b2e9252fd0d5';  // Ваш client_id
$client_secret = 'tqGScc3gssZ4W3lGOTqi2cvF1mHCSKTO';  // Ваш client_secret
$redirect_uri = 'http://localhost/wow/leaderid_auth.php';  // Ваш redirect_uri

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Шаг 1. Обмен кода на токен
    $token_url = "https://leader-id.ru/oauth/token";
    $data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code',
    ];

    // Отправляем запрос через cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (isset($token_data['access_token'])) {
        $access_token = $token_data['access_token'];

        // Шаг 2. Получаем информацию о пользователе
        $user_info_url = "https://leader-id.ru/api/v1/users/me";
        $headers = [
            "Authorization: Bearer $access_token",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $user_info = curl_exec($ch);
        curl_close($ch);

        $user_data = json_decode($user_info, true);

        if (isset($user_data['data'])) {
            $user = $user_data['data'];

            // Пример данных, которые мы получаем от LeaderID
            $user_name = $user['first_name'] . ' ' . $user['last_name'];  // Имя и фамилия
            $user_email = $user['email'];  // Email

            // Проверка, существует ли пользователь в базе данных
            $getUserQuery = "SELECT user_id FROM users WHERE email = $1";
            $resultGetUser = pg_query_params($conn, $getUserQuery, [$user_email]);

            if ($resultGetUser && pg_num_rows($resultGetUser) > 0) {
                // Если пользователь существует — авторизуем его
                $row = pg_fetch_assoc($resultGetUser);
                $_SESSION['user_id'] = $row['user_id'];
                setcookie("user_id", $row['user_id'], time() + 3600 * 24 * 30, "/"); // cookie на 30 дней

                // Перенаправляем пользователя на страницу личного кабинета
                header("Location: /lk.php");
                exit;
            } else {
                // Если пользователя нет в базе — регистрируем его
                $stmt = pg_prepare($conn, "insert_user", "INSERT INTO users (email, name) VALUES ($1, $2) RETURNING user_id");
                $result = pg_execute($conn, "insert_user", [$user_email, $user_name]);

                if ($result) {
                    $row = pg_fetch_assoc($result);
                    $_SESSION['user_id'] = $row['user_id'];
                    setcookie("user_id", $row['user_id'], time() + 3600 * 24 * 30, "/");

                    // Перенаправляем на личный кабинет
                    header("Location: /lk.php");
                    exit;
                } else {
                    echo 'Ошибка регистрации пользователя.';
                }
            }
        } else {
            echo 'Ошибка получения данных пользователя.';
        }
    } else {
        echo 'Ошибка авторизации: не удалось получить токен доступа.';
    }
} else {
    echo 'Ошибка: отсутствует код авторизации.';
}
?>
