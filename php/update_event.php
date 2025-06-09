<?php
session_start();
require 'conn.php';

require 'autoload.php';

$variables = require 'variables.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Получаем event_id
$eventId = $_POST['event_id'] ?? $_SESSION['event_id'] ?? null;

if (!$eventId) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: мероприятие не найдено.']);
    exit();
}

// Получаем данные из формы
$title = $_POST['title_event'] ?? '';
$type = $_POST['type_event'] ?? '';
$topic = $_POST['topic_event'] ?? '';
$description = $_POST['desc_event'] ?? '';
$eventDate = $_POST['date_event'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$city = $_POST['city_event'] ?? '';
$place = $_POST['place_event'] ?? '';
$address = $_POST['address_event'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';

// Загрузка программы мероприятия
$programFile  = null;
$programMime  = null;

if (isset($_FILES['program']) && $_FILES['program']['error'] === UPLOAD_ERR_OK) {
    $allowedDocTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    if (in_array($_FILES['program']['type'], $allowedDocTypes, true)) {
        $tmp = $_FILES['program']['tmp_name'];
        $programFile = pg_escape_bytea($conn, file_get_contents($tmp));
        $programMime = $_FILES['program']['type'];  // сохраняем тип
    } else {
        $response['message'] = 'Допустимы только PDF или DOCX.';
        echo json_encode($response);
        exit();
    }
}
// Проверка обязательных полей
if (empty($title) || empty($type) || empty($topic) || empty($description) || empty($eventDate) || empty($startTime) || empty($endTime) || empty($city) || empty($place) || empty($address) || empty($phone) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: все поля должны быть заполнены.']);
    exit();
}

// Обновление данных мероприятия
$query = "UPDATE events SET title = $1, type = $2, topic = $3, description = $4, event_date = $5, start_time = $6, end_time = $7, city = $8, place = $9, address = $10, phone = $11, email = $12, program_file = $13 WHERE event_id = $14";
$result = pg_query_params($conn, $query, [
    $title,
    $type,
    $topic,
    $description,
    $eventDate,
    $startTime,
    $endTime,
    $city,
    $place,
    $address,
    $phone,
    $email,
    $programFile,
    $eventId
]);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении данных.']);
    exit();
}

// Обработка изображения
if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
    // Восстановление дефолтного изображения
    $defaultImage = file_get_contents('../img/event_fon.jpg');
    if ($defaultImage === false) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при чтении дефолтного изображения.']);
        exit();
    }
    $escapedData = pg_escape_bytea($conn, $defaultImage);
    $query = "UPDATE events SET image = $1 WHERE event_id = $2";
    $result = pg_query_params($conn, $query, [$escapedData, $eventId]);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при восстановлении изображения.']);
        exit();
    }
} elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Проверка и загрузка нового изображения
    $file = $_FILES['file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 10 * 1024 * 1024; // 10 МБ

    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Формат изображения должен быть JPEG, JPG или PNG.']);
        exit();
    }

    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Размер изображения не должен превышать 10 МБ.']);
        exit();
    }

    // Чтение и сохранение изображения
    $imageData = file_get_contents($file['tmp_name']);
    if ($imageData === false) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при чтении содержимого изображения.']);
        exit();
    }

    $escapedData = pg_escape_bytea($conn, $imageData);
    $query = "UPDATE events SET image = $1 WHERE event_id = $2";
    $result = pg_query_params($conn, $query, [$escapedData, $eventId]);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении изображения.']);
        exit();
    }
}


// Получаем email всех участников мероприятия
$queryParticipants = "SELECT users.email FROM user_events
                      JOIN users ON user_events.user_id = users.user_id
                      WHERE user_events.event_id = $1 and user_events.is_signed = true";
$resultParticipants = pg_query_params($conn, $queryParticipants, [$eventId]);

$emails = [];
while ($row = pg_fetch_assoc($resultParticipants)) {
    if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
        $emails[] = $row['email'];
    }
}

// Получаем email организатора
$queryOrganizer = "SELECT organizators.email FROM events
                   JOIN organizators ON events.organizator_id = organizators.organizator_id
                   WHERE events.event_id = $1 LIMIT 1";
$resultOrganizer = pg_query_params($conn, $queryOrganizer, [$eventId]);

if ($resultOrganizer && pg_num_rows($resultOrganizer) > 0) {
    $organizerEmail = pg_fetch_result($resultOrganizer, 0, 0);

    // if (filter_var($organizerEmail, FILTER_VALIDATE_EMAIL)) {
    //     $emails[] = $organizerEmail;
    // }
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка: организатор не найден.']);
    exit();
}

// // Проверяем, есть ли кому отправлять
// if (empty($emails)) {
//     echo json_encode(['success' => false, 'message' => 'Нет валидных email для отправки.']);
//     exit();
// }

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';
    $mail->SMTPAuth = true;
    $mail->Username = $variables['smtp_username'];
    $mail->Password = $variables['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->setFrom($variables['smtp_username'], 'MEET');
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = 'Уведомлении об изменении данных мероприятия';

    $mail->clearAddresses();
    $mail->addAddress($organizerEmail);
    $mail->Body = "
            <h1>Уведомление об изменении информации о мероприятии</h1>
            <p>Уважаемый организатор,</p>
            <p>Мы хотим проинформировать вас о том, что были внесены изменения в детали мероприятия, которое вы создали.</p>
            <p><strong>Название мероприятия:</strong> {$title}</p>
            <p><strong>Направление:</strong> {$type}</p>
            <p><strong>Тип:</strong> {$topic}</p>
            <p><strong>Дата и время:</strong> {$eventDate}, {$startTime} - {$endTime}</p>
            <p><strong>Место проведения:</strong> {$place}</p>
            <p><strong>Адрес:</strong> {$city}, {$address}</p>
            <p><strong>Контактный телефон:</strong> {$phone}</p>
            <p><strong>Email организатора:</strong> {$email}</p>
            <p>Пожалуйста, ознакомьтесь с обновленной информацией.</p>
            <p>Если у вас возникли вопросы, свяжитесь с организатором мероприятия.</p>
            <p>С уважением,<br>Команда MEET</p>";
    $mail->AltBody = "Уважаемый организатор,\n\n"
        . "Мы хотим проинформировать вас о том, что были внесены изменения в детали мероприятия, которое вы создали.\n\n"
        . "Название мероприятия: {$title}\n"
        . "Направление: {$type}\n"
        . "Тип: {$topic}\n"
        . "Дата и время: {$eventDate}, {$startTime} - {$endTime}\n"
        . "Место проведения: {$place}\n"
        . "Адрес: {$city}, {$address}\n"
        . "Контактный телефон: {$phone}\n"
        . "Email организатора: {$email}\n\n"
        . "Пожалуйста, ознакомьтесь с обновленной информацией.\n"
        . "Если у вас возникли вопросы, свяжитесь с нашей командой.\n\n"
        . "С уважением,\nКоманда MEET";
    $mail->send();

    // Получаем активность мероприятия
    $queryEvents = "SELECT is_active FROM events WHERE event_id = $1";
    $resultEvents = pg_query_params($conn, $queryEvents, [$eventId]);

    if ($resultEvents) {
        $row = pg_fetch_assoc($resultEvents); // Преобразуем результат в ассоциативный массив
        if ($row && $row['is_active'] == 't') {
            foreach ($emails as $userEmail) {
                $mail->clearAddresses();
                $mail->addAddress($userEmail);
                $mail->Subject = 'Уведомлении об изменении данных мероприятия';
                $mail->Body = "
                    <h1>Уведомление об изменении информации о мероприятии</h1>
                    <p>Уважаемый участник,</p>
                    <p>Мы хотим проинформировать вас о том, что были внесены изменения в детали мероприятия, на которое вы зарегистрированы.</p>
                    <p><strong>Название мероприятия:</strong> {$title}</p>
                    <p><strong>Направление:</strong> {$type}</p>
                    <p><strong>Тип:</strong> {$topic}</p>
                    <p><strong>Дата и время:</strong> {$eventDate}, {$startTime} - {$endTime}</p>
                    <p><strong>Место проведения:</strong> {$place}</p>
                    <p><strong>Адрес:</strong> {$city}, {$address}</p>
                    <p><strong>Контактный телефон:</strong> {$phone}</p>
                    <p><strong>Email организатора:</strong> {$email}</p>
                    <p>Пожалуйста, ознакомьтесь с обновленной информацией и внесите соответствующие изменения в свои планы.</p>
                    <p>Если у вас возникли вопросы, свяжитесь с организатором мероприятия.</p>
                    <p>С уважением,<br>Команда MEET</p>";

                $mail->AltBody = "Уважаемый участник,\n\n"
                    . "Мы хотим проинформировать вас о том, что были внесены изменения в детали мероприятия, на которое вы зарегистрированы.\n\n"
                    . "Название мероприятия: {$title}\n"
                    . "Направление: {$type}\n"
                    . "Тип: {$topic}\n"
                    . "Дата и время: {$eventDate}, {$startTime} - {$endTime}\n"
                    . "Место проведения: {$place}, \n"
                    . "Адрес: {$city}, {$address}\n"
                    . "Контактный телефон: {$phone}\n"
                    . "Email организатора: {$email}\n\n"
                    . "Пожалуйста, ознакомьтесь с обновленной информацией и внесите соответствующие изменения в свои планы.\n"
                    . "Если у вас возникли вопросы, свяжитесь с нашей командой.\n\n"
                    . "С уважением,\nКоманда MEET";



                $mail->send();
            }
        }
    }
    echo json_encode(['success' => true, 'message' => 'Данные успешно обновлены.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка отправки email: ' . $e->getMessage()]);
}

pg_close($conn);
