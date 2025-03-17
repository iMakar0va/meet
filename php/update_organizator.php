<?php
session_start();
require 'conn.php';

require 'autoload.php';

$variables = require 'variables.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Получаем organizator_id
$organizatorId = $_POST['organizator_id'] ?? $_SESSION['organizator_id'] ?? null;

if (!$organizatorId) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: организатор не найден.']);
    exit();
}

// Получаем данные из формы
$name = $_POST['name'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$date_start_work = $_POST['date_start_work'] ?? '';
$description = $_POST['description'] ?? '';

// Проверка обязательных полей
if (empty($name) || empty($phone_number) || empty($date_start_work) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: все поля должны быть заполнены.']);
    exit();
}

// Обновление данных мероприятия
$query = "UPDATE organizators SET name = $1, phone_number = $2, date_start_work = $3, description = $4 WHERE organizator_id = $5";
$result = pg_query_params($conn, $query, [
    $name,
    $phone_number,
    $date_start_work,
    $description,
    $organizatorId
]);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении данных.']);
    exit();
}
// Получаем email организатора
$queryEmail = "SELECT email FROM organizators WHERE organizator_id = $1";
$resultEmail = pg_query_params($conn, $queryEmail, [$organizatorId]);

if (!$resultEmail || pg_num_rows($resultEmail) === 0) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: email организатора не найден.']);
    exit();
}
$organizerEmail = pg_fetch_result($resultEmail, 0, 0);

// Отправка уведомления по email
$mailBody = "
    <h2>Уважаемый пользователь, ваш профиль был успешно обновлён</h2>
    <p><b>Имя:</b> $name</p>
    <p><b>Телефон:</b> $phone_number</p>
    <p><b>Дата начала работы:</b> $date_start_work</p>
    <p><b>Описание:</b> $description</p>
    <p>Если вы не вносили изменения, пожалуйста, немедленно свяжитесь с нашей службой поддержки.</p>
    <p>С уважением,<br>Команда поддержки</p>";

$altBody = "
    Уважаемый(ая) пользователь, ваш профиль был успешно обновлён.
    Имя: $name
    Телефон: $phone_number
    Дата начала работы: $date_start_work
    Описание: $description

    Если вы не вносили изменения, пожалуйста, немедленно свяжитесь с нашей службой поддержки.

    С уважением,
    Команда поддержки.";


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
    $mail->addAddress($organizerEmail);
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = 'Ваш профиль был обновлен';
    $mail->Body = $mailBody;
    $mail->AltBody = strip_tags($mailBody);
    $mail->send();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка отправки email: ' . $e->getMessage()]);
    exit();
}

echo json_encode(['success' => true, 'message' => 'Данные успешно обновлены, уведомление отправлено.']);
pg_close($conn);
